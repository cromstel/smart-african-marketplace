@extends('layouts.shop')

@section('title', 'Book: ' . ucfirst($bookingType->name))

@section('content')
<x-hero-section
    pageIdentifier="booking-types-resources"
    :fallbackTitle="'Book a ' . ucfirst($bookingType->name)"
    :fallbackSubtitle="$bookingType->description"
    fallbackImage="/images/hero-banners/salon-banner.avif"
    
/>

<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <a href="{{ route('booking-types.index') }}" class="text-indigo-400 hover:text-indigo-300 text-sm">
            &larr; All booking types
        </a>
    </div>

    @if($resources->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($resources as $resource)
                <a href="{{ route('resource-bookings.create', $resource) }}"
                   class="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden hover:border-indigo-500 transition-colors block">
                    @if($resource->image)
                        <img src="{{ '/storage/' . ltrim($resource->image ?? '', '/') }}" alt="{{ $resource->name }}" class="w-full h-40 object-cover" loading="lazy">
                    @else
                        <div class="w-full h-40 bg-gray-700 flex items-center justify-center">
                            <svg class="w-12 h-12 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                    @endif
                    <div class="p-4">
                        <h3 class="text-lg font-bold text-white mb-1">{{ $resource->name }}</h3>
                        <p class="text-gray-400 text-sm mb-3">{{ $resource->vendor->business_name ?? 'N/A' }}</p>
                        <div class="flex items-center justify-between">
                            <p class="text-xl font-bold text-white">{{ $resource->price_label }}</p>
                            <p class="text-xs text-gray-400">{{ $resource->quantity_available ?? $resource->quantity_total }} available</p>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        @if($resources->hasPages())
            <div class="mt-8">
                {{ $resources->links() }}
            </div>
        @endif
    @else
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-12 text-center">
            <p class="text-gray-400 text-lg">No {{ $bookingType->name }} resources available right now.</p>
        </div>
    @endif
</div>
@endsection
