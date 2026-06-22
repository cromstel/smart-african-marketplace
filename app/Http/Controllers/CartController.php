<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    public function index()
    {
        $cart = $this->getOrCreateCart();
        $cart->calculateTotals();
        $cart->load(['items.product.category', 'items.variant']);

        return view('cart.index', compact('cart'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'product_variant_id' => 'nullable|exists:product_variations,id',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $variant = $this->resolveVariant($product, $validated['product_variant_id'] ?? null);
        $availableStock = $variant?->stock_quantity ?? $product->stock_quantity;

        if (! $product->is_active || ! $product->isInStock()) {
            throw ValidationException::withMessages([
                'product_id' => 'This product is currently unavailable.',
            ]);
        }

        if ($variant && ! $variant->isInStock()) {
            throw ValidationException::withMessages([
                'product_variant_id' => 'The selected variant is out of stock.',
            ]);
        }

        $cart = $this->getOrCreateCart();

        $existingItem = $cart->items()
            ->where('product_id', $product->id)
            ->where('product_variant_id', $validated['product_variant_id'] ?? null)
            ->first();

        $requestedQuantity = (int) $validated['quantity'] + (int) ($existingItem?->quantity ?? 0);

        if ($availableStock < $requestedQuantity) {
            throw ValidationException::withMessages([
                'quantity' => "Only {$availableStock} unit(s) are available for this item.",
            ]);
        }

        if ($existingItem) {
            $existingItem->quantity += $validated['quantity'];
            $existingItem->price = $variant?->price ?? $product->price;
            $existingItem->calculateSubtotal();
        } else {
            $unitPrice = (float) ($variant?->price ?? $product->price);
            $cartItem = $cart->items()->create([
                'product_id' => $product->id,
                'product_variant_id' => $validated['product_variant_id'] ?? null,
                'quantity' => $validated['quantity'],
                'price' => $unitPrice,
                'subtotal' => $unitPrice * (int) $validated['quantity'],
            ]);
        }

        $cart->calculateTotals();

        return redirect()->route('cart.index')->with('success', 'Product added to cart');
    }

    public function update(Request $request, CartItem $cartItem)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $this->ensureCartOwnership($cartItem);

        $availableStock = $cartItem->variant?->stock_quantity ?? $cartItem->product->stock_quantity;
        if ((int) $validated['quantity'] > (int) $availableStock) {
            throw ValidationException::withMessages([
                'quantity' => "Only {$availableStock} unit(s) are available for this item.",
            ]);
        }

        if (! $cartItem->product->is_active || ! $cartItem->product->isInStock()) {
            throw ValidationException::withMessages([
                'quantity' => 'This product is no longer available.',
            ]);
        }

        $cartItem->quantity = $validated['quantity'];
        $cartItem->price = $cartItem->variant?->price ?? $cartItem->product->price;
        $cartItem->calculateSubtotal();
        $cartItem->cart->calculateTotals();

        return redirect()->route('cart.index')->with('success', 'Cart updated');
    }

    public function destroy(CartItem $cartItem)
    {
        $this->ensureCartOwnership($cartItem);

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

    private function ensureCartOwnership(CartItem $cartItem): void
    {
        if ((int) $cartItem->cart->user_id !== (int) auth()->id()) {
            abort(403);
        }
    }

    private function resolveVariant(Product $product, ?int $variantId): ?ProductVariation
    {
        if (! $variantId) {
            return null;
        }

        $variant = ProductVariation::findOrFail($variantId);

        if ((int) $variant->product_id !== (int) $product->id || ! $variant->is_active) {
            throw ValidationException::withMessages([
                'product_variant_id' => 'The selected variant is invalid.',
            ]);
        }

        return $variant;
    }
}
