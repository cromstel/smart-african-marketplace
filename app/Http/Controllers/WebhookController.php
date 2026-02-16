<?php

namespace App\Http\Controllers;

use App\Mail\OrderPlaced;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Setting;
use App\Services\LoyaltyPointsService;
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
        $webhookSecret = Setting::get('stripe_webhook_secret');

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
        $webhookSecret = Setting::get('paystack_secret_key');

        if (! $webhookSecret) {
            Log::error('Paystack webhook secret not configured');

            return response()->json(['error' => 'Webhook secret not configured'], 500);
        }

        $signature = $request->header('X-Paystack-Signature');
        $payload = $request->getContent();
        $computedSignature = hash_hmac('sha512', $payload, $webhookSecret);

        if ($signature !== $computedSignature) {
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

                default:
                    Log::info('Paystack webhook unhandled event type: '.$event['event']);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Paystack webhook processing error: '.$e->getMessage());

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

                return;
            }

            if ($order->payment_status === 'paid') {
                Log::info("Order {$orderId} already marked as paid");

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

            $loyaltyService = app(LoyaltyPointsService::class);
            $loyaltyService->awardPointsForPurchase($order->user, $order->total, $order->id);

            DB::commit();

            Mail::to($order->user->email)->send(new OrderPlaced($order));

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

            $loyaltyService = app(LoyaltyPointsService::class);
            $loyaltyService->awardPointsForPurchase($order->user, $order->total, $order->id);

            DB::commit();

            Mail::to($order->user->email)->send(new OrderPlaced($order));

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

                return;
            }

            if ($order->payment_status === 'paid') {
                Log::info("Order {$orderId} already marked as paid");

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

            $loyaltyService = app(LoyaltyPointsService::class);
            $loyaltyService->awardPointsForPurchase($order->user, $order->total, $order->id);

            DB::commit();

            Mail::to($order->user->email)->send(new OrderPlaced($order));

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
}
