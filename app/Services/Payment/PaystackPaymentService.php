<?php

namespace App\Services\Payment;

use App\Models\Setting;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaystackPaymentService
{
    protected $secretKey;

    protected $publicKey;

    protected $baseUrl = 'https://api.paystack.co';

    public function __construct()
    {
        // Reading Paystack settings happens eagerly whenever this service is
        // resolved from the container (even for unrelated requests/commands
        // that merely type-hint a class depending on it), so a DB outage
        // here must never crash the whole request -- fall back to treating
        // Paystack as unconfigured instead.
        try {
            $this->secretKey = Setting::get('paystack_secret_key');
            $this->publicKey = Setting::get('paystack_public_key');
        } catch (Exception $e) {
            Log::error('Failed to load Paystack settings from database; treating Paystack as unconfigured', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function isConfigured(): bool
    {
        if (empty($this->secretKey)) {
            return false;
        }

        try {
            return (bool) Setting::get('paystack_enabled', false);
        } catch (Exception $e) {
            Log::error('Failed to check paystack_enabled setting; treating Paystack as disabled', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    protected function makeRequest(string $method, string $endpoint, array $data = []): array
    {
        if (! $this->isConfigured()) {
            throw new Exception('Paystack is not configured');
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$this->secretKey,
            'Content-Type' => 'application/json',
        ])->$method($this->baseUrl.$endpoint, $data);

        if (! $response->successful()) {
            throw new Exception('Paystack API error: '.$response->body());
        }

        return $response->json();
    }

    public function createCustomer(User $user): ?string
    {
        try {
            $response = $this->makeRequest('post', '/customer', [
                'email' => $user->email,
                'first_name' => explode(' ', $user->name)[0] ?? $user->name,
                'last_name' => explode(' ', $user->name)[1] ?? '',
                'metadata' => [
                    'user_id' => $user->id,
                ],
            ]);

            return $response['data']['customer_code'] ?? null;
        } catch (Exception $e) {
            Log::error('Paystack customer creation failed: '.$e->getMessage());
            throw $e;
        }
    }

    public function createPlan(SubscriptionPlan $plan): ?string
    {
        try {
            $amount = (int) ($plan->price * 100);

            $response = $this->makeRequest('post', '/plan', [
                'name' => $plan->name,
                'amount' => $amount,
                'interval' => $plan->billing_cycle === 'yearly' ? 'annually' : 'monthly',
                'currency' => $plan->currency,
                'description' => $plan->description,
            ]);

            return $response['data']['plan_code'] ?? null;
        } catch (Exception $e) {
            Log::error('Paystack plan creation failed: '.$e->getMessage());

            return null;
        }
    }

    public function createSubscription(User $user, SubscriptionPlan $plan, ?string $authorizationCode = null): array
    {
        try {
            $customerCode = $user->paystack_customer_code ?? $this->createCustomer($user);

            if (! $user->paystack_customer_code) {
                $user->update(['paystack_customer_code' => $customerCode]);
            }

            $planCode = $plan->paystack_plan_code ?? $this->createPlan($plan);

            if (! $plan->paystack_plan_code && $planCode) {
                $plan->update(['paystack_plan_code' => $planCode]);
            }

            $subscriptionData = [
                'customer' => $customerCode,
                'plan' => $planCode,
            ];

            if ($authorizationCode) {
                $subscriptionData['authorization'] = $authorizationCode;
            }

            if ($plan->trial_days > 0) {
                $subscriptionData['start_date'] = now()->addDays($plan->trial_days)->toIso8601String();
            }

            $response = $this->makeRequest('post', '/subscription', $subscriptionData);

            return [
                'subscription_code' => $response['data']['subscription_code'] ?? null,
                'email_token' => $response['data']['email_token'] ?? null,
                'status' => $response['data']['status'] ?? 'inactive',
            ];
        } catch (Exception $e) {
            Log::error('Paystack subscription creation failed: '.$e->getMessage());
            throw $e;
        }
    }

    public function cancelSubscription(string $subscriptionCode, string $emailToken): bool
    {
        try {
            $this->makeRequest('post', '/subscription/disable', [
                'code' => $subscriptionCode,
                'token' => $emailToken,
            ]);

            return true;
        } catch (Exception $e) {
            Log::error('Paystack subscription cancellation failed: '.$e->getMessage());

            return false;
        }
    }

    public function enableSubscription(string $subscriptionCode, string $emailToken): bool
    {
        try {
            $this->makeRequest('post', '/subscription/enable', [
                'code' => $subscriptionCode,
                'token' => $emailToken,
            ]);

            return true;
        } catch (Exception $e) {
            Log::error('Paystack subscription enable failed: '.$e->getMessage());

            return false;
        }
    }

    public function verifyTransaction(string $reference): array
    {
        try {
            $response = $this->makeRequest('get', "/transaction/verify/{$reference}");

            return $response['data'] ?? [];
        } catch (Exception $e) {
            Log::error('Paystack transaction verification failed: '.$e->getMessage());
            throw $e;
        }
    }

    public function initializeTransaction(float $amount, string $email, string $currency = 'NGN', array $metadata = []): array
    {
        try {
            $response = $this->makeRequest('post', '/transaction/initialize', [
                'amount' => (int) ($amount * 100),
                'email' => $email,
                'currency' => $currency,
                'metadata' => $metadata,
            ]);

            return [
                'authorization_url' => $response['data']['authorization_url'] ?? null,
                'access_code' => $response['data']['access_code'] ?? null,
                'reference' => $response['data']['reference'] ?? null,
            ];
        } catch (Exception $e) {
            Log::error('Paystack transaction initialization failed: '.$e->getMessage());
            throw $e;
        }
    }

    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        if (! $this->secretKey) {
            return false;
        }

        $computedSignature = hash_hmac('sha512', $payload, $this->secretKey);

        return hash_equals($computedSignature, $signature);
    }

    /**
     * Create a payment (initialize transaction)
     */
    public function createPayment(array $data): array
    {
        return $this->initializeTransaction(
            $data['amount'] / 100, // Convert from cents
            $data['metadata']['customer_email'] ?? '',
            $data['currency'] ?? 'NGN',
            $data['metadata'] ?? []
        );
    }

    /**
     * Refund a transaction
     */
    public function refundTransaction(string $reference, int $amount): array
    {
        try {
            $response = $this->makeRequest('post', '/refund', [
                'transaction' => $reference,
                'amount' => $amount,
            ]);

            return [
                'refund_id' => $response['data']['refund_id'] ?? null,
                'status' => $response['data']['status'] ?? 'pending',
            ];
        } catch (Exception $e) {
            Log::error('Paystack refund failed: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Get payment URL
     */
    public function getPaymentUrl(object $payment): string
    {
        try {
            $response = $this->makeRequest('get', "/transaction/verify/{$payment->payment_reference}");

            return $response['data']['authorization_url'] ?? '';
        } catch (Exception $e) {
            Log::error('Paystack payment URL retrieval failed: '.$e->getMessage());

            return '';
        }
    }
}
