@extends('layouts.shop')

@section('title', $brand->name . ' Products')

@section('content')
@php
    $fallbackStats = [
        ['value' => $products->total() . '+', 'label' => 'Products'],
        ['value' => $categories->count() . '+', 'label' => 'Categories'],
    ];
@endphp

<x-hero-section
    pageIdentifier="brand"
    heightPreset="standard"
    headingTag="h1"
    :fallbackTitle="$brand->name . ' Products'"
    :fallbackSubtitle="$brand->description ?: ('Browse products from ' . $brand->name . '.')"
    :fallbackStats="$fallbackStats"
    
/>

<div class="container mx-auto px-4 py-8">
    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6 mb-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div class="flex items-center gap-4">
                @if($brand->logo)
                    <img src="{{ '/storage/' . ltrim($brand->logo ?? '', '/') }}" alt="{{ $brand->name }}" class="w-16 h-16 rounded-lg object-cover border border-gray-700">
                @endif
                <div>
                    <h1 class="text-3xl font-bold text-white">{{ $brand->name }}</h1>
                    @if($brand->description)
                        <p class="text-gray-300 mt-2">{{ $brand->description }}</p>
                    @endif
                    @if($brand->website)
                        <a href="{{ $brand->website }}" target="_blank" rel="nofollow noopener" class="text-indigo-400 hover:text-indigo-300 text-sm inline-block mt-2">
                            {{ $brand->website }}
                        </a>
                    @endif
                </div>
            </div>

            <form method="GET" action="{{ url()->current() }}" class="w-full md:w-auto flex flex-col sm:flex-row gap-3">
                <input type="hidden" name="category" value="{{ request('category') }}">
                <input type="hidden" name="sort" value="{{ request('sort') }}">
                <input type="hidden" name="min_price" value="{{ request('min_price') }}">
                <input type="hidden" name="max_price" value="{{ request('max_price') }}">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search within {{ $brand->name }}..."
                       class="w-full sm:w-72 bg-gray-900 border border-gray-700 text-white rounded-lg px-4 py-2">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg">
                    Search
                </button>
            </form>
        </div>
    </div>

    <div class="flex flex-col lg:flex-row gap-8">
        <aside class="w-full lg:w-72 flex-shrink-0 space-y-4">
            <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
                <h3 class="text-lg font-semibold text-white mb-4">Categories</h3>
                <div class="space-y-2">
                    <a href="{{ request()->fullUrlWithQuery(['category' => null, 'page' => null]) }}"
                       class="block px-3 py-2 rounded {{ !request('category') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700' }}">
                        All categories
                    </a>
                    @foreach($categoryTree as $category)
                        <a href="{{ request()->fullUrlWithQuery(['category' => $category->id, 'page' => null]) }}"
                           class="block px-3 py-2 rounded {{ (string) request('category') === (string) $category->id ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700' }}">
                            {{ $category->name }}
                        </a>
                        @if($category->children->count() > 0)
                            <div class="ml-3 space-y-1">
                                @foreach($category->children as $child)
                                    <a href="{{ request()->fullUrlWithQuery(['category' => $child->id, 'page' => null]) }}"
                                       class="block px-3 py-2 rounded text-sm {{ (string) request('category') === (string) $child->id ? 'bg-indigo-600 text-white' : 'text-gray-400 hover:bg-gray-700' }}">
                                        {{ $child->name }}
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

            <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
                <h3 class="text-lg font-semibold text-white mb-4">Filters</h3>
                <form method="GET" action="{{ url()->current() }}" class="space-y-3">
                    <input type="hidden" name="category" value="{{ request('category') }}">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <input type="hidden" name="min_price" value="{{ request('min_price') }}">
                    <input type="hidden" name="max_price" value="{{ request('max_price') }}">

                    <div>
                        <label class="block text-sm text-gray-300 mb-1">Sort</label>
                        <select name="sort" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2">
                            <option value="" {{ request('sort') ? '' : 'selected' }}>Latest</option>
                            <option value="price_low" {{ request('sort') === 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                            <option value="price_high" {{ request('sort') === 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                            <option value="popular" {{ request('sort') === 'popular' ? 'selected' : '' }}>Most Popular</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm text-gray-300 mb-1">Min</label>
                            <input type="number" name="min_price" value="{{ request('min_price') }}" step="0.01" min="0"
                                   class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2" placeholder="0.00">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-300 mb-1">Max</label>
                            <input type="number" name="max_price" value="{{ request('max_price') }}" step="0.01" min="0"
                                   class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2" placeholder="0.00">
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg">
                        Apply
                    </button>

                    <a href="{{ url()->current() }}" class="block text-center text-sm text-gray-400 hover:text-gray-200">
                        Reset
                    </a>
                </form>
            </div>
        </aside>

        <main class="flex-1">
            <div class="flex items-center justify-between mb-4">
                <p class="text-gray-300">
                    Showing <span class="font-medium text-white">{{ $products->count() }}</span> of <span class="font-medium text-white">{{ $products->total() }}</span> products
                </p>
            </div>

            @if($products->count() === 0)
                <div class="bg-gray-800 rounded-lg border border-gray-700 p-8 text-center text-gray-300">
                    No products found for this brand.
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($products as $product)
                        <div class="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden hover:border-indigo-500 transition">
                            <a href="{{ route('shop.show', $product) }}" class="block">
                                @if($product->featured_image)
                                    <img src="{{ '/storage/' . ltrim($product->featured_image ?? '', '/') }}" alt="{{ $product->name }}" class="w-full h-48 object-cover">
                                @else
                                    <div class="w-full h-48 bg-gray-900 flex items-center justify-center text-gray-500">
                                        No image
                                    </div>
                                @endif
                            </a>
                            <div class="p-5">
                                <a href="{{ route('shop.show', $product) }}" class="text-lg font-semibold text-white hover:text-indigo-300">
                                    {{ $product->name }}
                                </a>
                                <div class="flex items-center justify-between mt-3">
                                    <span class="text-xl font-bold text-white">${{ number_format($product->price, 2) }}</span>
                                    <a href="{{ route('shop.show', $product) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded text-sm">
                                        View
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $products->links() }}
                </div>
            @endif
        </main>
    </div>
</div>
@endsection

