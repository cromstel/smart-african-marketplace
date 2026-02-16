@extends('layouts.shop')

@section('title', 'Shop')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-white">Shop African Products</h1>
        <p class="text-gray-400 mt-2">Discover authentic African food, clothing, and services</p>

        <form method="GET" class="mt-6">
            <div class="flex gap-2">
                <input type="hidden" name="category" value="{{ request('category') }}">
                <input type="hidden" name="sort" value="{{ request('sort') }}">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search products..."
                       class="flex-1 bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-2 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg font-medium">
                    Search
                </button>
                @if(request('search'))
                    <a href="{{ route('shop.index', ['category' => request('category'), 'sort' => request('sort')]) }}" 
                       class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                        Clear
                    </a>
                @endif
            </div>
        </form>
    </div>

    <div class="flex flex-col md:flex-row gap-8">
        <aside class="w-full md:w-64 flex-shrink-0">
            <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
                <h3 class="text-lg font-semibold text-white mb-4">Categories</h3>
                <div class="space-y-2">
                    <a href="{{ route('shop.index') }}" class="block px-3 py-2 rounded {{ !request('category') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700' }}">
                        All Products
                    </a>
                    @foreach($categories as $category)
                        <a href="{{ route('shop.index', ['category' => $category->id]) }}" class="block px-3 py-2 rounded {{ request('category') == $category->id ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700' }}">
                            {{ $category->name }}
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="bg-gray-800 rounded-lg p-6 border border-gray-700 mt-4">
                <h3 class="text-lg font-semibold text-white mb-4">Sort By</h3>
                <form method="GET" class="space-y-2">
                    <input type="hidden" name="category" value="{{ request('category') }}">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <button type="submit" name="sort" value="latest" class="w-full text-left px-3 py-2 rounded {{ request('sort') == 'latest' || !request('sort') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700' }}">
                        Latest
                    </button>
                    <button type="submit" name="sort" value="price_low" class="w-full text-left px-3 py-2 rounded {{ request('sort') == 'price_low' ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700' }}">
                        Price: Low to High
                    </button>
                    <button type="submit" name="sort" value="price_high" class="w-full text-left px-3 py-2 rounded {{ request('sort') == 'price_high' ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700' }}">
                        Price: High to Low
                    </button>
                    <button type="submit" name="sort" value="popular" class="w-full text-left px-3 py-2 rounded {{ request('sort') == 'popular' ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700' }}">
                        Most Popular
                    </button>
                </form>
            </div>

            <div class="bg-gray-800 rounded-lg p-6 border border-gray-700 mt-4">
                <h3 class="text-lg font-semibold text-white mb-4">Price Range</h3>
                <form method="GET" class="space-y-3">
                    <input type="hidden" name="category" value="{{ request('category') }}">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <input type="hidden" name="sort" value="{{ request('sort') }}">
                    
                    <div>
                        <label class="block text-sm text-gray-300 mb-1">Min Price</label>
                        <input type="number" name="min_price" value="{{ request('min_price') }}" step="0.01" min="0"
                               class="w-full bg-gray-700 border border-gray-600 text-white rounded px-3 py-2 text-sm"
                               placeholder="$0.00">
                    </div>
                    
                    <div>
                        <label class="block text-sm text-gray-300 mb-1">Max Price</label>
                        <input type="number" name="max_price" value="{{ request('max_price') }}" step="0.01" min="0"
                               class="w-full bg-gray-700 border border-gray-600 text-white rounded px-3 py-2 text-sm"
                               placeholder="$999.99">
                    </div>

                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded text-sm">
                        Apply Filter
                    </button>
                </form>
            </div>
        </aside>

        <div class="flex-1">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($products as $product)
                    <div class="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden hover:border-indigo-500 transition">
                        <div class="aspect-w-16 aspect-h-12 bg-gray-700">
                            @if($product->featured_image)
                                <picture>
                                    @php
                                        $webpPath = str_replace(pathinfo($product->featured_image, PATHINFO_EXTENSION), 'webp', $product->featured_image);
                                    @endphp
                                    @if(Storage::disk('public')->exists($webpPath))
                                        <source srcset="{{ asset('storage/' . $webpPath) }}" type="image/webp">
                                    @endif
                                    <img src="{{ asset('storage/' . $product->featured_image) }}" alt="{{ $product->name }}" class="object-cover w-full h-48" loading="lazy">
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
                            <h3 class="text-lg font-semibold text-white mb-1">{{ $product->name }}</h3>
                            <p class="text-sm text-gray-400 mb-2">{{ $product->category->name }}</p>
                            <p class="text-gray-300 text-sm mb-3 line-clamp-2">{{ $product->short_description }}</p>
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="text-xl font-bold text-white">${{ number_format($product->price, 2) }}</span>
                                    @if($product->compare_price)
                                        <span class="text-sm text-gray-500 line-through ml-2">${{ number_format($product->compare_price, 2) }}</span>
                                    @endif
                                </div>
                                <a href="{{ route('shop.show', $product) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded text-sm">
                                    View
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center py-12 text-gray-400">
                        No products found
                    </div>
                @endforelse
            </div>

            <div class="mt-8">
                {{ $products->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
