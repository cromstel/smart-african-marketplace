<?php

namespace App\Http\Controllers;

use App\Mail\OrderPlaced;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function index()
    {
        $cart = Cart::where('user_id', auth()->id())->first();

        if (! $cart || $cart->items->count() === 0) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty');
        }

        $cart->load(['items.product', 'items.variant']);
        $addresses = Address::where('user_id', auth()->id())->get();

        return view('checkout.index', compact('cart', 'addresses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'billing_address_id' => 'required|exists:addresses,id',
            'shipping_address_id' => 'required|exists:addresses,id',
            'payment_method' => 'required|in:stripe,paystack,mobile_money,cash_on_delivery',
            'customer_notes' => 'nullable|string',
            'phone_number' => 'required_if:payment_method,mobile_money|nullable|string',
        ]);

        $cart = Cart::where('user_id', auth()->id())->first();

        if (! $cart || $cart->items->count() === 0) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty');
        }

        DB::beginTransaction();

        try {
            $orderNumber = 'ORD-'.strtoupper(Str::random(10));

            $order = Order::create([
                'order_number' => $orderNumber,
                'user_id' => auth()->id(),
                'billing_address_id' => $validated['billing_address_id'],
                'shipping_address_id' => $validated['shipping_address_id'],
                'subtotal' => $cart->subtotal,
                'tax' => $cart->tax,
                'shipping_cost' => $cart->shipping,
                'discount' => $cart->discount,
                'total' => $cart->total,
                'coupon_code' => $cart->coupon_code,
                'payment_method' => $validated['payment_method'],
                'payment_status' => $validated['payment_method'] === 'cash_on_delivery' ? 'pending' : 'pending',
                'status' => 'pending',
                'customer_notes' => $validated['customer_notes'],
            ]);

            foreach ($cart->items as $item) {
                $order->items()->create([
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

                $product = $item->product;
                $product->stock_quantity -= $item->quantity;
                $product->sales_count += $item->quantity;
                $product->save();
            }

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
                return redirect()->route('payment.momo', $order);
            }
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Failed to create order: '.$e->getMessage());
        }
    }
}
