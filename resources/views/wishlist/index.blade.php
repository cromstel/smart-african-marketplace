@extends('layouts.shop')

@section('title', 'My Wishlist - Smart African Market')
@section('seoDescription', 'Your favorite products saved for later from Smart African Market.')
@section('seoKeywords', 'wishlist, favorite products, saved items, African Market')

@section('content')
<x-hero-section
    pageIdentifier="wishlist"
    fallbackTitle="My Wishlist"
    fallbackSubtitle="Your favorite products saved for later"
    fallbackImage="/images/hero-banners/wishlist-banner.avif"
    :fallbackStats="[
        ['value' => $wishlistItems->count(), 'label' => 'Saved Items']
    ]"
    
/>

<div class="container mx-auto px-4 py-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">Share Your Wishlist</h2>
            <p class="text-sm text-gray-600">Create a link to share your saved items.</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-2 sm:items-center">
            @if($shareLink)
                <div class="flex items-center gap-2">
                    <input type="text" readonly value="{{ $shareLink }}" class="w-full sm:w-80 bg-white border border-gray-300 rounded px-3 py-2 text-sm text-gray-700">
                    <button type="button" class="bg-gray-900 text-white px-3 py-2 rounded text-sm" onclick="copyWishlistLink('{{ $shareLink }}')">
                        Copy
                    </button>
                </div>
            @endif
            <form action="{{ route('wishlist.share') }}" method="POST">
                @csrf
                <button type="submit" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded text-sm">
                    {{ $shareLink ? 'Refresh Link' : 'Generate Link' }}
                </button>
            </form>
        </div>
    </div>

    @if($wishlistItems->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($wishlistItems as $item)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow duration-200">
                    <div class="aspect-w-16 aspect-h-12 bg-gray-100">
                        @if($item->product->featured_image)
                            <picture>
                                @php
                                    $wishWebpPath = str_replace(pathinfo($item->product->featured_image, PATHINFO_EXTENSION), 'webp', $item->product->featured_image);
                                @endphp
                                @if(Storage::disk('public')->exists($wishWebpPath))
                                    <source srcset="{{ '/storage/' . ltrim($wishWebpPath ?? '', '/') }}" type="image/webp">
                                @endif
                                <img src="{{ '/storage/' . ltrim($item->product->featured_image ?? '', '/') }}" alt="{{ $item->product->name }}" class="object-cover w-full h-48" loading="lazy">
                            </picture>
                        @else
                            <div class="flex items-center justify-center h-48 bg-gray-100">
                                <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        @endif
                    </div>
                    
                    <div class="p-4">
                        <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ $item->product->name }}</h3>
                        <p class="text-sm text-gray-600 mb-2">{{ $item->product->category->name }}</p>
                        
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <span class="text-xl font-bold text-gray-900">${{ number_format((float) $item->product->price, 2) }}</span>
                                @if($item->product->compare_price)
                                    <span class="text-sm text-gray-500 line-through ml-2">${{ number_format((float) $item->product->compare_price, 2) }}</span>
                                @endif
                            </div>
                        </div>

                        @if($item->product->isInStock())
                            <span class="px-2 py-1 bg-green-100 text-green-700 text-xs rounded mb-3 inline-block font-medium">In Stock</span>
                        @else
                            <span class="px-2 py-1 bg-red-100 text-red-700 text-xs rounded mb-3 inline-block font-medium">Out of Stock</span>
                        @endif

                        <div class="flex gap-2">
                            <a href="{{ route('shop.show', $item->product) }}" class="flex-1 bg-gradient-to-r from-orange-600 to-red-600 hover:from-orange-700 hover:to-red-700 text-white px-4 py-2 rounded text-sm text-center transition-all duration-200">
                                View
                            </a>
                            <form action="{{ route('wishlist.destroy', $item) }}" method="POST" class="flex-1">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded text-sm transition-colors duration-200">
                                    Remove
                                </button>
                            </form>
                        </div>

                        @if($item->product->isInStock())
                            <form action="{{ route('wishlist.move', $item) }}" method="POST" class="mt-2">
                                @csrf
                                <button type="submit" class="w-full bg-gray-900 hover:bg-gray-800 text-white px-4 py-2 rounded text-sm transition-colors duration-200">
                                    Move to Cart
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
            <svg class="w-24 h-24 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
            </svg>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Your wishlist is empty</h2>
            <p class="text-gray-600 mb-6">Start adding products you love to your wishlist</p>
            <a href="{{ route('shop.index') }}" class="inline-block bg-gradient-to-r from-orange-600 to-red-600 hover:from-orange-700 hover:to-red-700 text-white px-6 py-3 rounded-lg font-semibold transition-all duration-200 shadow-md hover:shadow-lg">
                Browse Products
            </a>
        </div>
    @endif
</div>

<script>
    function copyWishlistLink(link) {
        if (!navigator.clipboard) {
            return;
        }
        navigator.clipboard.writeText(link);
    }
</script>
@endsection

