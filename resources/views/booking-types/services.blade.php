@extends('layouts.shop')

@section('title', 'Book: ' . ucfirst($bookingType->name))

@section('content')
<x-hero-section
    pageIdentifier="booking-types-services"
    :fallbackTitle="'Book an ' . ucfirst($bookingType->name)"
    :fallbackSubtitle="$bookingType->description"
    fallbackImage="/images/hero-banners/salon-banner.avif"
    :animated="true"
/>

<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <a href="{{ route('booking-types.index') }}" class="text-indigo-400 hover:text-indigo-300 text-sm">
            &larr; All booking types
        </a>
    </div>

    @if($services->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($services as $service)
                <a href="{{ route('salon.show', $service->slug) }}"
                   class="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden hover:border-indigo-500 transition-colors block">
                    @if($service->image)
                        <img src="{{ '/storage/' . ltrim($service->image ?? '', '/') }}" alt="{{ $service->name }}" class="w-full h-40 object-cover" loading="lazy">
                    @else
                        <div class="w-full h-40 bg-gray-700 flex items-center justify-center">
                            <svg class="w-12 h-12 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    @endif
                    <div class="p-4">
                        <h3 class="text-lg font-bold text-white mb-1">{{ $service->name }}</h3>
                        <p class="text-gray-400 text-sm mb-3">{{ $service->salonCategory->name ?? $service->category->name ?? 'N/A' }}</p>
                        <div class="flex items-center justify-between">
                            <p class="text-xl font-bold text-white">${{ number_format($service->price, 2) }}</p>
                            <p class="text-xs text-gray-400">{{ $service->duration_minutes }} min</p>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        @if($services->hasPages())
            <div class="mt-8">
                {{ $services->links() }}
            </div>
        @endif
    @else
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-12 text-center">
            <p class="text-gray-400 text-lg">No {{ $bookingType->name }} services available right now.</p>
        </div>
    @endif
</div>
@endsection
