@extends('layouts.shop')

@section('content')
<div class="min-h-screen">
    <x-hero-section 
        page-identifier="home"
        fallback-title="{!! __('ui.home.hero_title') !!}"
        fallback-subtitle="{{ __('ui.home.hero_subtitle') }}"
        heading-tag="h1"
        fallback-image="/images/hero-banners/hero-banner.avif"
        :fallback-stats="$heroStats ?? []"
    />


    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl md:text-4xl font-bold text-center mb-12 text-gray-900">Why Choose {{ config('app.name') }}?</h2>
            <p class="text-center text-gray-600 max-w-3xl mx-auto -mt-6 mb-10">
                Discover quality products and services curated for the African community, backed by trusted sellers and a seamless shopping experience.
            </p>
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="bg-gray-50 rounded-xl p-8 text-center hover:shadow-lg transition">
                    <div class="text-6xl mb-4">🍲</div>
                    <h3 class="text-xl font-bold mb-3 text-gray-900">{{ __('ui.home.feature_food_title') }}</h3>
                    <p class="text-gray-600">
                        {{ __('ui.home.feature_food_body') }}
                    </p>
                </div>
                
                <div class="bg-gray-50 rounded-xl p-8 text-center hover:shadow-lg transition">
                    <div class="text-6xl mb-4">👗</div>
                    <h3 class="text-xl font-bold mb-3 text-gray-900">{{ __('ui.home.feature_clothing_title') }}</h3>
                    <p class="text-gray-600">
                        {{ __('ui.home.feature_clothing_body') }}
                    </p>
                </div>
                
                <div class="bg-gray-50 rounded-xl p-8 text-center hover:shadow-lg transition">
                    <div class="text-6xl mb-4">💇</div>
                    <h3 class="text-xl font-bold mb-3 text-gray-900">{{ __('ui.home.feature_salon_title') }}</h3>
                    <p class="text-gray-600">
                        {{ __('ui.home.feature_salon_body') }}
                    </p>
                </div>

                <div class="bg-gray-50 rounded-xl p-8 text-center hover:shadow-lg transition">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-green-100 flex items-center justify-center">
                        <svg class="w-8 h-8 text-green-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3l7 4v5c0 5-3.5 8.5-7 9-3.5-.5-7-4-7-9V7l7-4z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3 text-gray-900">Trusted Vendors & Secure Checkout</h3>
                    <p class="text-gray-600">
                        Shop confidently with vetted vendors, transparent pricing, and secure payment options built for reliability.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900">{{ __('ui.home.featured_products') }}</h2>
                <a href="{{ route('shop.index') }}" class="text-orange-600 hover:text-orange-700 font-semibold flex items-center transition">
                    {{ __('ui.home.view_all') }}
                    <svg class="w-5 h-5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @php
                    $featuredProducts = \App\Models\Product::with('category')
                        ->where('is_featured', true)
                        ->where('is_active', true)
                        ->take(12)
                        ->get();
                @endphp

                @forelse($featuredProducts as $product)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow duration-200">
                        <a href="{{ route('shop.show', $product) }}" class="block">
                            <div class="aspect-w-1 aspect-h-1 bg-gray-100">
                                @if($product->featured_image)
                                    <img src="{{ '/storage/' . ltrim($product->featured_image ?? '', '/') }}" 
                                         alt="{{ $product->name }}" 
                                         class="object-cover w-full h-48">
                                @else
                                    <div class="flex items-center justify-center h-48 bg-gradient-to-br from-orange-100 to-red-100">
                                        <svg class="w-20 h-20 text-orange-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                        </a>

                        <div class="p-4">
                            <a href="{{ route('shop.show', $product) }}">
                                <h3 class="text-sm font-semibold text-gray-900 mb-1 hover:text-orange-600 transition line-clamp-2">
                                    {{ $product->name }}
                                </h3>
                            </a>
                            <p class="text-xs text-gray-600 mb-2">{{ $product->category->name }}</p>
                            
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <span class="text-lg font-bold text-gray-900">${{ number_format((float) $product->price, 2) }}</span>
                                    @if($product->compare_price)
                                        <span class="text-xs text-gray-500 line-through ml-1">${{ number_format((float) $product->compare_price, 2) }}</span>
                                    @endif
                                </div>
                            </div>

                            <button class="w-full bg-gradient-to-r from-orange-600 to-red-600 hover:from-orange-700 hover:to-red-700 text-white px-4 py-2 rounded text-sm font-medium transition-all duration-200">
                                {{ __('ui.home.add_to_cart') }}
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center py-12">
                        <p class="text-gray-500">{{ __('ui.home.no_featured_products') }}</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl md:text-4xl font-bold text-center mb-12 text-gray-900">{{ __('ui.home.shop_by_category') }}</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                @forelse($shopCategories as $category)
                    <a href="{{ route('shop.index', ['category' => $category->id]) }}" class="bg-white rounded-lg p-6 text-center hover:shadow-lg hover:scale-105 transition cursor-pointer">
                        <div class="h-12 w-12 mx-auto mb-3 rounded-full bg-orange-100"></div>
                        <h4 class="font-semibold text-gray-900">{{ $category->name }}</h4>
                    </a>
                @empty
                    <div class="col-span-full text-center text-gray-500">
                        {{ __('ui.home.categories_soon') }}
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="py-16 bg-orange-50">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl md:text-4xl font-bold mb-4 text-gray-900">{{ __('ui.home.cta_title') }}</h2>
            <p class="text-xl text-gray-700 mb-8 max-w-2xl mx-auto">
                {{ __('ui.home.cta_body') }}
            </p>
            <a href="{{ route('register') }}" class="inline-block bg-gradient-to-r from-orange-600 to-red-600 text-white px-10 py-4 rounded-lg font-semibold hover:from-orange-700 hover:to-red-700 transition text-lg">
                {{ __('ui.home.cta_button') }}
            </a>
        </div>
    </section>
</div>
@endsection





