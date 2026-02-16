@extends('layouts.shop')

@section('title', $product->name . ' - African Market')

@push('head')
<meta name="description" content="{{ Str::limit(strip_tags($product->description), 160) }}">
<meta name="keywords" content="{{ $product->category->name }}, {{ $product->name }}, African Market, buy {{ $product->name }}">
<link rel="canonical" href="{{ route('shop.show', $product->slug) }}">

<meta property="og:title" content="{{ $product->name }}">
<meta property="og:description" content="{{ Str::limit(strip_tags($product->description), 200) }}">
<meta property="og:image" content="{{ $product->featured_image ? asset('storage/' . $product->featured_image) : asset('images/default-product.jpg') }}">
<meta property="og:url" content="{{ route('shop.show', $product->slug) }}">
<meta property="og:type" content="product">
<meta property="og:site_name" content="African Market">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $product->name }}">
<meta name="twitter:description" content="{{ Str::limit(strip_tags($product->description), 200) }}">
<meta name="twitter:image" content="{{ $product->featured_image ? asset('storage/' . $product->featured_image) : asset('images/default-product.jpg') }}">

<script type="application/ld+json">
{
  "@context": "https://schema.org/",
  "@type": "Product",
  "name": "{{ $product->name }}",
  "image": "{{ $product->featured_image ? asset('storage/' . $product->featured_image) : '' }}",
  "description": "{{ strip_tags($product->description) }}",
  "sku": "{{ $product->sku }}",
  "brand": {
    "@type": "Brand",
    "name": "{{ $product->vendor ? $product->vendor->business_name : 'African Market' }}"
  },
  "offers": {
    "@type": "Offer",
    "url": "{{ route('shop.show', $product->slug) }}",
    "priceCurrency": "USD",
    "price": "{{ $product->price }}",
    "availability": "{{ $product->stock_quantity > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock' }}",
    "seller": {
      "@type": "Organization",
      "name": "African Market"
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
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
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
                            <source srcset="{{ asset('storage/' . $mainWebpPath) }}" type="image/webp">
                        @endif
                        <img id="mainImage" src="{{ asset('storage/' . $mainImage->image_path) }}" 
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
                        <button type="button" onclick="changeImage('{{ asset('storage/' . $image->image_path) }}')"
                                class="bg-gray-800 rounded border-2 {{ $image->is_primary ? 'border-indigo-500' : 'border-gray-700' }} hover:border-indigo-500 overflow-hidden transition">
                            <picture>
                                @php
                                    $thumbWebpPath = str_replace(pathinfo($image->image_path, PATHINFO_EXTENSION), 'webp', $image->image_path);
                                @endphp
                                @if(Storage::disk('public')->exists($thumbWebpPath))
                                    <source srcset="{{ asset('storage/' . $thumbWebpPath) }}" type="image/webp">
                                @endif
                                <img src="{{ asset('storage/' . $image->image_path) }}" alt="{{ $image->alt_text }}" class="w-full h-20 object-cover" loading="lazy">
                            </picture>
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <div class="mb-4">
                <span class="text-sm text-indigo-400">{{ $product->category->name }}</span>
                <h1 class="text-3xl font-bold text-white mt-2">{{ $product->name }}</h1>
            </div>

            <div class="flex items-baseline gap-3 mb-6">
                <span class="text-3xl font-bold text-white">${{ number_format($product->price, 2) }}</span>
                @if($product->compare_price)
                    <span class="text-xl text-gray-500 line-through">${{ number_format($product->compare_price, 2) }}</span>
                    <span class="text-sm bg-green-900 text-green-300 px-2 py-1 rounded">
                        Save {{ round((($product->compare_price - $product->price) / $product->compare_price) * 100) }}%
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
                        <span>Origin: <span class="font-medium">{{ $product->origin_country }}</span></span>
                    </div>
                @endif

                <div class="flex items-center">
                    @if($product->isInStock())
                        <span class="px-3 py-1 bg-green-900 text-green-300 rounded text-sm">
                            In Stock ({{ $product->stock_quantity }} available)
                        </span>
                    @else
                        <span class="px-3 py-1 bg-red-900 text-red-300 rounded text-sm">
                            Out of Stock
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
                                <h3 class="text-sm font-medium text-gray-300 mb-3">Select Options</h3>
                                
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
                                            <p class="text-sm text-gray-400">Selected Variant</p>
                                            <p id="variantName" class="text-white font-medium"></p>
                                        </div>
                                        <div class="text-right">
                                            <p id="variantPrice" class="text-2xl font-bold text-white"></p>
                                            <p id="variantStock" class="text-xs text-gray-400"></p>
                                        </div>
                                    </div>
                                </div>

                                <p class="text-xs text-yellow-300 mt-2">Please select all options to continue</p>
                            </div>
                        @endif

                        <div>
                            <label for="quantity" class="block text-sm font-medium text-gray-300 mb-2">Quantity</label>
                            <input type="number" name="quantity" id="quantity" value="1" min="1" max="{{ $product->stock_quantity }}" 
                                   class="bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 w-32">
                        </div>

                        <div class="flex gap-2">
                            <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-3 px-6 rounded-lg font-medium inline-flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                Add to Cart
                            </button>

                            @if($inWishlist)
                                <form action="{{ route('wishlist.destroy', \App\Models\Wishlist::where('user_id', auth()->id())->where('product_id', $product->id)->first()) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white py-3 px-6 rounded-lg">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                                        </svg>
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('wishlist.store') }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <button type="submit" class="bg-gray-700 hover:bg-gray-600 text-white py-3 px-6 rounded-lg">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                        </svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </form>
                @else
                    @if($inWishlist)
                        <form action="{{ route('wishlist.destroy', \App\Models\Wishlist::where('user_id', auth()->id())->where('product_id', $product->id)->first()) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white py-3 px-6 rounded-lg font-medium">
                                Remove from Wishlist
                            </button>
                        </form>
                    @else
                        <form action="{{ route('wishlist.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <button type="submit" class="w-full bg-gray-700 hover:bg-gray-600 text-white py-3 px-6 rounded-lg font-medium">
                                Add to Wishlist
                            </button>
                        </form>
                    @endif
                @endif
            @else
                <a href="{{ route('login') }}" class="block w-full bg-indigo-600 hover:bg-indigo-700 text-white py-3 px-6 rounded-lg font-medium text-center">
                    Login to Purchase
                </a>
            @endauth
        </div>
    </div>

    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6 mb-8">
        <h2 class="text-2xl font-bold text-white mb-4">Product Description</h2>
        <div class="text-gray-300 prose prose-invert max-w-none">
            {!! nl2br(e($product->description)) !!}
        </div>
    </div>

    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6 mb-8">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-white">Customer Reviews</h2>
            <div class="flex items-center gap-2">
                @if($product->reviews_count > 0)
                    <div class="flex items-center">
                        @for($i = 1; $i <= 5; $i++)
                            <svg class="w-5 h-5 {{ $i <= $product->average_rating ? 'text-yellow-400' : 'text-gray-600' }}" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        @endfor
                        <span class="ml-2 text-white font-semibold">{{ number_format($product->average_rating, 1) }}</span>
                        <span class="ml-1 text-gray-400">({{ $product->reviews_count }} {{ Str::plural('review', $product->reviews_count) }})</span>
                    </div>
                @else
                    <span class="text-gray-400">No reviews yet</span>
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
                    <h3 class="text-lg font-semibold text-white mb-4">Write a Review</h3>
                    
                    @if($hasPurchased)
                        <p class="text-green-400 text-sm mb-4">✓ Verified Purchase - You bought this product</p>
                    @endif

                    <form action="{{ route('reviews.store', $product) }}" method="POST" class="space-y-4">
                        @csrf

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Rating *</label>
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
                            <label class="block text-sm font-medium text-gray-300 mb-2">Review Title (Optional)</label>
                            <input type="text" name="title" value="{{ old('title') }}" maxlength="255"
                                   class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                                   placeholder="Sum up your experience">
                            @error('title') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Your Review *</label>
                            <textarea name="comment" rows="4" required maxlength="1000"
                                      class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                                      placeholder="Share your experience with this product">{{ old('comment') }}</textarea>
                            @error('comment') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>

                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg font-medium">
                            Submit Review
                        </button>
                    </form>
                </div>
            @else
                <div class="bg-gray-900 rounded-lg p-4 mb-6">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-white font-semibold">Your Review</span>
                                @if($userReview->is_verified_purchase)
                                    <span class="px-2 py-1 bg-green-900 text-green-300 text-xs rounded">Verified Purchase</span>
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
                            <p class="text-gray-500 text-sm mt-2">{{ $userReview->created_at->diffForHumans() }}</p>
                        </div>
                        <form action="{{ route('reviews.destroy', $userReview) }}" method="POST" onsubmit="return confirm('Delete your review?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-red-300 text-sm">Delete</button>
                        </form>
                    </div>
                </div>
            @endif
        @else
            <div class="bg-gray-900 rounded-lg p-6 mb-6 text-center">
                <p class="text-gray-300 mb-4">Please log in to write a review</p>
                <a href="{{ route('login') }}" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg">
                    Log In
                </a>
            </div>
        @endauth

        <div class="space-y-4">
            @forelse($product->reviews()->approved()->with('user')->latest()->get() as $review)
                @if(!auth()->check() || $review->user_id !== auth()->id())
                    <div class="bg-gray-900 rounded-lg p-4">
                        <div class="flex items-start justify-between mb-2">
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-white font-semibold">{{ $review->user->name }}</span>
                                    @if($review->is_verified_purchase)
                                        <span class="px-2 py-1 bg-green-900 text-green-300 text-xs rounded">Verified Purchase</span>
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
                    </div>
                @endif
            @empty
                @guest
                    <p class="text-gray-400 text-center py-8">No reviews yet. Be the first to review this product!</p>
                @endguest
            @endforelse
        </div>
    </div>

    @if($relatedProducts->count() > 0)
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-white mb-6">Related Products</h2>
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
                                        <source srcset="{{ asset('storage/' . $relWebpPath) }}" type="image/webp">
                                    @endif
                                    <img src="{{ asset('storage/' . $relatedProduct->featured_image) }}" alt="{{ $relatedProduct->name }}" class="object-cover w-full h-48" loading="lazy">
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
                                <span class="text-xl font-bold text-white">${{ number_format($relatedProduct->price, 2) }}</span>
                                <a href="{{ route('shop.show', $relatedProduct) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded text-sm">
                                    View
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
        document.getElementById('variantStock').textContent = matchingVariant.stock_quantity + ' in stock';
        document.getElementById('variantInfo').classList.remove('hidden');
        
        const quantityInput = document.getElementById('quantity');
        quantityInput.max = matchingVariant.stock_quantity;
        if (parseInt(quantityInput.value) > matchingVariant.stock_quantity) {
            quantityInput.value = matchingVariant.stock_quantity;
        }
    } else {
        document.getElementById('variantInfo').classList.add('hidden');
        document.getElementById('selectedVariantId').value = '';
        alert('No variant found for the selected combination. Please try different options.');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const addToCartForm = document.getElementById('addToCartForm');
    if (addToCartForm) {
        addToCartForm.addEventListener('submit', function(e) {
            if (totalAttributes > 0 && !document.getElementById('selectedVariantId').value) {
                e.preventDefault();
                alert('Please select all product options before adding to cart.');
                return false;
            }
        });
    }
});
@endif
</script>
@endsection
