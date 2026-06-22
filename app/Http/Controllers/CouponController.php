<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function apply(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string',
        ]);

        $cart = Cart::where('user_id', auth()->id())->first();

        if (! $cart || $cart->items->count() === 0) {
            return redirect()->back()->with('error', 'Your cart is empty');
        }

        $coupon = Coupon::where('code', strtoupper($validated['code']))->first();

        if (! $coupon) {
            return redirect()->back()->with('error', 'Invalid coupon code');
        }

        $cartSubtotal = $cart->subtotal;
        if ($coupon->vendor_id) {
            $cartSubtotal = $cart->items
                ->filter(fn ($item) => $item->product?->vendor_id === $coupon->vendor_id)
                ->sum('subtotal');

            if ($cartSubtotal <= 0) {
                return redirect()->back()->with('error', 'This coupon is only valid for specific vendor products.');
            }
        }

        $validation = $coupon->canBeUsedByUser(auth()->id(), $cartSubtotal);

        if (! $validation['valid']) {
            return redirect()->back()->with('error', $validation['message']);
        }

        $discount = $coupon->calculateDiscount($cartSubtotal);

        $cart->update([
            'coupon_code' => $coupon->code,
            'discount' => $discount,
        ]);
        $cart->calculateTotals();

        return redirect()->back()->with('success', 'Coupon applied! You saved $'.number_format($discount, 2));
    }

    public function remove()
    {
        $cart = Cart::where('user_id', auth()->id())->first();

        if ($cart) {
            $cart->update([
                'coupon_code' => null,
                'discount' => 0,
            ]);
            $cart->calculateTotals();
        }

        return redirect()->back()->with('success', 'Coupon removed');
    }
}
