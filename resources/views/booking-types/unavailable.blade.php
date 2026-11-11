@extends('layouts.shop')

@section('title', 'Book: ' . ucfirst($bookingType->name))

@section('content')
<x-hero-section
    pageIdentifier="booking-types-unavailable"
    :fallbackTitle="ucfirst($bookingType->name) . ' Booking'"
    :fallbackSubtitle="$bookingType->description"
    fallbackImage="/images/hero-banners/salon-banner.avif"
    
/>

<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <a href="{{ route('booking-types.index') }}" class="text-indigo-400 hover:text-indigo-300 text-sm">
            &larr; All booking types
        </a>
    </div>

    <div class="max-w-2xl mx-auto bg-gray-800 rounded-lg border border-gray-700 p-12 text-center">
        <div class="w-16 h-16 rounded-full bg-indigo-900 text-indigo-300 flex items-center justify-center mx-auto mb-4">
            {!! $bookingType->icon_html !!}
        </div>
        <h2 class="text-xl font-bold text-white mb-2 capitalize">{{ $bookingType->name }} booking isn't available yet</h2>
        <p class="text-gray-400 mb-6">{{ $bookingType->description }}</p>
        <a href="{{ route('booking-types.index') }}" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg">
            See what's available to book
        </a>
    </div>
</div>
@endsection
