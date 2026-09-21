@extends('layouts.shop')

@section('title', $service->name)

@section('content')
<x-hero-section
    pageIdentifier="salon"
    heightPreset="standard"
    fallbackTitle="Service Details"
    fallbackSubtitle="View service information and book your appointment"
    fallbackImage="/images/hero-banners/salon-banner.avif"
    headingTag="h2"
    :animated="true"
/>

<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <a href="{{ route('salon.index') }}" class="text-indigo-400 hover:text-indigo-300 text-sm">
            ← Back to Services
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2">
            <div class="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden mb-6">
                @if($service->image)
                    <picture>
                        @php
                            $salonShowWebpPath = str_replace(pathinfo($service->image, PATHINFO_EXTENSION), 'webp', $service->image);
                        @endphp
                        @if(Storage::disk('public')->exists($salonShowWebpPath))
                            <source srcset="{{ '/storage/' . ltrim($salonShowWebpPath ?? '', '/') }}" type="image/webp">
                        @endif
                        <img src="{{ '/storage/' . ltrim($service->image ?? '', '/') }}" alt="{{ $service->name }}" class="w-full h-96 object-cover">
                    </picture>
                @else
                    <div class="w-full h-96 bg-gray-700 flex items-center justify-center">
                        <svg class="w-24 h-24 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                @endif

                <div class="p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h2 class="text-3xl font-bold text-white mb-2">{{ $service->name }}</h2>
                            <span class="px-3 py-1 text-sm rounded bg-indigo-900 text-indigo-300">
                                {{-- Prefer the dedicated salon service category (salon_category_id)
                                     over the generic product category (category_id) -- these are two
                                     separate taxonomies (see SalonService::salonCategory() /
                                     ::category()), and the listing page (salon/index.blade.php)
                                     already prefers salonCategory the same way. This page previously
                                     only checked category, which showed an unrelated/incorrect
                                     category badge whenever the two didn't match. --}}
                                {{ $service->salonCategory->name ?? $service->category->name ?? 'N/A' }}
                            </span>
                        </div>
                    </div>

                    <div class="border-t border-gray-700 pt-6 mt-6">
                        <h2 class="text-xl font-bold text-white mb-4">Description</h2>
                        <p class="text-gray-300 leading-relaxed whitespace-pre-wrap">{{ $service->description }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-1">
            <div class="bg-gray-800 rounded-lg border border-gray-700 p-6 sticky top-4 space-y-6">
                <div>
                    <p class="text-gray-400 text-sm mb-2">Price</p>
                    <p class="text-4xl font-bold text-white">${{ number_format($service->price, 2) }}</p>
                </div>

                <div class="border-t border-gray-700 pt-6">
                    <p class="text-gray-400 text-sm mb-2">Duration</p>
                    <p class="text-2xl font-semibold text-white">{{ $service->duration_minutes }} minutes</p>
                </div>

                <div class="border-t border-gray-700 pt-6">
                    <p class="text-gray-400 text-sm mb-2">Service Provider</p>
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-indigo-600 rounded-full flex items-center justify-center text-white font-bold mr-3">
                            {{ substr($service->user->name ?? 'N', 0, 1) }}
                        </div>
                        <div>
                            <p class="text-white font-medium">{{ $service->user->name ?? 'N/A' }}</p>
                            <p class="text-gray-400 text-xs">Verified Provider</p>
                        </div>
                    </div>
                </div>

                @if($service->stylist)
                    <div class="border-t border-gray-700 pt-6">
                        <p class="text-gray-400 text-sm mb-2">Stylist</p>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-gray-700 rounded-full flex items-center justify-center text-white font-bold mr-3">
                                    {{ substr($service->stylist->name ?? 'S', 0, 1) }}
                                </div>
                                <div>
                                    <p class="text-white font-medium">{{ $service->stylist->name }}</p>
                                    <p class="text-gray-400 text-xs">{{ $service->stylist->years_experience ? $service->stylist->years_experience.' yrs experience' : 'Experience N/A' }}</p>
                                </div>
                            </div>
                            <a href="{{ route('stylists.show', $service->stylist) }}" class="text-indigo-400 hover:text-indigo-300 text-sm">
                                View Profile
                            </a>
                        </div>
                    </div>
                @endif

                @auth
                    <div class="border-t border-gray-700 pt-6">
                        <a href="{{ route('bookings.create', $service) }}" class="block w-full bg-indigo-600 hover:bg-indigo-700 text-white text-center px-6 py-3 rounded-lg font-medium">
                            Book This Service
                        </a>
                    </div>
                @else
                    <div class="border-t border-gray-700 pt-6">
                        <a href="{{ route('login') }}" class="block w-full bg-indigo-600 hover:bg-indigo-700 text-white text-center px-6 py-3 rounded-lg font-medium">
                            Login to Book
                        </a>
                        <p class="text-gray-400 text-xs text-center mt-2">You need to login to make a booking</p>
                    </div>
                @endauth

                <div class="bg-gray-900 rounded p-4">
                    <h3 class="text-white font-semibold mb-2 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Booking Information
                    </h3>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li class="flex items-start">
                            <svg class="w-4 h-4 mr-2 mt-0.5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Instant booking confirmation
                        </li>
                        <li class="flex items-start">
                            <svg class="w-4 h-4 mr-2 mt-0.5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Available time slots shown
                        </li>
                        <li class="flex items-start">
                            <svg class="w-4 h-4 mr-2 mt-0.5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Professional service provider
                        </li>
                        <li class="flex items-start">
                            <svg class="w-4 h-4 mr-2 mt-0.5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Easy cancellation
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


