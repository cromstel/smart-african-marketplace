@extends('vendor.layout')

@section('title', 'Product Reviews')
@section('header', 'Customer Reviews')

@section('content')
<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-gray-800 rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-400">Total Reviews</p>
                    <p class="text-2xl font-bold text-white">{{ $stats['total'] }}</p>
                </div>
                <svg class="w-8 h-8 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                </svg>
            </div>
        </div>
        <div class="bg-gray-800 rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-400">Approved</p>
                    <p class="text-2xl font-bold text-green-400">{{ $stats['approved'] }}</p>
                </div>
                <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
        <div class="bg-gray-800 rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-400">Pending</p>
                    <p class="text-2xl font-bold text-yellow-400">{{ $stats['pending'] }}</p>
                </div>
                <svg class="w-8 h-8 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
        <div class="bg-gray-800 rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-400">Avg Rating</p>
                    <p class="text-2xl font-bold text-indigo-400">{{ number_format($stats['average_rating'], 1) }}</p>
                </div>
                <svg class="w-8 h-8 text-indigo-500" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
            </div>
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-4">
        <a href="{{ route('vendor.reviews.index') }}" class="px-4 py-2 rounded-lg {{ !request('status') ? 'bg-indigo-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600' }}">
            All
        </a>
        <a href="{{ route('vendor.reviews.index', ['status' => 'approved']) }}" class="px-4 py-2 rounded-lg {{ request('status') === 'approved' ? 'bg-green-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600' }}">
            Approved
        </a>
        <a href="{{ route('vendor.reviews.index', ['status' => 'pending']) }}" class="px-4 py-2 rounded-lg {{ request('status') === 'pending' ? 'bg-yellow-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600' }}">
            Pending
        </a>
        <form method="GET" class="ml-auto">
            <input type="hidden" name="status" value="{{ request('status') }}">
            <select name="sort" onchange="this.form.submit()" class="bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-white">
                <option value="">Sort: Latest</option>
                <option value="rating_high" {{ request('sort') === 'rating_high' ? 'selected' : '' }}>Rating High</option>
                <option value="rating_low" {{ request('sort') === 'rating_low' ? 'selected' : '' }}>Rating Low</option>
                <option value="helpful" {{ request('sort') === 'helpful' ? 'selected' : '' }}>Most Helpful</option>
            </select>
        </form>
    </div>

    <div class="bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="divide-y divide-gray-700">
            @forelse($reviews as $review)
                <div class="p-6 hover:bg-gray-750 transition">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center space-x-4 mb-2">
                                <div class="flex items-center">
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($i <= $review->rating)
                                            <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                        @else
                                            <svg class="w-5 h-5 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                        @endif
                                    @endfor
                                </div>
                                @if($review->is_verified_purchase)
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-900 text-green-200">Verified Purchase</span>
                                @endif
                                @if($review->is_approved)
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-900 text-green-200">Approved</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-900 text-yellow-200">Pending</span>
                                @endif
                            </div>
                            
                            @if($review->title)
                                <h3 class="text-lg font-semibold text-white mb-2">{{ $review->title }}</h3>
                            @endif
                            
                            <p class="text-gray-300 mb-3">{{ $review->comment }}</p>
                            
                            <div class="flex items-center space-x-4 text-sm text-gray-400">
                                <span>By {{ $review->user->name }}</span>
                                <span>•</span>
                                <span>{{ $review->created_at->diffForHumans() }}</span>
                                <span>•</span>
                                <span>{{ $review->helpful_votes_count }} helpful</span>
                                <span>•</span>
                                <a href="{{ route('vendor.products.edit', $review->product) }}" class="text-indigo-400 hover:text-indigo-300">
                                    {{ $review->product->name }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-6 py-12 text-center text-gray-400">
                    <svg class="mx-auto h-12 w-12 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                    </svg>
                    <p class="mt-4">No reviews found.</p>
                </div>
            @endforelse
        </div>
    </div>

    @if($reviews->hasPages())
        <div class="mt-4">
            {{ $reviews->links() }}
        </div>
    @endif
</div>
@endsection


