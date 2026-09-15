@extends('layouts.shop')

@section('title', 'Shopping Cart')

@section('content')
<x-hero-section
    pageIdentifier="cart"
    fallbackTitle="Shopping Cart"
    fallbackSubtitle="Review your items before checkout"
    fallbackImage="/images/hero-banners/shopping-cart-banner.avif"
    :animated="true"
/>

<div class="container mx-auto px-4 py-8">

    @if($cart->items->count() > 0)
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2">
                <div class="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden">
                    @foreach($cart->items as $item)
                        <div class="p-6 border-b border-gray-700 last:border-b-0">
                            <div class="flex items-center gap-4">
                                <div class="w-24 h-24 bg-gray-700 rounded flex-shrink-0">
                                    @if($item->product->featured_image)
                                        <picture>
                                            @php
                                                $cartWebpPath = str_replace(pathinfo($item->product->featured_image, PATHINFO_EXTENSION), 'webp', $item->product->featured_image);
                                            @endphp
                                            @if(Storage::disk('public')->exists($cartWebpPath))
                                                <source srcset="{{ '/storage/' . ltrim($cartWebpPath ?? '', '/') }}" type="image/webp">
                                            @endif
                                            <img src="{{ '/storage/' . ltrim($item->product->featured_image ?? '', '/') }}" alt="{{ $item->product->name }}" class="w-full h-full object-cover rounded" loading="lazy">
                                        </picture>
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-gray-500 text-xs">
                                            No image
                                        </div>
                                    @endif
                                </div>

                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-white">{{ $item->product->name }}</h3>
                                    <p class="text-sm text-gray-400">{{ $item->product->category->name ?? 'Uncategorized' }}</p>
                                    @if($item->variant)
                                        <div class="mt-1">
                                            <span class="text-xs bg-indigo-900 text-indigo-300 px-2 py-1 rounded">{{ $item->variant->name }}</span>
                                        </div>
                                    @endif
                                    <p class="text-sm text-gray-400 mt-1">${{ number_format($item->price, 2) }} each</p>
                                </div>

                                <div class="flex items-center gap-4">
                                    <form action="{{ route('cart.update', $item) }}" method="POST" class="flex items-center gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" max="{{ $item->product->stock_quantity }}"
                                               class="bg-gray-700 border border-gray-600 text-white rounded px-3 py-1 w-20">
                                        <button type="submit" class="text-indigo-400 hover:text-indigo-300 text-sm">Update</button>
                                    </form>

                                    <div class="text-right min-w-[100px]">
                                        <p class="text-lg font-bold text-white">${{ number_format($item->subtotal, 2) }}</p>
                                    </div>

                                    <form action="{{ route('cart.destroy', $item) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-400 hover:text-red-300" onclick="return confirm('Remove this item?')">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="lg:col-span-1">
                <div class="bg-gray-800 rounded-lg border border-gray-700 p-6 sticky top-4">
                    <h2 class="text-xl font-bold text-white mb-4">Order Summary</h2>

                    <div class="space-y-3 mb-4">
                        <div class="flex justify-between text-gray-300">
                            <span>Subtotal</span>
                            <span>${{ number_format($cart->subtotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-gray-300">
                            <span>Tax</span>
                            <span>${{ number_format($cart->tax ?? 0, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-gray-300">
                            <span>Shipping</span>
                            <span>${{ number_format($cart->shipping ?? 0, 2) }}</span>
                        </div>
                        @if($cart->discount > 0)
                            <div class="flex justify-between text-green-400">
                                <span>Discount</span>
                                <span>-${{ number_format($cart->discount, 2) }}</span>
                            </div>
                        @endif
                    </div>

                    <div class="border-t border-gray-700 pt-4 mb-4">
                        @if($cart->coupon_code)
                            <div class="mb-3 rounded-lg border border-green-600/40 bg-green-900/20 p-3">
                                <p class="text-sm text-green-300">Coupon applied: <span class="font-semibold">{{ $cart->coupon_code }}</span></p>
                            </div>
                            <form action="{{ route('coupon.remove') }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full rounded-lg border border-gray-600 px-4 py-2 text-sm font-medium text-gray-200 hover:border-gray-500 hover:text-white">
                                    Remove Coupon
                                </button>
                            </form>
                        @else
                            <form action="{{ route('coupon.apply') }}" method="POST" class="space-y-2">
                                @csrf
                                <label for="coupon-code" class="block text-sm font-medium text-gray-300">Coupon Code</label>
                                <input id="coupon-code" type="text" name="code" placeholder="Enter coupon code"
                                       class="w-full rounded-lg border border-gray-600 bg-gray-700 px-3 py-2 text-white placeholder-gray-400 focus:border-indigo-500 focus:outline-none">
                                <button type="submit" class="w-full rounded-lg border border-indigo-500 px-4 py-2 text-sm font-medium text-indigo-300 hover:bg-indigo-500/10">
                                    Apply Coupon
                                </button>
                            </form>
                        @endif
                    </div>

                    <div class="border-t border-gray-700 pt-3 mb-6">
                        <div class="flex justify-between text-white text-xl font-bold">
                            <span>Total</span>
                            <span>${{ number_format($cart->total, 2) }}</span>
                        </div>
                    </div>

                    <a href="{{ route('checkout.index') }}" class="block w-full bg-indigo-600 hover:bg-indigo-700 text-white py-3 px-6 rounded-lg font-medium text-center">
                        Proceed to Checkout
                    </a>

                    <a href="{{ route('shop.index') }}" class="block w-full mt-3 text-center text-indigo-400 hover:text-indigo-300">
                        Continue Shopping
                    </a>
                </div>
            </div>
        </div>
    @else
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-12 text-center">
            <svg class="w-16 h-16 mx-auto text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <h2 class="text-2xl font-bold text-white mb-2">Your cart is empty</h2>
            <p class="text-gray-400 mb-6">Add some products to get started</p>
            <a href="{{ route('shop.index') }}" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-medium">
                Start Shopping
            </a>
        </div>
    @endif
</div>
@endsection

