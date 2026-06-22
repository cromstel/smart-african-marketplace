<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use App\Models\Wishlist;
use App\Models\WishlistShare;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WishlistController extends Controller
{
    public function index()
    {
        $wishlistItems = Wishlist::where('user_id', auth()->id())
            ->with('product.category')
            ->latest()
            ->get();

        $share = WishlistShare::where('user_id', auth()->id())
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->latest()
            ->first();

        $shareLink = $share ? route('wishlist.shared', $share->token) : null;

        return view('wishlist.index', compact('wishlistItems', 'shareLink'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $exists = Wishlist::where('user_id', auth()->id())
            ->where('product_id', $validated['product_id'])
            ->exists();

        if ($exists) {
            return redirect()->back()->with('info', 'Product already in your wishlist');
        }

        Wishlist::create([
            'user_id' => auth()->id(),
            'product_id' => $validated['product_id'],
        ]);

        return redirect()->back()->with('success', 'Product added to wishlist');
    }

    public function destroy(Wishlist $wishlist)
    {
        if ($wishlist->user_id !== auth()->id()) {
            abort(403);
        }

        $wishlist->delete();

        return redirect()->back()->with('success', 'Product removed from wishlist');
    }

    public function share()
    {
        $share = WishlistShare::where('user_id', auth()->id())->first();

        if (! $share) {
            $share = WishlistShare::create([
                'user_id' => auth()->id(),
                'token' => Str::random(48),
                'is_active' => true,
                'expires_at' => now()->addDays(30),
            ]);
        } else {
            $share->update([
                'token' => Str::random(48),
                'is_active' => true,
                'expires_at' => now()->addDays(30),
            ]);
        }

        return redirect()->route('wishlist.index')->with('success', 'Share link created.');
    }

    public function shared(string $token)
    {
        $share = WishlistShare::where('token', $token)
            ->where('is_active', true)
            ->with('user')
            ->firstOrFail();

        if ($share->expires_at && $share->expires_at->isPast()) {
            abort(404);
        }

        $wishlistItems = Wishlist::where('user_id', $share->user_id)
            ->with('product.category')
            ->latest()
            ->get();

        return view('wishlist.shared', compact('wishlistItems', 'share'));
    }

    public function moveToCart(Wishlist $wishlist)
    {
        if ($wishlist->user_id !== auth()->id()) {
            abort(403);
        }

        $product = Product::find($wishlist->product_id);

        if (! $product || ! $product->is_active || ! $product->isInStock()) {
            return redirect()->back()->with('error', 'This product is not available.');
        }

        $cart = Cart::firstOrCreate(['user_id' => auth()->id()]);

        $existingItem = $cart->items()
            ->where('product_id', $product->id)
            ->whereNull('product_variant_id')
            ->first();

        $availableStock = $product->stock_quantity;
        $requestedQuantity = 1 + (int) ($existingItem?->quantity ?? 0);

        if ($availableStock < $requestedQuantity) {
            return redirect()->back()->with('error', "Only {$availableStock} unit(s) are available for this item.");
        }

        if ($existingItem) {
            $existingItem->quantity += 1;
            $existingItem->price = $product->price;
            $existingItem->calculateSubtotal();
        } else {
            $cart->items()->create([
                'product_id' => $product->id,
                'quantity' => 1,
                'price' => $product->price,
                'subtotal' => $product->price,
            ]);
        }

        $cart->calculateTotals();
        $wishlist->delete();

        return redirect()->route('cart.index')->with('success', 'Item moved to cart.');
    }
}
