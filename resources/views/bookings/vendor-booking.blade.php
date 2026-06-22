@extends('layouts.shop')

@section('title', 'Book at ' . $vendor->name)

@section('content')
<x-hero-section
    pageIdentifier="vendor-booking"
    fallbackTitle="Book Your Experience"
    fallbackSubtitle="Choose from appointments, reservations, rentals & more"
    fallbackImage="/images/hero-banners/booking-banner.avif"
    headingTag="h2"
    :animated="true"
/>

<div class="container mx-auto px-4 py-10">
    <div class="mb-6">
        <a href="{{ route('vendors.show', $vendor) }}" class="text-indigo-400 hover:text-indigo-300 text-sm">
            ← Back to {{ $vendor->name }}
        </a>
    </div>

    <div class="max-w-6xl mx-auto">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-white">Book at {{ $vendor->name }}</h1>
            <p class="text-gray-400 mt-2">Select your preferred booking type and customize your experience</p>
        </div>

        @auth
            <form 
                action="{{ route('bookings.store', ['service' => 0]) }}" 
                method="POST"
                id="bookingForm"
            >
                @csrf
                <input type="hidden" name="vendor_id" value="{{ $vendor->id }}">
                <input type="hidden" name="booking_type" x-model="selectedType">
                <input type="hidden" name="booking_option_id" x-model="selectedOption?.id">
                <input type="hidden" name="booking_date" x-model="selectedDate">
                <input type="hidden" name="booking_time" x-model="selectedSlot?.time">
                <input type="hidden" name="rental_start_date" x-model="rentalStartDate">
                <input type="hidden" name="rental_end_date" x-model="rentalEndDate">
                <input type="hidden" name="attendees" x-model="attendees">
                <input type="hidden" name="notes" x-model="notes">
                <input type="hidden" name="total_price" x-model="totalPrice">
                
                <x-booking.booking-form
                    :vendor="$vendor"
                    :bookingTypes="$bookingTypes"
                    :selectedType="null"
                    :selectedOption="null"
                    :availableSlots="[]"
                    :calculatedPrice="null"
                />
            </form>
        @else
            <div class="bg-gray-800 rounded-2xl border border-gray-700 p-8 text-center">
                <div class="mb-4">
                    <svg class="w-16 h-16 mx-auto text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-white mb-2">Sign in to Book</h3>
                <p class="text-gray-400 mb-6">You need to be signed in to make a booking.</p>
                <div class="flex justify-center gap-4">
                    <a href="{{ route('login') }}" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold rounded-lg transition">
                        Sign In
                    </a>
                    <a href="{{ route('register') }}" class="px-6 py-3 bg-gray-700 hover:bg-gray-600 text-white font-semibold rounded-lg transition">
                        Create Account
                    </a>
                </div>
            </div>
        @endauth
    </div>
</div>
@endsection

