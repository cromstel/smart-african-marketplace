@extends('layouts.shop')

@section('title', 'My Wishlist')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-3xl font-bold text-white mb-8">My Wishlist</h1>

    @if($wishlistItems->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($wishlistItems as $item)
                <div class="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden">
                    <div class="aspect-w-16 aspect-h-12 bg-gray-700">
                        @if($item->product->featured_image)
                            <picture>
                                @php
                                    $wishWebpPath = str_replace(pathinfo($item->product->featured_image, PATHINFO_EXTENSION), 'webp', $item->product->featured_image);
                                @endphp
                                @if(Storage::disk('public')->exists($wishWebpPath))
                                    <source srcset="{{ asset('storage/' . $wishWebpPath) }}" type="image/webp">
                                @endif
                                <img src="{{ asset('storage/' . $item->product->featured_image) }}" alt="{{ $item->product->name }}" class="object-cover w-full h-48" loading="lazy">
                            </picture>
                        @else
                            <div class="flex items-center justify-center h-48 bg-gray-700">
                                <svg class="w-16 h-16 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        @endif
                    </div>
                    
                    <div class="p-4">
                        <h3 class="text-lg font-semibold text-white mb-1">{{ $item->product->name }}</h3>
                        <p class="text-sm text-gray-400 mb-2">{{ $item->product->category->name }}</p>
                        
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <span class="text-xl font-bold text-white">${{ number_format($item->product->price, 2) }}</span>
                                @if($item->product->compare_price)
                                    <span class="text-sm text-gray-500 line-through ml-2">${{ number_format($item->product->compare_price, 2) }}</span>
                                @endif
                            </div>
                        </div>

                        @if($item->product->isInStock())
                            <span class="px-2 py-1 bg-green-900 text-green-300 text-xs rounded mb-3 inline-block">In Stock</span>
                        @else
                            <span class="px-2 py-1 bg-red-900 text-red-300 text-xs rounded mb-3 inline-block">Out of Stock</span>
                        @endif

                        <div class="flex gap-2">
                            <a href="{{ route('shop.show', $item->product) }}" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded text-sm text-center">
                                View
                            </a>
                            <form action="{{ route('wishlist.destroy', $item) }}" method="POST" class="flex-1">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded text-sm">
                                    Remove
                                </button>
                            </form>
                        </div>

                        @if($item->product->isInStock())
                            <form action="{{ route('cart.store') }}" method="POST" class="mt-2">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $item->product->id }}">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="w-full bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded text-sm">
                                    Add to Cart
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-12 text-center">
            <svg class="w-24 h-24 mx-auto mb-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
            </svg>
            <h2 class="text-2xl font-bold text-white mb-2">Your wishlist is empty</h2>
            <p class="text-gray-400 mb-6">Start adding products you love to your wishlist</p>
            <a href="{{ route('shop.index') }}" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-medium">
                Browse Products
            </a>
        </div>
    @endif
</div>
@endsection
