@extends('layouts.shop')

@section('title', $product->name . ' - ' . __('ui.brand.name'))

@push('head')
<meta name="description" content="{{ Str::limit(strip_tags($product->description), 160) }}">
<meta name="keywords" content="{{ $product->category->name }}, {{ $product->name }}, Smart African Marketplace, buy {{ $product->name }}">
<link rel="canonical" href="{{ route('shop.show', $product->slug) }}">

<meta property="og:title" content="{{ $product->name }}">
<meta property="og:description" content="{{ Str::limit(strip_tags($product->description), 200) }}">
<meta property="og:image" content="{{ $product->featured_image ? '/storage/' . ltrim($product->featured_image ?? '', '/') : '/images/default-product.jpg' }}">
<meta property="og:url" content="{{ route('shop.show', $product->slug) }}">
<meta property="og:type" content="product">
<meta property="og:site_name" content="Smart African Marketplace">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $product->name }}">
<meta name="twitter:description" content="{{ Str::limit(strip_tags($product->description), 200) }}">
<meta name="twitter:image" content="{{ $product->featured_image ? '/storage/' . ltrim($product->featured_image ?? '', '/') : '/images/default-product.jpg' }}">

<script type="application/ld+json">
{
  "@context": "https://schema.org/",
  "@type": "Product",
  "name": "{{ $product->name }}",
  "image": "{{ $product->featured_image ? '/storage/' . ltrim($product->featured_image ?? '', '/') : '' }}",
  "description": "{{ strip_tags($product->description) }}",
  "sku": "{{ $product->sku }}",
  "brand": {
    "@type": "Brand",
    "name": "{{ $product->vendor ? $product->vendor->business_name : 'Smart African Marketplace' }}"
  },
  "offers": {
    "@type": "Offer",
    "url": "{{ route('shop.show', $product->slug) }}",
    "priceCurrency": "USD",
    "price": "{{ $product->price }}",
    "availability": "{{ $product->stock_quantity > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock' }}",
    "seller": {
      "@type": "Organization",
      "name": "Smart African Marketplace"
    }
  }
  @if($product->reviews->count() > 0)
  ,"aggregateRating": {
    "@type": "AggregateRating",
    "ratingValue": "{{ $product->reviews->avg('rating') }}",
    "reviewCount": "{{ $product->reviews->count() }}"
  }
  @endif
}
</script>
@endpush

@section('content')
<x-hero-section
    pageIdentifier="shop"
    heightPreset="standard"
    :fallbackTitle="__('ui.shop.product_details')"
    :fallbackSubtitle="__('ui.shop.product_details_subtitle')"
    fallbackImage="/images/hero-banner.avif"
    headingTag="h2"
    :animated="true"
/>

<div class="container mx-auto px-4 py-8">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
        <div>
            <div class="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden mb-4">
                @if($product->images->count() > 0)
                    @php
                        $mainImage = $product->images->firstWhere('is_primary', true) ?? $product->images->first();
                        $mainWebpPath = str_replace(pathinfo($mainImage->image_path, PATHINFO_EXTENSION), 'webp', $mainImage->image_path);
                    @endphp
                    <picture>
                        @if(Storage::disk('public')->exists($mainWebpPath))
                            <source srcset="{{ '/storage/' . ltrim($mainWebpPath ?? '', '/') }}" type="image/webp">
                        @endif
                        <img id="mainImage" src="{{ '/storage/' . ltrim($mainImage->image_path ?? '', '/') }}" 
                             alt="{{ $product->name }}" class="w-full h-96 object-cover">
                    </picture>
                @else
                    <div class="flex items-center justify-center h-96 bg-gray-700">
                        <svg class="w-32 h-32 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                @endif
            </div>

            @if($product->images->count() > 1)
                <div class="grid grid-cols-4 gap-2">
                    @foreach($product->images as $image)
                        <button type="button" onclick="changeImage('{{ '/storage/' . ltrim($image->image_path ?? '', '/') }}')"
                                class="bg-gray-800 rounded border-2 {{ $image->is_primary ? 'border-indigo-500' : 'border-gray-700' }} hover:border-indigo-500 overflow-hidden transition">
                            <picture>
                                @php
                                    $thumbWebpPath = str_replace(pathinfo($image->image_path, PATHINFO_EXTENSION), 'webp', $image->image_path);
                                @endphp
                                @if(Storage::disk('public')->exists($thumbWebpPath))
                                    <source srcset="{{ '/storage/' . ltrim($thumbWebpPath ?? '', '/') }}" type="image/webp">
                                @endif
                                <img src="{{ '/storage/' . ltrim($image->image_path ?? '', '/') }}" alt="{{ $image->alt_text }}" class="w-full h-20 object-cover" loading="lazy">
                            </picture>
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <div class="mb-4">
                <span class="text-sm text-indigo-400">{{ $product->category->name }}</span>
                <h2 class="text-3xl font-bold text-white mt-2">{{ $product->name }}</h2>
            </div>

            <div class="flex items-baseline gap-3 mb-6">
                <span class="text-3xl font-bold text-white">${{ number_format((float) $product->price, 2) }}</span>
                @if($product->compare_price)
                    <span class="text-xl text-gray-500 line-through">${{ number_format((float) $product->compare_price, 2) }}</span>
                    <span class="text-sm bg-green-900 text-green-300 px-2 py-1 rounded">
                        {{ __('ui.shop.save', ['percent' => round((($product->compare_price - $product->price) / $product->compare_price) * 100)]) }}
                    </span>
                @endif
            </div>

            <p class="text-gray-300 mb-6">{{ $product->short_description }}</p>

            <div class="space-y-4 mb-6">
                @if($product->origin_country)
                    <div class="flex items-center text-gray-300">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>{{ __('ui.shop.origin') }} <span class="font-medium">{{ $product->origin_country }}</span></span>
                    </div>
                @endif

                @if($product->brand)
                    <div class="flex items-center text-gray-300">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12V8H4v4M20 12H4m16 0v4H4v-4m0 0V8m0 8v4h16v-4"/>
                        </svg>
                        <span>
                            Brand
                            <a href="{{ route('brands.show', $product->brand) }}" class="font-medium text-indigo-400 hover:text-indigo-300">
                                {{ $product->brand->name }}
                            </a>
                        </span>
                    </div>
                @endif
 
                <div class="flex items-center">
                    @if($product->isInStock())
                        <span class="px-3 py-1 bg-green-900 text-green-300 rounded text-sm">
                            {{ __('ui.shop.in_stock', ['count' => $product->stock_quantity]) }}
                        </span>
                    @else
                        <span class="px-3 py-1 bg-red-900 text-red-300 rounded text-sm">
                            {{ __('ui.shop.out_of_stock') }}
                        </span>
                    @endif
                </div>
            </div>

            @auth
                @php
                    $inWishlist = \App\Models\Wishlist::where('user_id', auth()->id())
                        ->where('product_id', $product->id)
                        ->exists();
                @endphp

                @if($product->isInStock())
                    <form action="{{ route('cart.store') }}" method="POST" class="space-y-4" id="addToCartForm">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <input type="hidden" name="product_variant_id" id="selectedVariantId" value="">

                        @if($product->variants->where('is_active', true)->count() > 0)
                            <div class="bg-gray-900 rounded-lg p-4 mb-4">
                                <h3 class="text-sm font-medium text-gray-300 mb-3">{{ __('ui.shop.select_options') }}</h3>
                                
                                @php
                                    $variantAttributes = [];
                                    foreach($product->variants->where('is_active', true) as $variant) {
                                        if ($variant->attributes && is_array($variant->attributes)) {
                                            foreach($variant->attributes as $key => $value) {
                                                if (!isset($variantAttributes[$key])) {
                                                    $variantAttributes[$key] = [];
                                                }
                                                if (!in_array($value, $variantAttributes[$key])) {
                                                    $variantAttributes[$key][] = $value;
                                                }
                                            }
                                        }
                                    }
                                @endphp

                                @foreach($variantAttributes as $attributeName => $attributeValues)
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-gray-400 mb-2">{{ ucfirst($attributeName) }}</label>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($attributeValues as $value)
                                                <button type="button" 
                                                        class="variant-option px-4 py-2 rounded-lg border-2 border-gray-600 text-white hover:border-indigo-500 transition-colors" 
                                                        data-attribute="{{ $attributeName }}" 
                                                        data-value="{{ $value }}"
                                                        onclick="selectVariantOption(this)">
                                                    {{ $value }}
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach

                                <div id="variantInfo" class="hidden mt-4 p-3 bg-gray-800 rounded border border-gray-700">
                                    <div class="flex justify-between items-center">
                                        <div>
                                            <p class="text-sm text-gray-400">{{ __('ui.shop.selected_variant') }}</p>
                                            <p id="variantName" class="text-white font-medium"></p>
                                        </div>
                                        <div class="text-right">
                                            <p id="variantPrice" class="text-2xl font-bold text-white"></p>
                                            <p id="variantStock" class="text-xs text-gray-400"></p>
                                        </div>
                                    </div>
                                </div>

                                <p class="text-xs text-yellow-300 mt-2">{{ __('ui.shop.select_all_options_note') }}</p>
                            </div>
                        @endif

                        <div>
                            <label for="quantity" class="block text-sm font-medium text-gray-300 mb-2">{{ __('ui.shop.quantity') }}</label>
                            <input type="number" name="quantity" id="quantity" value="1" min="1" max="{{ $product->stock_quantity }}" 
                                   class="bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 w-32">
                        </div>

                        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-3 px-6 rounded-lg font-medium inline-flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                {{ __('ui.shop.add_to_cart') }}
                        </button>
                    </form>

                    <div class="mt-3">
                        @if($inWishlist)
                            <form action="{{ route('wishlist.destroy', \App\Models\Wishlist::where('user_id', auth()->id())->where('product_id', $product->id)->first()) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white py-3 px-6 rounded-lg inline-flex items-center justify-center">
                                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ __('ui.shop.remove_from_wishlist') }}
                                </button>
                            </form>
                        @else
                            <form action="{{ route('wishlist.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <button type="submit" class="w-full bg-gray-700 hover:bg-gray-600 text-white py-3 px-6 rounded-lg inline-flex items-center justify-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                    </svg>
                                    {{ __('ui.shop.add_to_wishlist') }}
                                </button>
                            </form>
                        @endif
                    </div>
                @else
                    @if($inWishlist)
                        <form action="{{ route('wishlist.destroy', \App\Models\Wishlist::where('user_id', auth()->id())->where('product_id', $product->id)->first()) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white py-3 px-6 rounded-lg font-medium">
                                {{ __('ui.shop.remove_from_wishlist') }}
                            </button>
                        </form>
                    @else
                        <form action="{{ route('wishlist.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <button type="submit" class="w-full bg-gray-700 hover:bg-gray-600 text-white py-3 px-6 rounded-lg font-medium">
                                {{ __('ui.shop.add_to_wishlist') }}
                            </button>
                        </form>
                    @endif
                @endif
            @else
                <a href="{{ route('login') }}" class="block w-full bg-indigo-600 hover:bg-indigo-700 text-white py-3 px-6 rounded-lg font-medium text-center">
                    {{ __('ui.shop.login_to_purchase') }}
                </a>
            @endauth
        </div>
    </div>

    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6 mb-8">
        <h2 class="text-2xl font-bold text-white mb-4">{{ __('ui.shop.product_description') }}</h2>
        <div class="text-gray-300 prose prose-invert max-w-none">
            {!! nl2br(e($product->description)) !!}
        </div>
    </div>

    @if($product->vendor)
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6 mb-8">
            <h2 class="text-2xl font-bold text-white mb-4">{{ __('ui.shop.vendor_information') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <div class="flex items-center mb-4">
                        @php
                            $vendorProfileImage = $product->vendor->profile_image ?: $product->vendor->logo;
                        @endphp
                        @if($vendorProfileImage)
                            <img src="{{ '/storage/' . ltrim($vendorProfileImage ?? '', '/') }}" alt="{{ $product->vendor->business_name }} profile image" class="w-16 h-16 rounded-full object-cover mr-4">
                        @else
                            <div class="w-16 h-16 rounded-full bg-gray-700 text-white flex items-center justify-center font-semibold mr-4">
                                {{ strtoupper(substr($product->vendor->business_name, 0, 1)) }}
                            </div>
                        @endif
                        <div>
                            <h3 class="text-xl font-semibold text-white">{{ $product->vendor->business_name }}</h3>
                            @if($product->vendor->city || $product->vendor->country)
                                <p class="text-sm text-gray-400">
                                    <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    {{ $product->vendor->city ? $product->vendor->city.', ' : '' }}{{ $product->vendor->country }}
                                </p>
                            @endif
                        </div>
                    </div>
                    @if($product->vendor->description)
                        <p class="text-gray-300 mb-4">{{ Str::limit($product->vendor->description, 200) }}</p>
                    @endif
                    <a href="{{ $product->vendor->storefrontUrl() }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition">
                        {{ __('ui.shop.visit_store') }}
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                </div>
                @if(($product->latitude && $product->longitude) || ($product->vendor->latitude && $product->vendor->longitude))
                    <div>
                        @php
                            $lat = $product->latitude ?? $product->vendor->latitude;
                            $lng = $product->longitude ?? $product->vendor->longitude;
                        @endphp
                        <div id="product-location-map" class="h-64 rounded-lg overflow-hidden border border-gray-600"></div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6 mb-8">
        <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-white">{{ __('ui.shop.customer_reviews') }}</h2>
        <div class="flex items-center gap-2">
            @if($product->reviews_count > 0)
                <div class="flex items-center">
                        @for($i = 1; $i <= 5; $i++)
                            <svg class="w-5 h-5 {{ $i <= $product->average_rating ? 'text-yellow-400' : 'text-gray-600' }}" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        @endfor
                        <span class="ml-2 text-white font-semibold">{{ number_format($product->average_rating, 1) }}</span>
                        <span class="ml-1 text-gray-400">({{ $product->reviews_count }} {{ trans_choice('ui.shop.review_label', $product->reviews_count) }})</span>
                    </div>
                @else
                    <span class="text-gray-400">{{ __('ui.shop.no_reviews') }}</span>
                @endif
            </div>
        </div>

        @auth
            @php
                $userReview = $product->reviews()->where('user_id', auth()->id())->first();
                $hasPurchased = \App\Models\Order::where('user_id', auth()->id())
                    ->whereHas('items', function($query) use ($product) {
                        $query->where('product_id', $product->id);
                    })
                    ->where('status', 'delivered')
                    ->exists();
            @endphp

            @if(!$userReview)
                <div class="bg-gray-900 rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold text-white mb-4">{{ __('ui.shop.write_review') }}</h3>
                    
                    @if($hasPurchased)
                        <p class="text-green-400 text-sm mb-4">{{ __('ui.shop.verified_purchase_note') }}</p>
                    @endif

                    <form action="{{ route('reviews.store', $product) }}" method="POST" class="space-y-4" enctype="multipart/form-data">
                        @csrf

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">{{ __('ui.shop.rating') }}</label>
                            <div class="flex gap-2" id="rating-input">
                                @for($i = 1; $i <= 5; $i++)
                                    <button type="button" onclick="setRating({{ $i }})" class="rating-star">
                                        <svg class="w-8 h-8 text-gray-600 hover:text-yellow-400 transition" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    </button>
                                @endfor
                            </div>
                            <input type="hidden" name="rating" id="rating-value" required>
                            @error('rating') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">{{ __('ui.shop.review_title_optional') }}</label>
                            <input type="text" name="title" value="{{ old('title') }}" maxlength="255"
                                   class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                                   placeholder="{{ __('ui.shop.review_title_placeholder') }}">
                            @error('title') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">{{ __('ui.shop.review_body_label') }}</label>
                            <textarea name="comment" rows="4" required maxlength="1000"
                                      class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                                      placeholder="{{ __('ui.shop.review_body_placeholder') }}">{{ old('comment') }}</textarea>
                            @error('comment') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">{{ __('ui.shop.add_photos_optional') }}</label>
                            <input type="file" name="images[]" accept="image/png,image/jpeg,image/webp" multiple
                                   class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                            <p class="text-xs text-gray-400 mt-2">{{ __('ui.shop.photos_hint') }}</p>
                            @error('images.*') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>

                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg font-medium">
                            {{ __('ui.shop.submit_review') }}
                        </button>
                    </form>
                </div>
            @else
                <div class="bg-gray-900 rounded-lg p-4 mb-6">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-white font-semibold">{{ __('ui.shop.your_review') }}</span>
                                @if($userReview->is_verified_purchase)
                                    <span class="px-2 py-1 bg-green-900 text-green-300 text-xs rounded">{{ __('ui.shop.verified_purchase_badge') }}</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-1 mb-2">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-4 h-4 {{ $i <= $userReview->rating ? 'text-yellow-400' : 'text-gray-600' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                @endfor
                            </div>
                            @if($userReview->title)
                                <p class="text-white font-medium mb-1">{{ $userReview->title }}</p>
                            @endif
                            <p class="text-gray-300">{{ $userReview->comment }}</p>
                        @if($userReview->images && $userReview->images->count() > 0)
                            <div class="flex flex-wrap gap-2 mt-3">
                                @foreach($userReview->images as $image)
                                    <img src="{{ '/storage/' . ltrim($image->path ?? '', '/') }}" alt="{{ __('ui.shop.review_image_alt') }}" class="w-16 h-16 object-cover rounded border border-gray-700">
                                @endforeach
                            </div>
                        @endif
                        <p class="text-gray-500 text-sm mt-2">{{ $userReview->created_at->diffForHumans() }}</p>
                    </div>
                        <form action="{{ route('reviews.destroy', $userReview) }}" method="POST" onsubmit="return confirm('{{ __('ui.shop.delete_review_confirm') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-red-300 text-sm">{{ __('ui.shop.delete_review') }}</button>
                        </form>
                    </div>
                </div>
            @endif
        @else
            <div class="bg-gray-900 rounded-lg p-6 mb-6 text-center">
                <p class="text-gray-300 mb-4">{{ __('ui.shop.login_to_review') }}</p>
                <a href="{{ route('login') }}" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg">
                    {{ __('ui.shop.log_in') }}
                </a>
            </div>
        @endauth

        <div class="space-y-4">
            @forelse($product->reviews()->approved()->notSpam()->with(['user', 'images'])->withCount('helpfulVotes')->latest()->get() as $review)
                @if(!auth()->check() || $review->user_id !== auth()->id())
                    <div class="bg-gray-900 rounded-lg p-4">
                        <div class="flex items-start justify-between mb-2">
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-white font-semibold">{{ $review->user->name }}</span>
                                    @if($review->is_verified_purchase)
                                        <span class="px-2 py-1 bg-green-900 text-green-300 text-xs rounded">{{ __('ui.shop.verified_purchase_badge') }}</span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-1">
                                    @for($i = 1; $i <= 5; $i++)
                                        <svg class="w-4 h-4 {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-600' }}" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    @endfor
                                </div>
                            </div>
                            <span class="text-gray-500 text-sm">{{ $review->created_at->diffForHumans() }}</span>
                        </div>
                        @if($review->title)
                            <p class="text-white font-medium mb-1">{{ $review->title }}</p>
                        @endif
                        <p class="text-gray-300">{{ $review->comment }}</p>
                        @if($review->images && $review->images->count() > 0)
                            <div class="flex flex-wrap gap-2 mt-3">
                                @foreach($review->images as $image)
                                    <img src="{{ '/storage/' . ltrim($image->path ?? '', '/') }}" alt="{{ __('ui.shop.review_image_alt') }}" class="w-16 h-16 object-cover rounded border border-gray-700">
                                @endforeach
                            </div>
                        @endif
                        <div class="flex flex-wrap items-center gap-3 text-sm text-gray-400 mt-3">
                            <span>{{ $review->helpful_votes_count }} {{ __('ui.shop.helpful') }}</span>
                            @auth
                                @if($review->user_id !== auth()->id())
                                    <form action="{{ route('reviews.helpful', $review) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-indigo-400 hover:text-indigo-300">
                                            {{ __('ui.shop.mark_helpful') }}
                                        </button>
                                    </form>
                                @endif
                            @endauth
                        </div>
                    </div>
                @endif
            @empty
                @guest
                    <p class="text-gray-400 text-center py-8">{{ __('ui.shop.no_reviews_guest') }}</p>
                @endguest
            @endforelse
        </div>
    </div>

    @if($relatedProducts->count() > 0)
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-white mb-6">{{ __('ui.shop.related_products') }}</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($relatedProducts as $relatedProduct)
                    <div class="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden hover:border-indigo-500 transition">
                        <div class="aspect-w-16 aspect-h-12 bg-gray-700">
                            @if($relatedProduct->featured_image)
                                <picture>
                                    @php
                                        $relWebpPath = str_replace(pathinfo($relatedProduct->featured_image, PATHINFO_EXTENSION), 'webp', $relatedProduct->featured_image);
                                    @endphp
                                    @if(Storage::disk('public')->exists($relWebpPath))
                                        <source srcset="{{ '/storage/' . ltrim($relWebpPath ?? '', '/') }}" type="image/webp">
                                    @endif
                                    <img src="{{ '/storage/' . ltrim($relatedProduct->featured_image ?? '', '/') }}" alt="{{ $relatedProduct->name }}" class="object-cover w-full h-48" loading="lazy">
                                </picture>
                            @else
                                <div class="flex items-center justify-center h-48 bg-gray-700">
                                    <svg class="w-12 h-12 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            @endif
                        </div>
                        <div class="p-4">
                            <h3 class="text-lg font-semibold text-white mb-1">{{ $relatedProduct->name }}</h3>
                            <div class="flex items-center justify-between mt-3">
                                <span class="text-xl font-bold text-white">${{ number_format((float) $relatedProduct->price, 2) }}</span>
                                <a href="{{ route('shop.show', $relatedProduct) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded text-sm">
                                    {{ __('ui.shop.view') }}
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

<script>
function changeImage(imageSrc) {
    document.getElementById('mainImage').src = imageSrc;
}

function setRating(rating) {
    document.getElementById('rating-value').value = rating;
    const stars = document.querySelectorAll('.rating-star svg');
    stars.forEach((star, index) => {
        if (index < rating) {
            star.classList.remove('text-gray-600');
            star.classList.add('text-yellow-400');
        } else {
            star.classList.remove('text-yellow-400');
            star.classList.add('text-gray-600');
        }
    });
}

@if($product->variants->where('is_active', true)->count() > 0)
const productVariants = @json($product->variants->where('is_active', true)->values());
const selectedOptions = {};
const totalAttributes = {{ count($variantAttributes ?? []) }};

function selectVariantOption(button) {
    const attribute = button.getAttribute('data-attribute');
    const value = button.getAttribute('data-value');
    
    document.querySelectorAll(`.variant-option[data-attribute="${attribute}"]`).forEach(btn => {
        btn.classList.remove('border-indigo-500', 'bg-indigo-900');
        btn.classList.add('border-gray-600');
    });
    
    button.classList.remove('border-gray-600');
    button.classList.add('border-indigo-500', 'bg-indigo-900');
    
    selectedOptions[attribute] = value;
    
    if (Object.keys(selectedOptions).length === totalAttributes) {
        findMatchingVariant();
    } else {
        document.getElementById('variantInfo').classList.add('hidden');
        document.getElementById('selectedVariantId').value = '';
    }
}

function findMatchingVariant() {
    const matchingVariant = productVariants.find(variant => {
        if (!variant.attributes) return false;
        
        for (let key in selectedOptions) {
            if (variant.attributes[key] !== selectedOptions[key]) {
                return false;
            }
        }
        return true;
    });
    
    if (matchingVariant) {
        document.getElementById('selectedVariantId').value = matchingVariant.id;
        document.getElementById('variantName').textContent = matchingVariant.name;
        document.getElementById('variantPrice').textContent = '$' + parseFloat(matchingVariant.price).toFixed(2);
        document.getElementById('variantStock').textContent = matchingVariant.stock_quantity + ' ' + @json(__('ui.shop.in_stock_suffix'));
        document.getElementById('variantInfo').classList.remove('hidden');
        
        const quantityInput = document.getElementById('quantity');
        quantityInput.max = matchingVariant.stock_quantity;
        if (parseInt(quantityInput.value) > matchingVariant.stock_quantity) {
            quantityInput.value = matchingVariant.stock_quantity;
        }
    } else {
        document.getElementById('variantInfo').classList.add('hidden');
        document.getElementById('selectedVariantId').value = '';
        alert(@json(__('ui.shop.no_variant_found')));
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const addToCartForm = document.getElementById('addToCartForm');
    if (addToCartForm) {
        addToCartForm.addEventListener('submit', function(e) {
            if (totalAttributes > 0 && !document.getElementById('selectedVariantId').value) {
                e.preventDefault();
                alert(@json(__('ui.shop.select_all_options_alert')));
                return false;
            }
        });
    }
});
@endif

@if(($product->latitude && $product->longitude) || ($product->vendor && $product->vendor->latitude && $product->vendor->longitude))
const popupTitle = @json($product->vendor->business_name ?? __('ui.shop.product_location'));
const popupLocation = @json($product->location_city ?? $product->vendor->city ?? __('ui.shop.location_fallback'));
const productMap = L.map('product-location-map').setView([{{ $lat }}, {{ $lng }}], 13);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
}).addTo(productMap);

L.marker([{{ $lat }}, {{ $lng }}]).addTo(productMap)
    .bindPopup('<strong>' + popupTitle + '</strong><br>' + popupLocation)
    .openPopup();
@endif
</script>
@endsection


