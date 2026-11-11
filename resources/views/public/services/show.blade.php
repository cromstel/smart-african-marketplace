@extends('layouts.shop')

@section('title', '{{ $service->name }} - Smart African Market')
@section('seoDescription', 'Book {{ $service->name }} from {{ $service->vendor->business_name }}. {{ $service->description }}')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <header class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2">{{ $service->name }}</h1>
                <p class="text-gray-400">{{ $service->description }}</p>
            </div>
            <span class="text-indigo-400 text-2xl font-bold">${{ number_format($service->price, 2) }}</span>
        </div>
    </header>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div>
            <div class="h-64 bg-gray-700 rounded-lg overflow-hidden mb-6">
                @if($service->image)
                    <img src="{{ asset('storage/'.ltrim($service->image, '/')) }}"
                         alt="{{ $service->name }}"
                         class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center text-gray-500">
                        <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                            </path>
                        </svg>
                    </div>
                @endif
            </div>

            <div class="bg-gray-800 border border-gray-700 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Service Details</h3>
                <div class="space-y-4">
                    <div class="flex justify-between border-b border-gray-700 pb-2">
                        <span class="text-gray-400">Category</span>
                        <span class="text-white">{{ $service->category->name }}</span>
                    </div>
                    <div class="flex justify-between border-b border-gray-700 pb-2">
                        <span class="text-gray-400">Type</span>
                        <span class="text-indigo-400">{{ ucfirst($service->category->gender_target ?? 'unisex') }}</span>
                    </div>
                    <div class="flex justify-between border-b border-gray-700 pb-2">
                        <span class="text-gray-400">Duration</span>
                        <span class="text-white">{{ $service->duration_minutes }} minutes</span>
                    </div>
                    <div class="flex justify-between border-b border-gray-700 pb-2">
                        <span class="text-gray-400">Price</span>
                        <span class="text-white">${{ number_format($service->price, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Status</span>
                        <span class="px-2 py-1 bg-green-900/30 text-green-400 rounded text-xs uppercase">{{ $service->status }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-gray-800 border border-gray-700 rounded-lg p-6 sticky top-24">
            <h3 class="text-lg font-semibold text-white mb-6">Book This Service</h3>

            <div class="space-y-4">
                <div class="border-b border-gray-700 pb-2">
                    <label class="block text-sm font-medium text-gray-300 mb-2">Select Date</label>
                    <input type="date" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                </div>

                <div class="border-b border-gray-700 pb-2">
                    <label class="block text-sm font-medium text-gray-300 mb-2">Select Time</label>
                    <input type="time" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Notes (optional)</label>
                    <textarea rows="3" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"></textarea>
                </div>

                <div class="pt-4 border-t border-gray-700">
                    <div class="text-lg font-bold text-white mb-2">Total: ${{ number_format($service->price, 2) }}</div>
                    <button class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-3 rounded-lg text-lg font-medium transition-colors">
                        Book Now
                    </button>
                </div>
            </div>

            <div class="mt-6 p-4 bg-gray-700 rounded-lg">
                <h4 class="text-sm font-semibold text-white mb-3">Vendor</h4>
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-gray-600 rounded-full flex items-center justify-center text-white font-semibold">
                        {{ strtoupper(substr($service->vendor->business_name, 0, 1)) }}
                    </div>
                    <div>
                        <div class="text-white font-medium">{{ $service->vendor->business_name }}</div>
                        <div class="text-xs text-gray-400">{{ $service->vendor->city }}, {{ $service->vendor->country }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Services -->
    <div class="mt-12">
        <h2 class="text-2xl font-bold text-white mb-6">Related Services</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @forelse($relatedServices as $related)
                <article class="bg-gray-800 border border-gray-700 rounded-lg overflow-hidden">
                    <div class="h-32 bg-gray-700">
                        @if($related->image)
                            <img src="{{ asset('storage/'.ltrim($related->image, '/')) }}"
                                 alt="{{ $related->name }}"
                                 class="w-full h-full object-cover">
                        @endif
                    </div>
                    <div class="p-4">
                        <h4 class="text-white font-medium">{{ $related->name }}</h4>
                        <p class="text-indigo-400 text-sm">${{ number_format($related->price, 2) }}</p>
                        <a href="{{ route('services.show', $related) }}" class="text-indigo-400 text-xs hover:underline">View details</a>
                    </div>
                </article>
            @empty
                <p class="text-gray-400">No related services available.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
