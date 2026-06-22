<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $cart = Cart::firstOrCreate(['user_id' => $request->user()->id]);
        $cart->load(['items.product.images', 'items.variant']);

        return response()->json([
            'success' => true,
            'data' => $cart,
            'message' => 'Cart retrieved successfully',
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'product_variant_id' => 'nullable|exists:product_variations,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'Validation failed',
            ], 422);
        }

        $product = Product::findOrFail($request->product_id);
        $variant = $this->resolveVariant($product, $request->integer('product_variant_id'));

        if (! $product->is_active || ! $product->isInStock()) {
            return response()->json([
                'success' => false,
                'message' => 'This product is currently unavailable',
            ], 400);
        }

        if ($variant && ! $variant->isInStock()) {
            return response()->json([
                'success' => false,
                'message' => 'The selected variant is out of stock',
            ], 400);
        }

        $availableStock = $variant?->stock_quantity ?? $product->stock_quantity;
        if ($availableStock < $request->quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient stock available',
            ], 400);
        }

        $cart = Cart::firstOrCreate(['user_id' => $request->user()->id]);

        $cartItem = $cart->items()
            ->where('product_id', $request->integer('product_id'))
            ->where('product_variant_id', $request->integer('product_variant_id'))
            ->first();

        $newQuantity = (int) $request->quantity + (int) ($cartItem?->quantity ?? 0);
        if ($newQuantity > $availableStock) {
            return response()->json([
                'success' => false,
                'message' => "Only {$availableStock} unit(s) are available",
            ], 400);
        }

        if ($cartItem) {
            $cartItem->quantity = $newQuantity;
            $cartItem->price = $variant?->price ?? $product->price;
            $cartItem->calculateSubtotal();
        } else {
            $cartItem = $cart->items()->create([
                'product_id' => $request->integer('product_id'),
                'product_variant_id' => $request->integer('product_variant_id'),
                'quantity' => (int) $request->quantity,
                'price' => $variant?->price ?? $product->price,
                'subtotal' => 0,
            ]);
            $cartItem->calculateSubtotal();
        }

        $cart->calculateTotals();

        $cart->load(['items.product.images', 'items.variant']);

        return response()->json([
            'success' => true,
            'data' => $cart,
            'message' => 'Item added to cart successfully',
        ], 201);
    }

    public function update(Request $request, CartItem $cartItem)
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'Validation failed',
            ], 422);
        }

        if ($cartItem->cart->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        if (! $cartItem->product->is_active || ! $cartItem->product->isInStock()) {
            return response()->json([
                'success' => false,
                'message' => 'This product is currently unavailable',
            ], 400);
        }

        $availableStock = $cartItem->variant?->stock_quantity ?? $cartItem->product->stock_quantity;
        if ($availableStock < $request->quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient stock available',
            ], 400);
        }

        $cartItem->quantity = (int) $request->quantity;
        $cartItem->price = $cartItem->variant?->price ?? $cartItem->product->price;
        $cartItem->calculateSubtotal();
        $cartItem->cart->calculateTotals();
        $cart = $cartItem->cart->load(['items.product.images', 'items.variant']);

        return response()->json([
            'success' => true,
            'data' => $cart,
            'message' => 'Cart updated successfully',
        ]);
    }

    public function destroy(Request $request, CartItem $cartItem)
    {
        if ($cartItem->cart->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $cart = $cartItem->cart;
        $cartItem->delete();
        $cart->calculateTotals();
        $cart->load(['items.product.images', 'items.variant']);

        return response()->json([
            'success' => true,
            'data' => $cart,
            'message' => 'Item removed from cart successfully',
        ]);
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
