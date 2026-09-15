<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Mail\OrderPlaced;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderGroup;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Services\Payment\PaystackPaymentService;
use App\Services\Shipping\ShippingCalculatorService;
use App\Services\SmsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Stripe\Charge;
use Stripe\Stripe;

class CheckoutController extends Controller
{
    public function __construct(private readonly ShippingCalculatorService $shippingCalculator) {}

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'billing_address_id' => 'required|integer',
            'shipping_address_id' => 'required|integer',
            'payment_method' => 'required|in:stripe,paystack,mobile_money,cash_on_delivery,swish',
            'customer_notes' => 'nullable|string',
            'phone_number' => 'required_if:payment_method,mobile_money|nullable|string',
            'mobile_money_provider' => 'required_if:payment_method,mobile_money|nullable|in:mtn,mpesa',
            'stripe_token' => 'required_if:payment_method,stripe|nullable|string',
        ]);

        $user = $request->user();
        $cart = Cart::where('user_id', $user->id)->first();

        if (! $cart || $cart->items->count() === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Your cart is empty',
            ], 422);
        }

        $cart->load(['items.product', 'items.variant']);
        $cart->calculateTotals();
        $coupon = $cart->coupon_code ? Coupon::where('code', $cart->coupon_code)->first() : null;

        DB::beginTransaction();

        try {
            $couponVendorId = $coupon?->vendor_id;
            $billingAddress = Address::where('user_id', $user->id)->findOrFail((int) $validated['billing_address_id']);
            $shippingAddress = Address::where('user_id', $user->id)->findOrFail((int) $validated['shipping_address_id']);
            $shippingCalculation = $this->shippingCalculator->calculate((float) $cart->subtotal, $shippingAddress);
            $calculatedShippingCost = (float) $shippingCalculation['cost'];
            $calculatedTotal = max(0, (float) $cart->subtotal + (float) $cart->tax + $calculatedShippingCost - (float) $cart->discount);

            $orderGroup = OrderGroup::create([
                'group_number' => 'GRP-'.strtoupper(Str::random(8)),
                'user_id' => $user->id,
                'subtotal' => $cart->subtotal,
                'tax' => $cart->tax,
                'shipping_cost' => $calculatedShippingCost,
                'discount' => $cart->discount,
                'total' => $calculatedTotal,
            ]);

            $order = Order::create([
                'order_number' => 'ORD-'.strtoupper(Str::random(10)),
                'order_group_id' => $orderGroup->id,
                'is_parent' => true,
                'user_id' => $user->id,
                'billing_address_id' => $billingAddress->id,
                'shipping_address_id' => $shippingAddress->id,
                'subtotal' => $cart->subtotal,
                'tax' => $cart->tax,
                'shipping_cost' => $calculatedShippingCost,
                'discount' => $cart->discount,
                'total' => $calculatedTotal,
                'coupon_code' => $cart->coupon_code,
                'payment_method' => $validated['payment_method'],
                'payment_status' => 'pending',
                'status' => 'pending',
                'customer_notes' => $validated['customer_notes'] ?? null,
            ]);

            $itemsByVendor = $cart->items->groupBy(fn ($item) => $item->product->vendor_id);
            foreach ($itemsByVendor as $vendorId => $items) {
                if (! $vendorId) {
                    continue;
                }

                $vendorSubtotal = $items->sum('subtotal');
                $ratio = $cart->subtotal > 0 ? $vendorSubtotal / $cart->subtotal : 0;
                $vendorShipping = $calculatedShippingCost * $ratio;
                $vendorTax = $cart->tax * $ratio;
                $vendorDiscount = $couponVendorId
                    ? ($vendorId == $couponVendorId ? $cart->discount : 0)
                    : $cart->discount * $ratio;
                $vendorTotal = max(0, $vendorSubtotal + $vendorTax + $vendorShipping - $vendorDiscount);

                $childOrder = Order::create([
                    'order_number' => 'ORD-'.strtoupper(Str::random(10)),
                    'order_group_id' => $orderGroup->id,
                    'is_parent' => false,
                    'user_id' => $user->id,
                    'billing_address_id' => $billingAddress->id,
                    'shipping_address_id' => $shippingAddress->id,
                    'subtotal' => $vendorSubtotal,
                    'tax' => $vendorTax,
                    'shipping_cost' => $vendorShipping,
                    'discount' => $vendorDiscount,
                    'total' => $vendorTotal,
                    'coupon_code' => $cart->coupon_code,
                    'payment_method' => $validated['payment_method'],
                    'payment_status' => 'pending',
                    'status' => 'pending',
                    'customer_notes' => $validated['customer_notes'] ?? null,
                ]);

                foreach ($items as $item) {
                    $childOrder->items()->create([
                        'product_id' => $item->product_id,
                        'product_variant_id' => $item->product_variant_id,
                        'vendor_id' => $item->product->vendor_id,
                        'product_name' => $item->product->name,
                        'product_sku' => $item->product->sku,
                        'product_attributes' => $item->variant ? ['variant' => $item->variant->name] : null,
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'subtotal' => $item->subtotal,
                        'tax' => 0,
                        'total' => $item->subtotal,
                    ]);
                }
            }

            // Lock each product row for the duration of the transaction and
            // re-check stock immediately before decrementing it. Without this,
            // two concurrent checkouts (or stock that changed after the item
            // was added to the cart) could both pass and drive stock negative.
            foreach ($cart->items as $item) {
                $product = Product::whereKey($item->product_id)->lockForUpdate()->first();

                if (! $product || $product->stock_quantity < $item->quantity) {
                    $name = $product?->name ?? $item->product->name ?? 'this item';
                    $available = $product?->stock_quantity ?? 0;

                    throw new \RuntimeException("Not enough stock for \"{$name}\". Only {$available} unit(s) left.");
                }

                $product->stock_quantity -= $item->quantity;
                $product->sales_count += $item->quantity;
                $product->save();
            }

            $paymentResult = $this->initiatePayment($order, $validated, $user, $cart);

            if (isset($paymentResult['error'])) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => $paymentResult['error'],
                ], 422);
            }

            // Clear the cart once the order (and payment initiation) succeeds to avoid
            // orphaned / duplicate carts for the stateless mobile client. The web
            // payment callbacks also clear the cart, so this is a safe no-op there.
            $cart->items()->delete();
            $cart->delete();

            DB::commit();

            if ($validated['payment_method'] === 'cash_on_delivery') {
                try {
                    Mail::to($order->user->email)->send(new OrderPlaced($order));

                    if ($order->user->phone) {
                        app(SmsService::class)->sendOrderConfirmation(
                            $order->user->phone,
                            $order->order_number,
                            (float) $order->total
                        );
                    }
                } catch (\Exception $e) {
                    Log::error('Order confirmation (email/SMS) failed: '.$e->getMessage());
                }
            }

            $order->load(['items.product.images', 'billingAddress', 'shippingAddress', 'payment']);

            return response()->json([
                'success' => true,
                'data' => [
                    'order' => new OrderResource($order),
                    'payment' => $paymentResult['payload'],
                ],
                'message' => $paymentResult['message'],
            ], $paymentResult['http_status']);
        } catch (\RuntimeException $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create order: '.$e->getMessage(),
            ], 500);
        }
    }

    public function shippingPreview(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'address_id' => 'required|integer',
        ]);

        $user = $request->user();
        $cart = Cart::where('user_id', $user->id)->first();

        if (! $cart || $cart->items->count() === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cart is empty',
            ], 422);
        }

        $cart->load(['items.product', 'items.variant']);
        $cart->calculateTotals();

        $address = Address::where('user_id', $user->id)->findOrFail((int) $validated['address_id']);
        $calc = $this->shippingCalculator->calculate((float) $cart->subtotal, $address);
        $total = max(0, (float) $cart->subtotal + (float) $cart->tax + (float) $calc['cost'] - (float) $cart->discount);

        return response()->json([
            'success' => true,
            'data' => [
                'subtotal' => (float) $cart->subtotal,
                'tax' => (float) $cart->tax,
                'shipping' => (float) $calc['cost'],
                'discount' => (float) $cart->discount,
                'total' => $total,
                'mode' => (string) ($calc['mode'] ?? 'flat'),
            ],
            'message' => 'Shipping preview retrieved successfully',
        ]);
    }

    /**
     * Initialize payment for the chosen method. Always returns a payload the
     * mobile client can act on (Paystack URL, Mobile Money reference, etc.).
     */
    private function initiatePayment(Order $order, array $validated, User $user, Cart $cart): array
    {
        $method = $validated['payment_method'];
        $currency = (string) Setting::get('default_currency', 'NGN');

        if ($method === 'cash_on_delivery') {
            $payment = Payment::create([
                'order_id' => $order->id,
                'transaction_id' => 'COD-'.strtoupper(Str::random(10)),
                'payment_method' => 'cash_on_delivery',
                'amount' => $order->total,
                'currency' => $currency,
                'status' => 'pending',
                'processed_at' => now(),
            ]);

            return [
                'http_status' => 201,
                'message' => 'Order placed successfully. You will pay on delivery.',
                'payload' => [
                    'method' => 'cash_on_delivery',
                    'status' => 'pending',
                    'transaction_id' => $payment->transaction_id,
                ],
            ];
        }

        if ($method === 'paystack') {
            $paystack = app(PaystackPaymentService::class);

            if (! $paystack->isConfigured()) {
                return ['error' => 'Paystack payment is not configured'];
            }

            $init = $paystack->initializeTransaction(
                (float) $order->total,
                $user->email,
                $currency,
                [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'cart_id' => $cart->id,
                ]
            );

            Payment::create([
                'order_id' => $order->id,
                'transaction_id' => $init['reference'],
                'payment_method' => 'paystack',
                'amount' => $order->total,
                'currency' => $currency,
                'status' => 'pending',
                'payment_data' => json_encode($init),
                'processed_at' => now(),
            ]);

            return [
                'http_status' => 201,
                'message' => 'Order created. Complete Paystack payment to confirm your order.',
                'payload' => [
                    'method' => 'paystack',
                    'status' => 'pending',
                    'authorization_url' => $init['authorization_url'],
                    'access_code' => $init['access_code'],
                    'reference' => $init['reference'],
                ],
            ];
        }

        if ($method === 'mobile_money') {
            $provider = strtolower((string) ($validated['mobile_money_provider'] ?? 'mtn'));
            if (! in_array($provider, ['mtn', 'mpesa'], true)) {
                $provider = 'mtn';
            }

            $referenceId = (string) Str::uuid();
            $phone = (string) $validated['phone_number'];

            try {
                $response = $provider === 'mpesa'
                    ? $this->requestMpesaPayment($order, $phone, $referenceId)
                    : $this->requestMtnPayment($order, $phone, $referenceId);
            } catch (\RuntimeException $e) {
                return ['error' => $e->getMessage()];
            }

            if (! ($response->successful() || $response->status() === 202)) {
                throw new \Exception('Failed to initiate Mobile Money payment. Please try again.');
            }

            $providerReference = ($provider === 'mpesa' && $response->json('CheckoutRequestID'))
                ? (string) $response->json('CheckoutRequestID')
                : $referenceId;
            $mmCurrency = $provider === 'mpesa' ? 'KES' : 'EUR';

            Payment::create([
                'order_id' => $order->id,
                'transaction_id' => $providerReference,
                'payment_method' => 'mobile_money',
                'amount' => $order->total,
                'currency' => $mmCurrency,
                'status' => 'pending',
                'payment_data' => json_encode([
                    'phone_number' => $phone,
                    'reference_id' => $referenceId,
                    'provider_reference' => $providerReference,
                    'provider' => $provider,
                    'gateway_response' => $response->json(),
                ]),
                'processed_at' => now(),
            ]);

            return [
                'http_status' => 201,
                'message' => 'Mobile Money payment initiated. Please check your phone to complete the payment.',
                'payload' => [
                    'method' => 'mobile_money',
                    'status' => 'pending',
                    'provider' => $provider,
                    'provider_reference' => $providerReference,
                ],
            ];
        }

        if ($method === 'stripe') {
            $stripeSecretKey = Setting::getValue('stripe_secret_key');
            if (! $stripeSecretKey) {
                return ['error' => 'Stripe payment is not configured'];
            }

            Stripe::setApiKey($stripeSecretKey);

            $charge = Charge::create([
                'amount' => (int) round((float) $order->total * 100),
                'currency' => strtolower($currency),
                'description' => 'Order #'.$order->order_number,
                'source' => $validated['stripe_token'],
                'metadata' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                ],
            ]);

            Payment::create([
                'order_id' => $order->id,
                'transaction_id' => $charge->id,
                'payment_method' => 'stripe',
                'amount' => $order->total,
                'currency' => strtolower($currency),
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
                Order::where('order_group_id', $order->order_group_id)
                    ->where('is_parent', false)
                    ->update([
                        'payment_status' => 'paid',
                        'paid_at' => now(),
                        'status' => 'processing',
                    ]);
            }

            return [
                'http_status' => 201,
                'message' => 'Payment successful! Your order is confirmed.',
                'payload' => [
                    'method' => 'stripe',
                    'status' => 'completed',
                    'transaction_id' => $charge->id,
                ],
            ];
        }

        // swish is Nordic-only and requires the website checkout
        return ['error' => 'Swish payments are only available via the website checkout'];
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
