<?php

namespace App\Http\Controllers;

use App\Mail\OrderPlaced;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\UserSubscription;
use App\Services\LoyaltyPointsService;
use App\Services\SubscriptionService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class WebhookController extends Controller
{
    public function stripe(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = Setting::getValue('stripe_webhook_secret');

        if (! $webhookSecret) {
            Log::error('Stripe webhook secret not configured');

            return response()->json(['error' => 'Webhook secret not configured'], 500);
        }

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
        } catch (\UnexpectedValueException $e) {
            Log::error('Stripe webhook invalid payload: '.$e->getMessage());

            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (SignatureVerificationException $e) {
            Log::error('Stripe webhook invalid signature: '.$e->getMessage());

            return response()->json(['error' => 'Invalid signature'], 400);
        }

        try {
            switch ($event->type) {
                case 'charge.succeeded':
                    $this->handleStripeChargeSucceeded($event->data->object);
                    break;

                case 'charge.failed':
                    $this->handleStripeChargeFailed($event->data->object);
                    break;

                case 'payment_intent.succeeded':
                    $this->handleStripePaymentIntentSucceeded($event->data->object);
                    break;

                case 'payment_intent.payment_failed':
                    $this->handleStripePaymentIntentFailed($event->data->object);
                    break;

                case 'customer.subscription.created':
                    $this->handleStripeSubscriptionCreated($event->data->object);
                    break;

                case 'customer.subscription.updated':
                    $this->handleStripeSubscriptionUpdated($event->data->object);
                    break;

                case 'customer.subscription.deleted':
                    $this->handleStripeSubscriptionDeleted($event->data->object);
                    break;

                case 'customer.subscription.trial_will_end':
                    $this->handleStripeSubscriptionTrialWillEnd($event->data->object);
                    break;

                case 'invoice.payment_succeeded':
                    $this->handleStripeInvoicePaymentSucceeded($event->data->object);
                    break;

                case 'invoice.payment_failed':
                    $this->handleStripeInvoicePaymentFailed($event->data->object);
                    break;

                default:
                    Log::info('Stripe webhook unhandled event type: '.$event->type);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Stripe webhook processing error: '.$e->getMessage());

            return response()->json(['error' => 'Processing error'], 500);
        }
    }

    public function paystack(Request $request)
    {
        $webhookSecret = Setting::getValue('paystack_secret_key');

        if (! $webhookSecret) {
            Log::error('Paystack webhook secret not configured');

            return response()->json(['error' => 'Webhook secret not configured'], 500);
        }

        $signature = $request->header('X-Paystack-Signature');
        $payload = $request->getContent();
        $computedSignature = hash_hmac('sha512', $payload, $webhookSecret);

        if (! hash_equals($computedSignature, (string) $signature)) {
            Log::error('Paystack webhook invalid signature');

            return response()->json(['error' => 'Invalid signature'], 400);
        }

        try {
            $event = json_decode($payload, true);

            switch ($event['event']) {
                case 'charge.success':
                    $this->handlePaystackChargeSuccess($event['data']);
                    break;

                case 'charge.failed':
                    $this->handlePaystackChargeFailed($event['data']);
                    break;

                case 'subscription.create':
                    $this->handlePaystackSubscriptionCreate($event['data']);
                    break;

                case 'subscription.disable':
                    $this->handlePaystackSubscriptionDisable($event['data']);
                    break;

                default:
                    Log::info('Paystack webhook unhandled event type: '.$event['event']);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Paystack webhook processing error: '.$e->getMessage());

            return response()->json(['error' => 'Processing error'], 500);
        }
    }

    /**
     * Authenticates a mobile money callback.
     *
     * Returns a response to send when the request is not trustworthy, or null
     * when it may proceed. Fails closed: with no configured secret the
     * endpoint refuses everything rather than accepting unauthenticated
     * payment confirmations.
     */
    protected function guardMobileMoneyRequest(Request $request): ?JsonResponse
    {
        $secret = (string) (config('payment.mobile_money.webhook_secret') ?: '');

        if ($secret === '') {
            Log::critical('Mobile money webhook rejected: MOMO_WEBHOOK_SECRET is not configured. '
                .'Callbacks cannot be authenticated, so they are refused.');

            return response()->json(['error' => 'Webhook secret not configured'], 503);
        }

        $header = (string) config('payment.mobile_money.signature_header', 'X-Momo-Signature');
        $signature = (string) $request->header($header, '');

        if ($signature === '') {
            Log::warning('Mobile money webhook rejected: missing signature header', [
                'ip' => $request->ip(),
            ]);

            return response()->json(['error' => 'Missing signature'], 401);
        }

        $expected = hash_hmac('sha256', $request->getContent(), $secret);

        if (! hash_equals($expected, $signature)) {
            Log::warning('Mobile money webhook rejected: invalid signature', [
                'ip' => $request->ip(),
            ]);

            return response()->json(['error' => 'Invalid signature'], 401);
        }

        return null;
    }

    public function momo(Request $request, string $provider)
    {
        $provider = strtolower(trim($provider));
        if (! in_array($provider, ['mtn', 'mpesa'], true)) {
            return response()->json(['error' => 'Unsupported provider'], 422);
        }

        // This endpoint marks payments completed and orders paid, so it is
        // authenticated the same way the Stripe and Paystack webhooks are.
        // It previously accepted any request, which meant anyone who could
        // reach it with a known transaction reference could mark an order
        // paid without paying.
        if (($guard = $this->guardMobileMoneyRequest($request)) !== null) {
            return $guard;
        }

        $payload = $request->all();
        $referenceId = (string) (
            data_get($payload, 'reference_id')
            ?? data_get($payload, 'externalId')
            // Safaricom STK callbacks nest the identifiers under Body.stkCallback.
            // These were previously only read at the top level, where a real
            // M-Pesa callback does not carry them, so live callbacks were
            // rejected as "Missing reference". CallbackMetadata.Item.0.Value is
            // the amount rather than a reference, so it is tried last.
            ?? data_get($payload, 'Body.stkCallback.CheckoutRequestID')
            ?? data_get($payload, 'Body.stkCallback.MerchantRequestID')
            ?? data_get($payload, 'CheckoutRequestID')
            ?? data_get($payload, 'transaction_id')
            ?? data_get($payload, 'Body.stkCallback.CallbackMetadata.Item.0.Value')
            ?? ''
        );

        if ($referenceId === '') {
            return response()->json(['error' => 'Missing reference'], 422);
        }

        $payment = Payment::where('transaction_id', $referenceId)
            ->where('payment_method', 'mobile_money')
            ->first();

        if (! $payment) {
            Log::warning("Mobile money webhook payment not found for reference {$referenceId}");

            return response()->json(['success' => true]);
        }

        $status = strtolower((string) (
            data_get($payload, 'status')
            ?? data_get($payload, 'Status')
            ?? data_get($payload, 'Body.stkCallback.ResultCode')
            ?? ''
        ));

        $isSuccess = in_array($status, ['success', 'successful', 'completed', '0'], true);

        DB::beginTransaction();
        try {
            $payment->update([
                'status' => $isSuccess ? 'completed' : 'failed',
                'payment_data' => json_encode($payload),
                'processed_at' => now(),
                'error_message' => $isSuccess ? null : (string) (data_get($payload, 'message') ?? data_get($payload, 'Body.stkCallback.ResultDesc') ?? 'Mobile money payment failed'),
            ]);

            $order = $payment->order;
            if ($order && $isSuccess && $order->payment_status !== 'paid') {
                $order->update([
                    'payment_status' => 'paid',
                    'paid_at' => now(),
                    'status' => 'processing',
                ]);

                // Award loyalty points if user and order exist
                if ($order->user && $order->total) {
                    $loyaltyService = app(LoyaltyPointsService::class);
                    $loyaltyService->awardPointsForPurchase($order->user, (float) $order->total, $order->id);
                }
            }

            if ($order && ! $isSuccess) {
                $order->update(['payment_status' => 'failed']);
            }

            DB::commit();

            return response()->json(['success' => true]);
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error('Mobile money webhook processing failed: '.$exception->getMessage());

            return response()->json(['error' => 'Processing error'], 500);
        }
    }

    protected function handleStripeChargeSucceeded($charge)
    {
        $orderId = $charge->metadata->order_id ?? null;

        if (! $orderId) {
            Log::warning('Stripe charge succeeded but no order_id in metadata');

            return;
        }

        DB::beginTransaction();

        try {
            $order = Order::find($orderId);

            if (! $order) {
                Log::error("Order not found: {$orderId}");
                DB::rollBack();

                return;
            }

            if ($order->payment_status === 'paid') {
                Log::info("Order {$orderId} already marked as paid");
                DB::rollBack();

                return;
            }

            $payment = Payment::where('order_id', $orderId)
                ->where('transaction_id', $charge->id)
                ->first();

            if (! $payment) {
                Payment::create([
                    'order_id' => $orderId,
                    'transaction_id' => $charge->id,
                    'payment_method' => 'stripe',
                    'amount' => $charge->amount / 100,
                    'currency' => strtoupper($charge->currency),
                    'status' => 'completed',
                    'payment_data' => json_encode($charge),
                    'processed_at' => now(),
                ]);
            } else {
                $payment->update([
                    'status' => 'completed',
                    'payment_data' => json_encode($charge),
                    'processed_at' => now(),
                ]);
            }

            $order->update([
                'payment_status' => 'paid',
                'paid_at' => now(),
                'status' => 'processing',
            ]);

            // Award loyalty points if user and order exist
            if ($order->user && $order->total) {
                $loyaltyService = app(LoyaltyPointsService::class);
                $loyaltyService->awardPointsForPurchase($order->user, (float) $order->total, $order->id);
            }

            DB::commit();

            if ($order->user) {
                Mail::to($order->user->email)->send(new OrderPlaced($order));
            }

            Log::info("Stripe payment confirmed for order: {$orderId}");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error processing Stripe charge succeeded: {$e->getMessage()}");
        }
    }

    protected function handleStripeChargeFailed($charge)
    {
        $orderId = $charge->metadata->order_id ?? null;

        if (! $orderId) {
            return;
        }

        $order = Order::find($orderId);

        if ($order) {
            Payment::updateOrCreate(
                [
                    'order_id' => $orderId,
                    'transaction_id' => $charge->id,
                ],
                [
                    'payment_method' => 'stripe',
                    'amount' => $charge->amount / 100,
                    'currency' => strtoupper($charge->currency),
                    'status' => 'failed',
                    'payment_data' => json_encode($charge),
                    'processed_at' => now(),
                ]
            );

            $order->update(['payment_status' => 'failed']);

            Log::warning("Stripe payment failed for order: {$orderId}");
        }
    }

    protected function handleStripePaymentIntentSucceeded($paymentIntent)
    {
        $orderId = $paymentIntent->metadata->order_id ?? null;

        if (! $orderId) {
            return;
        }

        DB::beginTransaction();

        try {
            $order = Order::find($orderId);

            if (! $order || $order->payment_status === 'paid') {
                DB::rollBack();

                return;
            }

            Payment::updateOrCreate(
                ['order_id' => $orderId],
                [
                    'transaction_id' => $paymentIntent->id,
                    'payment_method' => 'stripe',
                    'amount' => $paymentIntent->amount / 100,
                    'currency' => strtoupper($paymentIntent->currency),
                    'status' => 'completed',
                    'payment_data' => json_encode($paymentIntent),
                    'processed_at' => now(),
                ]
            );

            $order->update([
                'payment_status' => 'paid',
                'paid_at' => now(),
                'status' => 'processing',
            ]);

            // Award loyalty points if user and order exist
            if ($order->user && $order->total) {
                $loyaltyService = app(LoyaltyPointsService::class);
                $loyaltyService->awardPointsForPurchase($order->user, (float) $order->total, $order->id);
            }

            DB::commit();

            if ($order->user) {
                Mail::to($order->user->email)->send(new OrderPlaced($order));
            }

            Log::info("Stripe PaymentIntent confirmed for order: {$orderId}");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error processing Stripe PaymentIntent: {$e->getMessage()}");
        }
    }

    protected function handleStripePaymentIntentFailed($paymentIntent)
    {
        $orderId = $paymentIntent->metadata->order_id ?? null;

        if (! $orderId) {
            return;
        }

        $order = Order::find($orderId);

        if ($order) {
            Payment::updateOrCreate(
                ['order_id' => $orderId],
                [
                    'transaction_id' => $paymentIntent->id,
                    'payment_method' => 'stripe',
                    'amount' => $paymentIntent->amount / 100,
                    'currency' => strtoupper($paymentIntent->currency),
                    'status' => 'failed',
                    'payment_data' => json_encode($paymentIntent),
                    'processed_at' => now(),
                ]
            );

            $order->update(['payment_status' => 'failed']);

            Log::warning("Stripe PaymentIntent failed for order: {$orderId}");
        }
    }

    protected function handlePaystackChargeSuccess($data)
    {
        $orderId = $data['metadata']['order_id'] ?? null;

        if (! $orderId) {
            Log::warning('Paystack charge success but no order_id in metadata');

            return;
        }

        DB::beginTransaction();

        try {
            $order = Order::find($orderId);

            if (! $order) {
                Log::error("Order not found: {$orderId}");
                DB::rollBack();

                return;
            }

            if ($order->payment_status === 'paid') {
                Log::info("Order {$orderId} already marked as paid");
                DB::rollBack();

                return;
            }

            Payment::updateOrCreate(
                [
                    'order_id' => $orderId,
                    'transaction_id' => $data['reference'],
                ],
                [
                    'payment_method' => 'paystack',
                    'amount' => $data['amount'] / 100,
                    'currency' => strtoupper($data['currency']),
                    'status' => 'completed',
                    'payment_data' => json_encode($data),
                    'processed_at' => now(),
                ]
            );

            $order->update([
                'payment_status' => 'paid',
                'paid_at' => now(),
                'status' => 'processing',
            ]);

            // Award loyalty points if user and order exist
            if ($order->user && $order->total) {
                $loyaltyService = app(LoyaltyPointsService::class);
                $loyaltyService->awardPointsForPurchase($order->user, (float) $order->total, $order->id);
            }

            DB::commit();

            if ($order->user) {
                Mail::to($order->user->email)->send(new OrderPlaced($order));
            }

            Log::info("Paystack payment confirmed for order: {$orderId}");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error processing Paystack charge success: {$e->getMessage()}");
        }
    }

    protected function handlePaystackChargeFailed($data)
    {
        $orderId = $data['metadata']['order_id'] ?? null;

        if (! $orderId) {
            return;
        }

        $order = Order::find($orderId);

        if ($order) {
            Payment::updateOrCreate(
                [
                    'order_id' => $orderId,
                    'transaction_id' => $data['reference'],
                ],
                [
                    'payment_method' => 'paystack',
                    'amount' => $data['amount'] / 100,
                    'currency' => strtoupper($data['currency']),
                    'status' => 'failed',
                    'payment_data' => json_encode($data),
                    'processed_at' => now(),
                ]
            );

            $order->update(['payment_status' => 'failed']);

            Log::warning("Paystack payment failed for order: {$orderId}");
        }
    }

    protected function handleStripeSubscriptionCreated($subscription)
    {
        $userId = $subscription->metadata->user_id ?? null;

        if (! $userId) {
            Log::warning('Stripe subscription created but no user_id in metadata');

            return;
        }

        Log::info("Stripe subscription created for user {$userId}: {$subscription->id}");
    }

    protected function handleStripeSubscriptionUpdated($subscription)
    {
        $userSubscription = UserSubscription::where('payment_provider_subscription_id', $subscription->id)->first();

        if (! $userSubscription) {
            Log::warning("Subscription not found for Stripe ID: {$subscription->id}");

            return;
        }

        $status = match ($subscription->status) {
            'active' => 'active',
            'trialing' => 'trialing',
            'past_due' => 'past_due',
            'canceled' => 'canceled',
            'unpaid' => 'expired',
            default => $userSubscription->status,
        };

        $userSubscription->update([
            'status' => $status,
            'current_period_start' => Carbon::createFromTimestamp($subscription->current_period_start),
            'current_period_end' => Carbon::createFromTimestamp($subscription->current_period_end),
            'cancel_at_period_end' => $subscription->cancel_at_period_end ?? false,
        ]);

        Log::info("Stripe subscription updated: {$subscription->id} - Status: {$status}");
    }

    protected function handleStripeSubscriptionDeleted($subscription)
    {
        $userSubscription = UserSubscription::where('payment_provider_subscription_id', $subscription->id)->first();

        if (! $userSubscription) {
            return;
        }

        $userSubscription->update([
            'status' => 'canceled',
            'canceled_at' => now(),
        ]);

        Log::info("Stripe subscription canceled: {$subscription->id}");
    }

    protected function handleStripeSubscriptionTrialWillEnd($subscription)
    {
        $userSubscription = UserSubscription::where('payment_provider_subscription_id', $subscription->id)->first();

        if (! $userSubscription) {
            return;
        }

        Log::info("Stripe subscription trial ending soon: {$subscription->id}");
    }

    protected function handleStripeInvoicePaymentSucceeded($invoice)
    {
        $subscriptionId = $invoice->subscription ?? null;

        if (! $subscriptionId) {
            return;
        }

        $userSubscription = UserSubscription::where('payment_provider_subscription_id', $subscriptionId)->first();

        if (! $userSubscription) {
            return;
        }

        $subscriptionService = app(SubscriptionService::class);
        $payment = $subscriptionService->recordPayment(
            $userSubscription,
            $invoice->amount_paid / 100,
            'paid',
            'stripe',
            $invoice->id
        );

        if (! $payment->wasRecentlyCreated) {
            Log::info("Stripe invoice payment already processed for invoice: {$invoice->id}");

            return;
        }

        if ($userSubscription->status === 'trialing') {
            $subscriptionService->activateSubscription($userSubscription);
        }

        Log::info("Stripe invoice payment succeeded for subscription: {$subscriptionId}");
    }

    protected function handleStripeInvoicePaymentFailed($invoice)
    {
        $subscriptionId = $invoice->subscription ?? null;

        if (! $subscriptionId) {
            return;
        }

        $userSubscription = UserSubscription::where('payment_provider_subscription_id', $subscriptionId)->first();

        if (! $userSubscription) {
            return;
        }

        $subscriptionService = app(SubscriptionService::class);
        $payment = $subscriptionService->recordPayment(
            $userSubscription,
            $invoice->amount_due / 100,
            'failed',
            'stripe',
            $invoice->id
        );

        if ($payment->wasRecentlyCreated) {
            $subscriptionService->markPastDue($userSubscription);
        }

        Log::warning("Stripe invoice payment failed for subscription: {$subscriptionId}");
    }

    protected function handlePaystackSubscriptionCreate($data)
    {
        Log::info('Paystack subscription created: '.json_encode($data));
    }

    protected function handlePaystackSubscriptionDisable($data)
    {
        $subscriptionCode = $data['subscription_code'] ?? null;

        if (! $subscriptionCode) {
            return;
        }

        $userSubscription = UserSubscription::where('payment_provider_subscription_id', $subscriptionCode)->first();

        if (! $userSubscription) {
            return;
        }

        $userSubscription->update([
            'status' => 'canceled',
            'canceled_at' => now(),
        ]);

        Log::info("Paystack subscription disabled: {$subscriptionCode}");
    }

    /**
     * Handle SWISH payment webhook callback
     */
    public function swish(Request $request)
    {
        $webhookSecret = Setting::getValue('swish_webhook_secret');

        // Require webhook secret for security - reject requests without it
        if (empty($webhookSecret)) {
            Log::error('SWISH webhook rejected: webhook secret not configured');

            return response()->json(['error' => 'Webhook not configured'], 500);
        }

        $signature = $request->header('X-Swish-Signature');
        $payload = $request->getContent();

        if (empty($signature)) {
            Log::error('SWISH webhook rejected: missing signature header');

            return response()->json(['error' => 'Missing signature'], 400);
        }

        $computedSignature = hash_hmac('sha256', $payload, $webhookSecret);

        // Use hash_equals to prevent timing attacks
        if (! hash_equals($computedSignature, $signature)) {
            Log::error('SWISH webhook rejected: invalid signature');

            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $payload = $request->all();

        Log::info('SWISH webhook received', $payload);

        $paymentRequestId = $payload['id'] ?? $payload['instructionUUID'] ?? null;

        if (! $paymentRequestId) {
            return response()->json(['error' => 'Missing payment request ID'], 422);
        }

        $payment = Payment::where('transaction_id', $paymentRequestId)
            ->where('payment_method', 'swish')
            ->first();

        if (! $payment) {
            Log::warning("SWISH webhook payment not found for ID {$paymentRequestId}");

            return response()->json(['success' => true]);
        }

        $status = $payload['status'] ?? '';
        $isSuccess = in_array($status, ['PAID', 'paid', 'SUCCESS', 'success'], true);

        DB::beginTransaction();
        try {
            $payment->update([
                'status' => $isSuccess ? 'completed' : 'failed',
                'payment_data' => json_encode($payload),
                'processed_at' => $isSuccess ? now() : null,
                'error_message' => $isSuccess ? null : ($payload['errorMessage'] ?? 'SWISH payment failed'),
            ]);

            $order = $payment->order;
            if ($order && $isSuccess && $order->payment_status !== 'paid') {
                $order->update([
                    'payment_status' => 'paid',
                    'paid_at' => now(),
                    'status' => 'processing',
                ]);

                // Award loyalty points if user and order exist
                if ($order->user && $order->total) {
                    $loyaltyService = app(LoyaltyPointsService::class);
                    $loyaltyService->awardPointsForPurchase($order->user, (float) $order->total, $order->id);
                }
            }

            if ($order && ! $isSuccess) {
                $order->update(['payment_status' => 'failed']);
            }

            DB::commit();

            return response()->json(['success' => true]);
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error('SWISH webhook processing failed: '.$exception->getMessage());

            return response()->json(['error' => 'Processing error'], 500);
        }
    }
}
