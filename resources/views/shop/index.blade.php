@extends('layouts.shop')

@section('title', __('ui.shop.title'))

@section('content')
{{-- Hero Section --}}
@php
    $categoryName = null;
    $categoryDesc = null;
    $categoryBannerImageUrl = null;
    if(request('category')) {
        $currentCategory = $categoryLookup->firstWhere('id', request('category'));
        $categoryName = $currentCategory->name ?? __('ui.shop.title');
        $categoryDesc = $currentCategory->description ?? __('ui.shop.category_fallback');
        if (! empty($currentCategory?->banner_image)) {
            $categoryBannerImageUrl = '/storage/' . ltrim($currentCategory->banner_image ?? '', '/');
        }
    }
    
    $fallbackStats = [
        ['value' => $products->total() . '+', 'label' => __('ui.shop.products_available')],
        ['value' => $categories->count() . '+', 'label' => __('ui.shop.categories')],
        ['value' => '100%', 'label' => __('ui.shop.authentic')]
    ];
@endphp

<x-hero-section
    pageIdentifier="shop"
    heightPreset="standard"
    heading-tag="h1"
    :fallbackTitle="$categoryName ?? __('ui.shop.hero_title')"
    :fallbackSubtitle="$categoryDesc ?? __('ui.shop.hero_subtitle')"
    :backgroundImageUrl="$categoryBannerImageUrl"
    fallbackImage="/images/hero-banners/shop-banner.avif"
    :fallbackStats="$fallbackStats"
    
/>

<div class="container mx-auto px-4 py-8">

    <div class="flex flex-col md:flex-row gap-8">
        <aside class="w-full md:w-64 flex-shrink-0">
            <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
                <h3 class="text-lg font-semibold text-white mb-4">{{ __('ui.shop.categories') }}</h3>
                <div class="space-y-2" x-data="{ open: @js(request('category')) }">
                    <a href="{{ route('shop.index') }}" class="block px-3 py-2 rounded {{ !request('category') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700' }}">
                        {{ __('ui.shop.all_products') }}
                    </a>
                    @foreach($categoryTree as $category)
                        <div>
                            <div class="flex items-center justify-between">
                                <a href="{{ route('shop.index', ['category' => $category->id]) }}" class="block px-3 py-2 rounded w-full {{ request('category') == $category->id ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700' }}">
                                    {{ $category->name }}
                                </a>
                                @if($category->children->count() > 0)
                                    <button type="button" class="ml-2 text-gray-400 hover:text-white" @click="open = open === {{ $category->id }} ? null : {{ $category->id }}" aria-label="Toggle {{ $category->name }} subcategories">
                                        <svg class="w-4 h-4 transform transition" :class="{ 'rotate-90': open === {{ $category->id }} }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </button>
                                @endif
                            </div>
                            @if($category->children->count() > 0)
                                <div x-show="open === {{ $category->id }}" class="mt-1 space-y-1" x-cloak>
                                    @foreach($category->children as $child)
                                        <a href="{{ route('shop.index', ['category' => $child->id]) }}" class="block px-3 py-2 rounded ml-3 text-sm {{ request('category') == $child->id ? 'bg-indigo-600 text-white' : 'text-gray-400 hover:bg-gray-700' }}">
                                            {{ $child->name }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-gray-800 rounded-lg p-6 border border-gray-700 mt-4">
                <h3 class="text-lg font-semibold text-white mb-4">{{ __('ui.shop.sort_by') }}</h3>
                <form method="GET" class="space-y-2">
                    <input type="hidden" name="category" value="{{ request('category') }}">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <input type="hidden" name="brand" value="{{ request('brand') }}">
                    <button type="submit" name="sort" value="latest" class="w-full text-left px-3 py-2 rounded {{ request('sort') == 'latest' || !request('sort') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700' }}">
                        {{ __('ui.shop.latest') }}
                    </button>
                    <button type="submit" name="sort" value="price_low" class="w-full text-left px-3 py-2 rounded {{ request('sort') == 'price_low' ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700' }}">
                        {{ __('ui.shop.price_low_high') }}
                    </button>
                    <button type="submit" name="sort" value="price_high" class="w-full text-left px-3 py-2 rounded {{ request('sort') == 'price_high' ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700' }}">
                        {{ __('ui.shop.price_high_low') }}
                    </button>
                    <button type="submit" name="sort" value="popular" class="w-full text-left px-3 py-2 rounded {{ request('sort') == 'popular' ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700' }}">
                        {{ __('ui.shop.most_popular') }}
                    </button>
                </form>
            </div>

            <div class="bg-gray-800 rounded-lg p-6 border border-gray-700 mt-4">
                <h3 class="text-lg font-semibold text-white mb-4">{{ __('ui.shop.brand') }}</h3>
                <form method="GET" class="space-y-2">
                    <input type="hidden" name="category" value="{{ request('category') }}">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <input type="hidden" name="sort" value="{{ request('sort') }}">
                    <label for="shop-brand-filter" class="sr-only">{{ __('ui.shop.brand') }}</label>
                    <select id="shop-brand-filter" name="brand" class="w-full bg-gray-700 border border-gray-600 text-white rounded px-3 py-2 text-sm" onchange="this.form.submit()">
                        <option value="">{{ __('ui.shop.all_brands') }}</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}" {{ request('brand') == $brand->id ? 'selected' : '' }}>
                                {{ $brand->name }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>

            <div class="bg-gray-800 rounded-lg p-6 border border-gray-700 mt-4">
                <h3 class="text-lg font-semibold text-white mb-4">{{ __('ui.shop.price_range') }}</h3>
                <form method="GET" class="space-y-3">
                    <input type="hidden" name="category" value="{{ request('category') }}">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <input type="hidden" name="sort" value="{{ request('sort') }}">
                    <input type="hidden" name="brand" value="{{ request('brand') }}">
                    
                    <div>
                        <label for="shop-min-price" class="block text-sm text-gray-300 mb-1">{{ __('ui.shop.min_price') }}</label>
                        <input id="shop-min-price" type="number" name="min_price" value="{{ request('min_price') }}" step="0.01" min="0"
                               class="w-full bg-gray-700 border border-gray-600 text-white rounded px-3 py-2 text-sm"
                               placeholder="$0.00">
                    </div>
                    
                    <div>
                        <label for="shop-max-price" class="block text-sm text-gray-300 mb-1">{{ __('ui.shop.max_price') }}</label>
                        <input id="shop-max-price" type="number" name="max_price" value="{{ request('max_price') }}" step="0.01" min="0"
                               class="w-full bg-gray-700 border border-gray-600 text-white rounded px-3 py-2 text-sm"
                               placeholder="$999.99">
                    </div>

                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded text-sm">
                        {{ __('ui.shop.apply_filter') }}
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
                                        <source srcset="{{ '/storage/' . ltrim($webpPath ?? '', '/') }}" type="image/webp">
                                    @endif
                                    <img src="{{ '/storage/' . ltrim($product->featured_image ?? '', '/') }}" alt="{{ $product->name }}" class="object-cover w-full h-48" loading="lazy">
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
                                    <span class="text-xl font-bold text-white">${{ number_format((float) $product->price, 2) }}</span>
                                    @if($product->compare_price)
                                        <span class="text-sm text-gray-500 line-through ml-2">${{ number_format((float) $product->compare_price, 2) }}</span>
                                    @endif
                                </div>
                                <a href="{{ route('shop.show', $product) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded text-sm">
                                    {{ __('ui.shop.view') }}
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center py-12 text-gray-400">
                        {{ __('ui.shop.no_products') }}
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


