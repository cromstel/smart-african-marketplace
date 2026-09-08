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
use Stripe\Charge;
use Stripe\Stripe;

class PaymentController extends Controller
{
    public function stripe(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        if (! Setting::getValue('stripe_enabled', false)) {
            return redirect()->route('cart.index')->with('error', 'Stripe payment is currently unavailable');
        }

        $stripePublicKey = Setting::getValue('stripe_public_key');

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

        $stripeSecretKey = Setting::getValue('stripe_secret_key');

        if (! $stripeSecretKey) {
            return redirect()->back()->with('error', 'Payment gateway not configured');
        }

        try {
            Stripe::setApiKey($stripeSecretKey);

            $charge = Charge::create([
                'amount' => $order->total * 100,
                'currency' => $order->currency ?? 'usd',
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
                'currency' => $order->currency ?? 'usd',
                'status' => 'completed',
                'payment_data' => json_encode($charge),
                'processed_at' => now(),
            ]);

            $order->update([
                'payment_status' => 'paid',
                'paid_at' => now(),
                'status' => 'processing',
            ]);
            if ($order->order_group_id) {
                $children = Order::where('order_group_id', $order->order_group_id)
                    ->where('is_parent', false)
                    ->get();
                foreach ($children as $child) {
                    $child->update([
                        'payment_status' => 'paid',
                        'paid_at' => now(),
                        'status' => 'processing',
                    ]);
                }
            }

            $cart = Cart::where('user_id', auth()->id())->first();
            if ($cart) {
                $cart->items()->delete();
                $cart->delete();
            }

            DB::commit();

            if ($order->user && $order->user->email) {
                Mail::to($order->user->email)->send(new OrderPlaced($order));
            }

            return redirect()->route('payment.success', $order)->with('success', 'Payment successful!');
        } catch (\Exception $e) {
            DB::rollBack();

            Payment::create([
                'order_id' => $order->id,
                'transaction_id' => 'FAILED-'.strtoupper(Str::random(10)),
                'payment_method' => 'stripe',
                'amount' => $order->total,
                'currency' => $order->currency ?? 'usd',
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

        if (! Setting::getValue('paystack_enabled', false)) {
            return redirect()->route('cart.index')->with('error', 'Paystack payment is currently unavailable');
        }

        $paystackPublicKey = Setting::getValue('paystack_public_key');

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

        $paystackSecretKey = Setting::getValue('paystack_secret_key');

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

            if ($result && ($result['status'] ?? false) && ($result['data']['status'] ?? '') === 'success') {
                DB::beginTransaction();

                Payment::create([
                    'order_id' => $order->id,
                    'transaction_id' => $result['data']['reference'] ?? $reference,
                    'payment_method' => 'paystack',
                    'amount' => $order->total,
                    'currency' => $result['data']['currency'] ?? 'usd',
                    'status' => 'completed',
                    'payment_data' => json_encode($result['data'] ?? []),
                    'processed_at' => now(),
                ]);

                $order->update([
                    'payment_status' => 'paid',
                    'paid_at' => now(),
                    'status' => 'processing',
                ]);
                if ($order->order_group_id) {
                    $children = Order::where('order_group_id', $order->order_group_id)
                        ->where('is_parent', false)
                        ->get();
                    foreach ($children as $child) {
                        $child->update([
                            'payment_status' => 'paid',
                            'paid_at' => now(),
                            'status' => 'processing',
                        ]);
                    }
                }

                $cart = Cart::where('user_id', auth()->id())->first();
                if ($cart) {
                    $cart->items()->delete();
                    $cart->delete();
                }

                DB::commit();

                if ($order->user && $order->user->email) {
                    Mail::to($order->user->email)->send(new OrderPlaced($order));
                }

                return redirect()->route('payment.success', $order)->with('success', 'Payment successful!');
            } else {
                Payment::create([
                    'order_id' => $order->id,
                    'transaction_id' => $reference,
                    'payment_method' => 'paystack',
                    'amount' => $order->total,
                    'currency' => $result['data']['currency'] ?? 'usd',
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

    public function momo(Request $request, Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        if (! Setting::getValue('momo_enabled', false)) {
            return redirect()->route('cart.index')->with('error', 'Mobile Money payment is currently unavailable');
        }

        $provider = strtolower((string) $request->query('provider', old('provider', 'mtn')));
        if (! in_array($provider, ['mtn', 'mpesa'], true)) {
            $provider = 'mtn';
        }

        return view('payment.momo', compact('order', 'provider'));
    }

    public function momoProcess(Request $request, Order $order)
    {
        $validated = $request->validate([
            'phone_number' => 'required|string',
            'provider' => 'required|in:mtn,mpesa',
        ]);

        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        try {
            $referenceId = Str::uuid()->toString();
            $provider = $validated['provider'];
            $response = $provider === 'mpesa'
                ? $this->requestMpesaPayment($order, $validated['phone_number'], $referenceId)
                : $this->requestMtnPayment($order, $validated['phone_number'], $referenceId);

            if ($response->successful() || $response->status() === 202) {
                DB::beginTransaction();
                $providerReference = $referenceId;
                if ($provider === 'mpesa' && $response->json('CheckoutRequestID')) {
                    $providerReference = (string) $response->json('CheckoutRequestID');
                }

                Payment::create([
                    'order_id' => $order->id,
                    'transaction_id' => $providerReference,
                    'payment_method' => 'mobile_money',
                    'amount' => $order->total,
                    'currency' => $provider === 'mpesa' ? 'KES' : 'EUR',
                    'status' => 'pending',
                    'payment_data' => json_encode([
                        'phone_number' => $validated['phone_number'],
                        'reference_id' => $referenceId,
                        'provider_reference' => $providerReference,
                        'provider' => $provider,
                        'gateway_response' => $response->json(),
                    ]),
                    'processed_at' => now(),
                ]);

                $order->update([
                    'payment_status' => 'pending',
                ]);
                if ($order->order_group_id) {
                    $children = Order::where('order_group_id', $order->order_group_id)
                        ->where('is_parent', false)
                        ->get();
                    foreach ($children as $child) {
                        $child->update([
                            'payment_status' => 'pending',
                        ]);
                    }
                }

                $cart = Cart::where('user_id', auth()->id())->first();
                if ($cart) {
                    $cart->items()->delete();
                    $cart->delete();
                }

                DB::commit();

                $providerLabel = strtoupper($provider === 'mpesa' ? 'M-Pesa' : 'MTN MoMo');

                return redirect()->route('orders.show', $order)->with('success', "{$providerLabel} payment initiated. Please check your phone to complete payment.");
            }

            return redirect()->back()->with('error', 'Failed to initiate Mobile Money payment. Please try again.');
        } catch (\Exception $e) {
            Log::error('MOMO payment error: '.$e->getMessage());

            return redirect()->back()->with('error', 'Payment processing failed: '.$e->getMessage());
        }
    }

    private function requestMtnPayment(Order $order, string $phoneNumber, string $referenceId)
    {
        $apiKey = Setting::getValue('momo_api_key');
        $apiSecret = Setting::getValue('momo_api_secret');
        $userId = Setting::getValue('momo_user_id');
        $environment = Setting::getValue('momo_environment', 'sandbox');

        if (! $apiKey || ! $apiSecret || ! $userId) {
            throw new \RuntimeException('MTN Mobile Money gateway not configured');
        }

        $baseUrl = $environment === 'production'
            ? 'https://proxy.momoapi.mtn.com'
            : 'https://sandbox.momodeveloper.mtn.com';

        return Http::withHeaders([
            'X-Reference-Id' => $referenceId,
            'X-Target-Environment' => $environment,
            'Ocp-Apim-Subscription-Key' => $apiKey,
            'Authorization' => 'Bearer '.base64_encode($userId.':'.$apiSecret),
            'Content-Type' => 'application/json',
        ])->timeout(30)->post($baseUrl.'/collection/v1_0/requesttopay', [
            'amount' => (string) $order->total,
            'currency' => 'EUR',
            'externalId' => $order->order_number,
            'payer' => [
                'partyIdType' => 'MSISDN',
                'partyId' => $phoneNumber,
            ],
            'payerMessage' => 'Payment for order #'.$order->order_number,
            'payeeNote' => 'Marketplace Order Payment',
        ]);
    }

    private function requestMpesaPayment(Order $order, string $phoneNumber, string $referenceId)
    {
        $consumerKey = Setting::getValue('mpesa_consumer_key');
        $consumerSecret = Setting::getValue('mpesa_consumer_secret');
        $shortcode = Setting::getValue('mpesa_shortcode');
        $passkey = Setting::getValue('mpesa_passkey');
        $callbackUrl = rtrim((string) config('app.url'), '/').'/webhooks/momo/mpesa';
        $environment = Setting::getValue('mpesa_environment', 'sandbox');

        if (! $consumerKey || ! $consumerSecret || ! $shortcode || ! $passkey) {
            throw new \RuntimeException('M-Pesa gateway not configured');
        }

        $baseUrl = $environment === 'production'
            ? 'https://api.safaricom.co.ke'
            : 'https://sandbox.safaricom.co.ke';

        $oauth = Http::withBasicAuth($consumerKey, $consumerSecret)
            ->timeout(30)
            ->asForm()
            ->post($baseUrl.'/oauth/v1/generate?grant_type=client_credentials');

        if (! $oauth->successful() || empty($oauth->json('access_token'))) {
            throw new \RuntimeException('Failed to obtain M-Pesa access token');
        }

        $accessToken = $oauth->json('access_token');
        $timestamp = now()->format('YmdHis');
        $password = base64_encode($shortcode.$passkey.$timestamp);

        return Http::withToken($accessToken)
            ->timeout(30)
            ->post($baseUrl.'/mpesa/stkpush/v1/processrequest', [
                'BusinessShortCode' => $shortcode,
                'Password' => $password,
                'Timestamp' => $timestamp,
                'TransactionType' => 'CustomerPayBillOnline',
                'Amount' => (int) round((float) $order->total),
                'PartyA' => $phoneNumber,
                'PartyB' => $shortcode,
                'PhoneNumber' => $phoneNumber,
                'CallBackURL' => $callbackUrl,
                'AccountReference' => $order->order_number,
                'TransactionDesc' => 'Order Payment',
                'ReferenceData' => ['Reference' => $referenceId],
            ]);
    }
}
