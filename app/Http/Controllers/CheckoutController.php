<?php

namespace App\Http\Controllers;

use App\Mail\OrderPlaced;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderGroup;
use App\Models\Payment;
use App\Models\Setting;
use App\Services\Campaign\CampaignTrackingService;
use App\Services\Shipping\ShippingCalculatorService;
use App\Services\SmsService;
use App\Services\SwishService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function __construct(private readonly ShippingCalculatorService $shippingCalculator) {}

    public function index()
    {
        $cart = Cart::where('user_id', auth()->id())->first();

        if (! $cart || $cart->items->count() === 0) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty');
        }

        $cart->load(['items.product', 'items.variant']);
        $cart->calculateTotals();
        $coupon = $cart->coupon_code ? Coupon::where('code', $cart->coupon_code)->first() : null;
        $addresses = Address::where('user_id', auth()->id())->get();
        $defaultShippingAddress = $addresses
            ->whereIn('type', ['shipping', 'both'])
            ->sortByDesc(fn (Address $address) => (int) $address->is_default)
            ->first();

        $shippingPreview = $this->buildShippingPreview($cart, $defaultShippingAddress);

        // Check if user is from a Nordic country for SWISH payment
        $isNordicCountry = false;
        $swishEnabled = Setting::getValue('swish_enabled', false);
        if ($swishEnabled && $defaultShippingAddress) {
            $isNordicCountry = SwishService::isNordicCountry($defaultShippingAddress->country);
        } elseif ($swishEnabled) {
            // Also check billing address if no shipping address
            $defaultBillingAddress = $addresses
                ->whereIn('type', ['billing', 'both'])
                ->sortByDesc(fn (Address $address) => (int) $address->is_default)
                ->first();
            if ($defaultBillingAddress) {
                $isNordicCountry = SwishService::isNordicCountry($defaultBillingAddress->country);
            }
        }

        return view('checkout.index', compact('cart', 'addresses', 'shippingPreview', 'isNordicCountry'));
    }

    public function shippingPreview(Request $request)
    {
        $validated = $request->validate([
            'address_id' => 'required|integer',
        ]);

        $cart = Cart::where('user_id', auth()->id())->first();
        if (! $cart || $cart->items->count() === 0) {
            return response()->json(['message' => 'Cart is empty'], 422);
        }

        $cart->load(['items.product', 'items.variant']);
        $cart->calculateTotals();

        $address = Address::where('user_id', auth()->id())->findOrFail((int) $validated['address_id']);
        $preview = $this->buildShippingPreview($cart, $address);

        return response()->json($preview);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'billing_address_id' => 'required|integer',
            'shipping_address_id' => 'required|integer',
            'payment_method' => 'required|in:stripe,paystack,mobile_money,cash_on_delivery,swish',
            'customer_notes' => 'nullable|string',
            'phone_number' => 'required_if:payment_method,mobile_money|nullable|string',
            'mobile_money_provider' => 'required_if:payment_method,mobile_money|nullable|in:mtn,mpesa',
        ]);

        $cart = Cart::where('user_id', auth()->id())->first();

        if (! $cart || $cart->items->count() === 0) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty');
        }

        $cart->load(['items.product', 'items.variant']);
        $cart->calculateTotals();
        $coupon = $cart->coupon_code ? Coupon::where('code', $cart->coupon_code)->first() : null;

        DB::beginTransaction();

        try {
            $couponVendorId = $coupon?->vendor_id;
            $billingAddress = Address::where('user_id', auth()->id())->findOrFail((int) $validated['billing_address_id']);
            $shippingAddress = Address::where('user_id', auth()->id())->findOrFail((int) $validated['shipping_address_id']);
            $shippingCalculation = $this->shippingCalculator->calculate((float) $cart->subtotal, $shippingAddress);
            $calculatedShippingCost = (float) $shippingCalculation['cost'];
            $calculatedTotal = max(0, (float) $cart->subtotal + (float) $cart->tax + $calculatedShippingCost - (float) $cart->discount);

            $groupNumber = 'GRP-'.strtoupper(Str::random(8));
            $orderGroup = OrderGroup::create([
                'group_number' => $groupNumber,
                'user_id' => auth()->id(),
                'subtotal' => $cart->subtotal,
                'tax' => $cart->tax,
                'shipping_cost' => $calculatedShippingCost,
                'discount' => $cart->discount,
                'total' => $calculatedTotal,
            ]);

            $orderNumber = 'ORD-'.strtoupper(Str::random(10));
            $order = Order::create([
                'order_number' => $orderNumber,
                'order_group_id' => $orderGroup->id,
                'is_parent' => true,
                'user_id' => auth()->id(),
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
                    'user_id' => auth()->id(),
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

            foreach ($cart->items as $item) {
                $product = $item->product;
                $product->stock_quantity -= $item->quantity;
                $product->sales_count += $item->quantity;
                $product->save();
            }

            app(CampaignTrackingService::class)->attachAttribution(
                $order,
                $request->session()->getId(),
                auth()->user()?->email,
                auth()->id()
            );

            if ($validated['payment_method'] === 'cash_on_delivery') {
                Payment::create([
                    'order_id' => $order->id,
                    'transaction_id' => 'COD-'.strtoupper(Str::random(10)),
                    'payment_method' => 'cash_on_delivery',
                    'amount' => $order->total,
                    'currency' => 'USD',
                    'status' => 'pending',
                    'processed_at' => now(),
                ]);

                $cart->items()->delete();
                $cart->delete();

                DB::commit();

                Mail::to($order->user->email)->send(new OrderPlaced($order));

                $smsService = app(SmsService::class);
                if ($order->user->phone) {
                    $smsService->sendOrderConfirmation(
                        $order->user->phone,
                        $order->order_number,
                        $order->total_amount
                    );
                }

                return redirect()->route('orders.show', $order)->with('success', 'Order placed successfully! You will pay on delivery.');
            }

            DB::commit();

            if ($validated['payment_method'] === 'stripe') {
                return redirect()->route('payment.stripe', $order);
            } elseif ($validated['payment_method'] === 'paystack') {
                return redirect()->route('payment.paystack', $order);
            } elseif ($validated['payment_method'] === 'mobile_money') {
                return redirect()->route('payment.momo', [
                    'order' => $order,
                    'provider' => $validated['mobile_money_provider'] ?? 'mtn',
                ]);
            } elseif ($validated['payment_method'] === 'swish') {
                return redirect()->route('payment.swish', $order);
            }
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Failed to create order: '.$e->getMessage());
        }
    }

    private function buildShippingPreview(Cart $cart, ?Address $address): array
    {
        $shippingCalculation = $this->shippingCalculator->calculate((float) $cart->subtotal, $address);
        $shipping = [
            'subtotal' => (float) $cart->subtotal,
            'tax' => (float) $cart->tax,
            'shipping' => (float) $shippingCalculation['cost'],
            'discount' => (float) $cart->discount,
            'mode' => (string) ($shippingCalculation['mode'] ?? 'flat'),
        ];
        $shipping['total'] = max(0, $shipping['subtotal'] + $shipping['tax'] + $shipping['shipping'] - $shipping['discount']);
        $shipping['label'] = match ($shipping['mode']) {
            'free_threshold' => 'Free Shipping',
            'disabled' => 'Shipping Disabled',
            'zone' => 'Zone Delivery',
            'zone_fallback_flat' => 'Standard Shipping',
            default => 'Standard Shipping',
        };

        return $shipping;
    }
}
