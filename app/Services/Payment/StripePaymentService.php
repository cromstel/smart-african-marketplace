<?php

namespace App\Services\Payment;

use App\Models\Setting;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Log;
use Stripe\Customer;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;
use Stripe\Refund;
use Stripe\Stripe;
use Stripe\Subscription;
use Stripe\Webhook;

class StripePaymentService
{
    protected $stripe;

    protected $secretKey;

    protected $publicKey;

    protected $webhookSecret;

    public function __construct()
    {
        // Reading Stripe settings happens eagerly whenever this service is
        // resolved from the container (even for unrelated requests/commands
        // that merely type-hint a class depending on it), so a DB outage
        // here must never crash the whole request -- fall back to treating
        // Stripe as unconfigured instead.
        try {
            $this->secretKey = Setting::get('stripe_secret_key');
            $this->publicKey = Setting::get('stripe_public_key');
            $this->webhookSecret = Setting::get('stripe_webhook_secret');
        } catch (Exception $e) {
            Log::error('Failed to load Stripe settings from database; treating Stripe as unconfigured', [
                'error' => $e->getMessage(),
            ]);
        }

        if ($this->secretKey) {
            Stripe::setApiKey($this->secretKey);
        }
    }

    public function isConfigured(): bool
    {
        if (empty($this->secretKey)) {
            return false;
        }

        try {
            return (bool) Setting::get('stripe_enabled', false);
        } catch (Exception $e) {
            Log::error('Failed to check stripe_enabled setting; treating Stripe as disabled', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function createCustomer(User $user): ?string
    {
        $this->ensureConfigured();

        try {
            $customer = Customer::create([
                'email' => $user->email,
                'name' => $user->name,
                'metadata' => [
                    'user_id' => $user->id,
                ],
            ]);

            return $customer->id;
        } catch (Exception $e) {
            Log::error('Stripe customer creation failed: '.$e->getMessage());
            throw $e;
        }
    }

    public function createSubscription(User $user, SubscriptionPlan $plan, ?string $paymentMethodId = null): array
    {
        $this->ensureConfigured();

        try {
            $customerId = $user->stripe_customer_id ?? $this->createCustomer($user);

            if (! $user->stripe_customer_id) {
                $user->update(['stripe_customer_id' => $customerId]);
            }

            if ($paymentMethodId) {
                PaymentMethod::retrieve($paymentMethodId)->attach([
                    'customer' => $customerId,
                ]);

                Customer::update($customerId, [
                    'invoice_settings' => [
                        'default_payment_method' => $paymentMethodId,
                    ],
                ]);
            }

            $priceData = [
                'currency' => strtolower($plan->currency),
                'product_data' => [
                    'name' => $plan->name,
                    'description' => $plan->description,
                ],
                'unit_amount' => (int) ($plan->price * 100),
                'recurring' => [
                    'interval' => $plan->billing_cycle === 'yearly' ? 'year' : 'month',
                ],
            ];

            $subscriptionData = [
                'customer' => $customerId,
                'items' => [[
                    'price_data' => $priceData,
                ]],
                'metadata' => [
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                ],
            ];

            if ($plan->trial_days > 0) {
                $subscriptionData['trial_period_days'] = $plan->trial_days;
            }

            $subscription = Subscription::create($subscriptionData);

            return [
                'subscription_id' => $subscription->id,
                'status' => $subscription->status,
                'current_period_end' => $subscription->current_period_end,
                'trial_end' => $subscription->trial_end,
            ];
        } catch (Exception $e) {
            Log::error('Stripe subscription creation failed: '.$e->getMessage());
            throw $e;
        }
    }

    public function cancelSubscription(string $subscriptionId, bool $immediately = false): bool
    {
        $this->ensureConfigured();

        try {
            if ($immediately) {
                Subscription::update($subscriptionId, [
                    'cancel_at_period_end' => false,
                ]);
                Subscription::retrieve($subscriptionId)->cancel();
            } else {
                Subscription::update($subscriptionId, [
                    'cancel_at_period_end' => true,
                ]);
            }

            return true;
        } catch (Exception $e) {
            Log::error('Stripe subscription cancellation failed: '.$e->getMessage());

            return false;
        }
    }

    public function resumeSubscription(string $subscriptionId): bool
    {
        $this->ensureConfigured();

        try {
            Subscription::update($subscriptionId, [
                'cancel_at_period_end' => false,
            ]);

            return true;
        } catch (Exception $e) {
            Log::error('Stripe subscription resume failed: '.$e->getMessage());

            return false;
        }
    }

    public function changePlan(string $subscriptionId, SubscriptionPlan $newPlan, bool $prorate = true): bool
    {
        $this->ensureConfigured();

        try {
            $subscription = Subscription::retrieve($subscriptionId);

            Subscription::update($subscriptionId, [
                'items' => [[
                    'id' => $subscription->items->data[0]->id,
                    'price_data' => [
                        'currency' => strtolower($newPlan->currency),
                        'product_data' => [
                            'name' => $newPlan->name,
                        ],
                        'unit_amount' => (int) ($newPlan->price * 100),
                        'recurring' => [
                            'interval' => $newPlan->billing_cycle === 'yearly' ? 'year' : 'month',
                        ],
                    ],
                ]],
                'proration_behavior' => $prorate ? 'create_prorations' : 'none',
            ]);

            return true;
        } catch (Exception $e) {
            Log::error('Stripe plan change failed: '.$e->getMessage());

            return false;
        }
    }

    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        if (! $this->webhookSecret) {
            return false;
        }

        try {
            Webhook::constructEvent($payload, $signature, $this->webhookSecret);

            return true;
        } catch (Exception $e) {
            Log::error('Stripe webhook verification failed: '.$e->getMessage());

            return false;
        }
    }

    public function constructWebhookEvent(string $payload, string $signature)
    {
        return Webhook::constructEvent($payload, $signature, $this->webhookSecret);
    }

    /**
     * Create a payment intent
     */
    public function createPaymentIntent(array $data): array
    {
        $this->ensureConfigured();

        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => $data['amount'],
                'currency' => strtolower($data['currency'] ?? 'usd'),
                'description' => $data['description'] ?? null,
                'metadata' => $data['metadata'] ?? [],
            ]);

            return [
                'transaction_id' => $paymentIntent->id,
                'client_secret' => $paymentIntent->client_secret,
                'status' => $paymentIntent->status,
            ];
        } catch (Exception $e) {
            Log::error('Stripe payment intent creation failed: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Retrieve a payment intent
     */
    public function retrievePaymentIntent(string $paymentIntentId): array
    {
        $this->ensureConfigured();

        try {
            $paymentIntent = PaymentIntent::retrieve($paymentIntentId);

            return [
                'status' => $paymentIntent->status,
                'amount' => $paymentIntent->amount,
            ];
        } catch (Exception $e) {
            Log::error('Stripe payment intent retrieval failed: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Create a refund
     */
    public function createRefund(array $data): array
    {
        $this->ensureConfigured();

        try {
            $refund = Refund::create([
                'payment_intent' => $data['payment_intent'],
                'amount' => $data['amount'] ?? null,
            ]);

            return [
                'refund_id' => $refund->id,
                'status' => $refund->status,
            ];
        } catch (Exception $e) {
            Log::error('Stripe refund creation failed: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Get payment URL for redirect
     */
    public function getPaymentUrl(object $payment): string
    {
        $this->ensureConfigured();

        try {
            $paymentIntent = PaymentIntent::retrieve($payment->transaction_id);

            return $paymentIntent->hosted_violation_url ?? '';
        } catch (Exception $e) {
            Log::error('Stripe payment URL retrieval failed: '.$e->getMessage());

            return '';
        }
    }

    private function ensureConfigured(): void
    {
        if (! $this->isConfigured()) {
            throw new Exception('Stripe is not configured');
        }
    }
}
