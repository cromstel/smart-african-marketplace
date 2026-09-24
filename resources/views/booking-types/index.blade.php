@extends('layouts.shop')

@section('title', 'Book a Service or Rental')

@section('content')
<x-hero-section
    pageIdentifier="booking-types"
    fallbackTitle="Start a Booking"
    fallbackSubtitle="Choose what you'd like to book"
    fallbackImage="/images/hero-banners/booking-banner.avif"
    :animated="true"
/>

<div class="container mx-auto px-4 py-8">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($bookingTypes as $bookingType)
            <a href="{{ route('booking-types.show', $bookingType) }}"
               class="bg-gray-800 rounded-lg border border-gray-700 p-6 hover:border-indigo-500 transition-colors block">
                <div class="w-12 h-12 rounded-lg bg-indigo-900 text-indigo-300 flex items-center justify-center mb-4">
                    {!! $bookingType->icon_html !!}
                </div>
                <h3 class="text-lg font-bold text-white capitalize mb-2">{{ $bookingType->name }}</h3>
                <p class="text-gray-400 text-sm line-clamp-3">{{ $bookingType->description }}</p>
            </a>
        @endforeach
    </div>
</div>
@endsection
