@extends('vendor.layout')

@section('title', 'Review from ' . ($review->user->name ?? 'Customer'))
@section('header', 'Review Detail')

@section('content')
<div class="max-w-3xl space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('vendor.reviews.index') }}" class="text-gray-400 hover:text-white transition" aria-label="Back">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h3 class="text-2xl font-bold text-white">{{ $review->title ?: 'Customer Review' }}</h3>
            <p class="text-gray-400 mt-1 text-sm">Posted {{ $review->created_at->format('M d, Y g:i A') }}</p>
        </div>
        <span class="ml-auto px-3 py-1 text-xs font-semibold rounded-full {{ $review->is_approved ? 'bg-green-900 text-green-200' : 'bg-yellow-900 text-yellow-200' }}">
            {{ $review->is_approved ? 'Approved' : 'Pending' }}
        </span>
    </div>

    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
        <h4 class="text-lg font-semibold text-white mb-4">Product</h4>
        <div class="flex items-center gap-4">
            @if($review->product->featured_image ?? null)
                <img src="{{ '/storage/' . ltrim($review->product->featured_image, '/') }}"
                     alt="{{ $review->product->name }}"
                     class="w-16 h-16 object-cover rounded">
            @else
                <div class="w-16 h-16 bg-gray-700 rounded flex items-center justify-center">
                    <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            @endif
            <div>
                <p class="text-white font-medium">{{ $review->product->name }}</p>
                <p class="text-gray-400 text-sm">SKU: {{ $review->product->sku ?? 'N/A' }}</p>
            </div>
        </div>
    </div>

    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
        <h4 class="text-lg font-semibold text-white mb-4">Rating &amp; Reviewer</h4>
        <dl class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <dt class="text-sm text-gray-400">Rating</dt>
                <dd class="mt-1 flex items-center">
                    @for($i = 1; $i <= 5; $i++)
                        <svg class="w-5 h-5 {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-600' }}" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                    @endfor
                </dd>
            </div>
            <div>
                <dt class="text-sm text-gray-400">Customer</dt>
                <dd class="text-white">{{ $review->user->name ?? 'N/A' }}</dd>
            </div>
            <div>
                <dt class="text-sm text-gray-400">Verified Purchase</dt>
                <dd class="text-white">{{ $review->is_verified_purchase ? 'Yes' : 'No' }}</dd>
            </div>
        </dl>
    </div>

    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
        <h4 class="text-lg font-semibold text-white mb-4">Comment</h4>
        <p class="text-gray-300 whitespace-pre-line">{{ $review->comment }}</p>
    </div>
</div>
@endsection
