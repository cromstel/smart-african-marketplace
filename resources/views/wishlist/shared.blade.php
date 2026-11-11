@extends('layouts.shop')

@section('title', 'Shared Wishlist')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-gradient-to-r from-orange-600 to-red-600 text-white py-12">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl md:text-4xl font-bold mb-2">Shared Wishlist</h2>
            <p class="text-orange-50 text-lg">
                {{ $share->user?->name ?? 'A shopper' }} shared their wishlist with you.
            </p>
        </div>
    </div>

    <div class="container mx-auto px-4 py-10">
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
                            <div class="flex items-center justify-between">
                                <span class="text-xl font-bold text-gray-900">${{ number_format((float) $item->product->price, 2) }}</span>
                                <a href="{{ route('shop.show', $item->product) }}" class="bg-gray-900 hover:bg-gray-800 text-white px-4 py-2 rounded text-sm">
                                    View
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                <h2 class="text-2xl font-bold text-gray-900 mb-2">No items in this wishlist</h2>
                <p class="text-gray-600 mb-6">Check back later or browse the shop.</p>
                <a href="{{ route('shop.index') }}" class="inline-block bg-gradient-to-r from-orange-600 to-red-600 hover:from-orange-700 hover:to-red-700 text-white px-6 py-3 rounded-lg font-semibold transition-all duration-200 shadow-md hover:shadow-lg">
                    Browse Products
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

