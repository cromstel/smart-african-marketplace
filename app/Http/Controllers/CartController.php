<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $cart = $this->getOrCreateCart();
        $cart->load(['items.product', 'items.variant']);

        return view('cart.index', compact('cart'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'product_variant_id' => 'nullable|exists:product_variants,id',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $cart = $this->getOrCreateCart();

        $existingItem = $cart->items()
            ->where('product_id', $product->id)
            ->where('product_variant_id', $validated['product_variant_id'] ?? null)
            ->first();

        if ($existingItem) {
            $existingItem->quantity += $validated['quantity'];
            $existingItem->calculateSubtotal();
        } else {
            $cartItem = $cart->items()->create([
                'product_id' => $product->id,
                'product_variant_id' => $validated['product_variant_id'] ?? null,
                'quantity' => $validated['quantity'],
                'price' => $product->price,
            ]);
            $cartItem->calculateSubtotal();
        }

        $cart->calculateTotals();

        return redirect()->route('cart.index')->with('success', 'Product added to cart');
    }

    public function update(Request $request, CartItem $cartItem)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cartItem->quantity = $validated['quantity'];
        $cartItem->calculateSubtotal();
        $cartItem->cart->calculateTotals();

        return redirect()->route('cart.index')->with('success', 'Cart updated');
    }

    public function destroy(CartItem $cartItem)
    {
        $cart = $cartItem->cart;
        $cartItem->delete();
        $cart->calculateTotals();

        return redirect()->route('cart.index')->with('success', 'Item removed from cart');
    }

    private function getOrCreateCart()
    {
        return Cart::firstOrCreate([
            'user_id' => auth()->id(),
        ]);
    }
}
