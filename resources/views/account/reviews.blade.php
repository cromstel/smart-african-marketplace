@extends('layouts.shop')

@section('title', 'My Reviews')

@section('content')
<x-hero-section
    pageIdentifier="reviews"
    fallbackTitle="My Reviews"
    fallbackSubtitle="Track and manage your product reviews"
    fallbackImage="/images/hero-banners/hero-banner.avif"
    :animated="true"
/>

<div class="container mx-auto px-4 py-8">
    @if($reviews->count() > 0)
        <div class="space-y-4">
            @foreach($reviews as $review)
                <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
                    <div class="flex flex-col lg:flex-row gap-6">
                        <div class="flex-1">
                            <div class="flex flex-wrap items-center gap-2 mb-3">
                                <h3 class="text-xl font-semibold text-white">
                                    {{ $review->product?->name ?? 'Product removed' }}
                                </h3>
                                @if($review->is_approved)
                                    <span class="px-2 py-1 text-xs rounded bg-green-900 text-green-300">Approved</span>
                                @else
                                    <span class="px-2 py-1 text-xs rounded bg-yellow-900 text-yellow-300">Pending</span>
                                @endif
                                @if($review->is_verified_purchase)
                                    <span class="px-2 py-1 text-xs rounded bg-blue-900 text-blue-300">Verified Purchase</span>
                                @endif
                            </div>

                            <div class="flex items-center gap-1 mb-3">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-4 h-4 {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-600' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                @endfor
                            </div>

                            @if($review->title)
                                <p class="text-white font-medium mb-1">{{ $review->title }}</p>
                            @endif
                            <p class="text-gray-300">{{ $review->comment }}</p>

                            @if($review->images && $review->images->count() > 0)
                                <div class="flex flex-wrap gap-2 mt-3">
                                    @foreach($review->images as $image)
                                        <img src="{{ '/storage/' . ltrim($image->path ?? '', '/') }}" alt="Review image" class="w-16 h-16 object-cover rounded border border-gray-700">
                                    @endforeach
                                </div>
                            @endif

                            <div class="flex flex-wrap items-center gap-3 text-sm text-gray-400 mt-3">
                                <span>{{ $review->created_at->format('M d, Y') }}</span>
                                <span>{{ $review->helpful_votes_count }} helpful</span>
                            </div>
                        </div>

                        <div class="w-full lg:w-56 space-y-3">
                            @if($review->product && $review->product->featured_image)
                                <img src="{{ '/storage/' . ltrim($review->product->featured_image ?? '', '/') }}" alt="{{ $review->product->name }}" class="w-full h-36 object-cover rounded-lg border border-gray-700">
                            @endif

                            @if($review->product)
                                <a href="{{ route('shop.show', $review->product) }}" class="block bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded text-center">
                                    View Product
                                </a>
                            @endif

                            <form action="{{ route('reviews.destroy', $review) }}" method="POST" onsubmit="return confirm('Delete your review?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded">
                                    Delete Review
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $reviews->links() }}
        </div>
    @else
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-12 text-center">
            <svg class="w-16 h-16 mx-auto text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
            </svg>
            <h2 class="text-2xl font-bold text-white mb-2">No reviews yet</h2>
            <p class="text-gray-400 mb-6">Start shopping and share your feedback.</p>
            <a href="{{ route('shop.index') }}" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-medium">
                Browse Products
            </a>
        </div>
    @endif
</div>
@endsection

