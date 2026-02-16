<?php

namespace App\Http\Controllers;

use App\Mail\OrderPlaced;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function stripe(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        if (! Setting::get('stripe_enabled', false)) {
            return redirect()->route('cart.index')->with('error', 'Stripe payment is currently unavailable');
        }

        $stripePublicKey = Setting::get('stripe_public_key');

        if (! $stripePublicKey) {
            return redirect()->route('cart.index')->with('error', 'Payment gateway not configured');
        }

        return view('payment.stripe', compact('order', 'stripePublicKey'));
    }

    public function stripeProcess(Request $request, Order $order)
    {
        $validated = $request->validate([
            'stripeToken' => 'required|string',
        ]);

        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $stripeSecretKey = Setting::get('stripe_secret_key');

        if (! $stripeSecretKey) {
            return redirect()->back()->with('error', 'Payment gateway not configured');
        }

        try {
            \Stripe\Stripe::setApiKey($stripeSecretKey);

            $charge = \Stripe\Charge::create([
                'amount' => $order->total * 100,
                'currency' => 'usd',
                'description' => 'Order #'.$order->order_number,
                'source' => $validated['stripeToken'],
                'metadata' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                ],
            ]);

            DB::beginTransaction();

            Payment::create([
                'order_id' => $order->id,
                'transaction_id' => $charge->id,
                'payment_method' => 'stripe',
                'amount' => $order->total,
                'currency' => 'usd',
                'status' => 'completed',
                'payment_data' => json_encode($charge),
                'processed_at' => now(),
            ]);

            $order->update([
                'payment_status' => 'paid',
                'paid_at' => now(),
                'status' => 'processing',
            ]);

            $cart = Cart::where('user_id', auth()->id())->first();
            if ($cart) {
                $cart->items()->delete();
                $cart->delete();
            }

            DB::commit();

            Mail::to($order->user->email)->send(new OrderPlaced($order));

            return redirect()->route('payment.success', $order)->with('success', 'Payment successful!');
        } catch (\Exception $e) {
            DB::rollBack();

            Payment::create([
                'order_id' => $order->id,
                'transaction_id' => 'FAILED-'.strtoupper(Str::random(10)),
                'payment_method' => 'stripe',
                'amount' => $order->total,
                'currency' => 'usd',
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'processed_at' => now(),
            ]);

            $order->update(['payment_status' => 'failed']);

            return redirect()->route('payment.failed', $order)->with('error', 'Payment failed: '.$e->getMessage());
        }
    }

    public function paystack(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        if (! Setting::get('paystack_enabled', false)) {
            return redirect()->route('cart.index')->with('error', 'Paystack payment is currently unavailable');
        }

        $paystackPublicKey = Setting::get('paystack_public_key');

        if (! $paystackPublicKey) {
            return redirect()->route('cart.index')->with('error', 'Payment gateway not configured');
        }

        return view('payment.paystack', compact('order', 'paystackPublicKey'));
    }

    public function paystackCallback(Request $request, Order $order)
    {
        $reference = $request->reference;

        if (! $reference) {
            return redirect()->route('payment.failed', $order)->with('error', 'Payment reference not found');
        }

        $paystackSecretKey = Setting::get('paystack_secret_key');

        if (! $paystackSecretKey) {
            return redirect()->back()->with('error', 'Payment gateway not configured');
        }

        try {
            $curl = curl_init();

            curl_setopt_array($curl, [
                CURLOPT_URL => 'https://api.paystack.co/transaction/verify/'.$reference,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer '.$paystackSecretKey,
                    'Content-Type: application/json',
                ],
            ]);

            $response = curl_exec($curl);
            curl_close($curl);

            $result = json_decode($response, true);

            if ($result['status'] && $result['data']['status'] === 'success') {
                DB::beginTransaction();

                Payment::create([
                    'order_id' => $order->id,
                    'transaction_id' => $result['data']['reference'],
                    'payment_method' => 'paystack',
                    'amount' => $order->total,
                    'currency' => $result['data']['currency'],
                    'status' => 'completed',
                    'payment_data' => json_encode($result['data']),
                    'processed_at' => now(),
                ]);

                $order->update([
                    'payment_status' => 'paid',
                    'paid_at' => now(),
                    'status' => 'processing',
                ]);

                $cart = Cart::where('user_id', auth()->id())->first();
                if ($cart) {
                    $cart->items()->delete();
                    $cart->delete();
                }

                DB::commit();

                Mail::to($order->user->email)->send(new OrderPlaced($order));

                return redirect()->route('payment.success', $order)->with('success', 'Payment successful!');
            } else {
                Payment::create([
                    'order_id' => $order->id,
                    'transaction_id' => $reference,
                    'payment_method' => 'paystack',
                    'amount' => $order->total,
                    'currency' => 'usd',
                    'status' => 'failed',
                    'error_message' => $result['message'] ?? 'Payment verification failed',
                    'processed_at' => now(),
                ]);

                $order->update(['payment_status' => 'failed']);

                return redirect()->route('payment.failed', $order)->with('error', 'Payment verification failed');
            }
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('payment.failed', $order)->with('error', 'Payment verification failed: '.$e->getMessage());
        }
    }

    public function success(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        return view('payment.success', compact('order'));
    }

    public function failed(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        return view('payment.failed', compact('order'));
    }

    public function momo(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        if (! Setting::get('momo_enabled', false)) {
            return redirect()->route('cart.index')->with('error', 'Mobile Money payment is currently unavailable');
        }

        return view('payment.momo', compact('order'));
    }

    public function momoProcess(Request $request, Order $order)
    {
        $validated = $request->validate([
            'phone_number' => 'required|string',
        ]);

        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $momoApiKey = Setting::get('momo_api_key');
        $momoApiSecret = Setting::get('momo_api_secret');
        $momoUserId = Setting::get('momo_user_id');
        $momoEnvironment = Setting::get('momo_environment', 'sandbox');

        if (! $momoApiKey || ! $momoApiSecret || ! $momoUserId) {
            return redirect()->back()->with('error', 'Mobile Money payment gateway not configured');
        }

        try {
            $baseUrl = $momoEnvironment === 'production'
                ? 'https://proxy.momoapi.mtn.com'
                : 'https://sandbox.momodeveloper.mtn.com';

            $referenceId = Str::uuid()->toString();

            $response = Http::withHeaders([
                'X-Reference-Id' => $referenceId,
                'X-Target-Environment' => $momoEnvironment,
                'Ocp-Apim-Subscription-Key' => $momoApiKey,
                'Authorization' => 'Bearer '.base64_encode($momoUserId.':'.$momoApiSecret),
                'Content-Type' => 'application/json',
            ])->post($baseUrl.'/collection/v1_0/requesttopay', [
                'amount' => (string) $order->total,
                'currency' => 'EUR',
                'externalId' => $order->order_number,
                'payer' => [
                    'partyIdType' => 'MSISDN',
                    'partyId' => $validated['phone_number'],
                ],
                'payerMessage' => 'Payment for order #'.$order->order_number,
                'payeeNote' => 'African Market Order Payment',
            ]);

            if ($response->successful() || $response->status() === 202) {
                DB::beginTransaction();

                Payment::create([
                    'order_id' => $order->id,
                    'transaction_id' => $referenceId,
                    'payment_method' => 'mobile_money',
                    'amount' => $order->total,
                    'currency' => 'EUR',
                    'status' => 'pending',
                    'payment_data' => json_encode([
                        'phone_number' => $validated['phone_number'],
                        'reference_id' => $referenceId,
                    ]),
                    'processed_at' => now(),
                ]);

                $order->update([
                    'payment_status' => 'pending',
                ]);

                $cart = Cart::where('user_id', auth()->id())->first();
                if ($cart) {
                    $cart->items()->delete();
                    $cart->delete();
                }

                DB::commit();

                return redirect()->route('orders.show', $order)->with('success', 'Mobile Money payment initiated. Please check your phone to complete payment.');
            }

            return redirect()->back()->with('error', 'Failed to initiate Mobile Money payment. Please try again.');
        } catch (\Exception $e) {
            Log::error('MOMO payment error: '.$e->getMessage());

            return redirect()->back()->with('error', 'Payment processing failed: '.$e->getMessage());
        }
    }
}
