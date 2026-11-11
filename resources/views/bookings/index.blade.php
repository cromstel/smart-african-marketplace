@extends('layouts.shop')

@section('title', 'My Bookings')

@section('content')
<x-hero-section
    pageIdentifier="bookings"
    fallbackTitle="My Bookings"
    fallbackSubtitle="View and manage your bookings and rentals"
    fallbackImage="/images/booking-banner.avif"
    
/>

<div class="container mx-auto px-4 py-8">

    @if($bookings->count() > 0)
        <div class="space-y-4">
            @foreach($bookings as $booking)
                <div class="bg-gray-800 rounded-lg border border-gray-700 p-6 hover:border-indigo-500 transition-colors">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-3 mb-3">
                                <h3 class="text-xl font-bold text-white">{{ $booking->booking_type === 'rental' ? $booking->resource_name : $booking->salonService?->name }}</h3>
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-yellow-900 text-yellow-300',
                                        'confirmed' => 'bg-blue-900 text-blue-300',
                                        'completed' => 'bg-green-900 text-green-300',
                                        'cancelled' => 'bg-red-900 text-red-300',
                                    ];
                                @endphp
                                <span class="px-3 py-1 text-xs rounded {{ $statusColors[$booking->status] ?? 'bg-gray-700 text-gray-300' }}">
                                    {{ ucfirst($booking->status) }}
                                </span>
                            </div>

                            @if($booking->booking_type === 'rental')
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                    <div>
                                        <p class="text-gray-400">Booking Number</p>
                                        <p class="text-white font-mono">{{ $booking->booking_number }}</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-400">Dates</p>
                                        <p class="text-white">
                                            {{ \Carbon\Carbon::parse($booking->booking_date)->format('M d, Y') }}
                                            @if($booking->end_date)
                                                &ndash; {{ \Carbon\Carbon::parse($booking->end_date)->format('M d, Y') }}
                                            @endif
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-gray-400">Quantity</p>
                                        <p class="text-white">{{ $booking->quantity }}</p>
                                    </div>
                                </div>

                                <div class="mt-4 text-sm">
                                    <p class="text-gray-400">Vendor</p>
                                    <p class="text-white">{{ $booking->resource->vendor->business_name ?? 'N/A' }}</p>
                                </div>
                            @else
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                    <div>
                                        <p class="text-gray-400">Booking Number</p>
                                        <p class="text-white font-mono">{{ $booking->booking_number }}</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-400">Date & Time</p>
                                        <p class="text-white">
                                            {{ \Carbon\Carbon::parse($booking->booking_date)->format('M d, Y') }} at
                                            {{ \Carbon\Carbon::parse($booking->booking_time)->format('g:i A') }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-gray-400">Duration</p>
                                        <p class="text-white">{{ $booking->duration_minutes }} minutes</p>
                                    </div>
                                </div>

                                <div class="mt-4 text-sm">
                                    <p class="text-gray-400">Service Provider</p>
                                    <p class="text-white">{{ $booking->salonService->user->name ?? 'N/A' }}</p>
                                </div>
                            @endif

                            @if($booking->customer_notes)
                                <div class="mt-4">
                                    <p class="text-gray-400 text-sm">Your Notes</p>
                                    <p class="text-white text-sm">{{ $booking->customer_notes }}</p>
                                </div>
                            @endif
                        </div>

                        <div class="flex flex-col items-end gap-3">
                            <p class="text-3xl font-bold text-white">${{ number_format($booking->price, 2) }}</p>
                            
                            <div class="flex gap-2">
                                <a href="{{ route('bookings.show', $booking) }}" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm">
                                    View Details
                                </a>
                                @if($booking->salon_service_id && in_array($booking->status, ['pending', 'confirmed']) && $booking->booking_date->isFuture())
                                    <a href="{{ route('bookings.reschedule', $booking) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm">
                                        Reschedule
                                    </a>
                                @endif
                                @if(in_array($booking->status, ['pending', 'confirmed']))
                                    <form action="{{ route('bookings.cancel', $booking) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this booking?')">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm">
                                            Cancel
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if($bookings->hasPages())
            <div class="mt-8">
                {{ $bookings->links() }}
            </div>
        @endif
    @else
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-12 text-center">
            <svg class="w-16 h-16 text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <p class="text-gray-400 text-lg mb-4">No bookings yet</p>
            <a href="{{ route('booking-types.index') }}" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg">
                Start a Booking
            </a>
        </div>
    @endif
</div>
@endsection

