@extends('layouts.shop')

@section('title', 'Booking Details')

@section('content')
<x-hero-section
    pageIdentifier="bookings"
    fallbackTitle="Booking Details"
    fallbackSubtitle="View your booking information"
    fallbackImage="/images/booking-banner.avif"
    headingTag="h2"
    :animated="true"
/>

<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <a href="{{ route('bookings.index') }}" class="text-indigo-400 hover:text-indigo-300 text-sm">
            ← Back to My Bookings
        </a>
    </div>

    <div class="max-w-4xl mx-auto">
        <div class="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden">
            <div class="bg-gray-900 px-6 py-4 border-b border-gray-700">
                <h2 class="text-2xl font-bold text-white">Booking Details</h2>
                <p class="text-gray-400 text-sm mt-1">Booking #{{ $booking->booking_number }}</p>
            </div>

            <div class="p-6 space-y-6">
                <div class="flex items-center justify-between pb-6 border-b border-gray-700">
                    <div>
                        <p class="text-gray-400 text-sm mb-1">Status</p>
                        @php
                            $statusColors = [
                                'pending' => 'bg-yellow-900 text-yellow-300',
                                'confirmed' => 'bg-blue-900 text-blue-300',
                                'completed' => 'bg-green-900 text-green-300',
                                'cancelled' => 'bg-red-900 text-red-300',
                            ];
                        @endphp
                        <span class="px-3 py-2 text-sm rounded {{ $statusColors[$booking->status] ?? 'bg-gray-700 text-gray-300' }}">
                            {{ ucfirst($booking->status) }}
                        </span>
                    </div>
                    @if(in_array($booking->status, ['pending', 'confirmed']))
                        <div class="flex flex-wrap gap-2">
                            @if($booking->salon_service_id && $booking->booking_date->isFuture())
                                <a href="{{ route('bookings.reschedule', $booking) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg">
                                    Reschedule
                                </a>
                            @endif
                            <form action="{{ route('bookings.cancel', $booking) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this booking?')">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg">
                                    Cancel Booking
                                </button>
                            </form>
                        </div>
                    @endif
                </div>

                @if($booking->booking_type === 'rental')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-gray-400 text-sm mb-2">Rental</p>
                            <div class="flex items-start gap-3">
                                @if($booking->resource?->image)
                                    <img src="{{ '/storage/' . ltrim($booking->resource->image ?? '', '/') }}" alt="{{ $booking->resource_name }}"
                                         class="w-20 h-20 object-cover rounded">
                                @endif
                                <div>
                                    <p class="text-white font-semibold text-lg">{{ $booking->resource_name }}</p>
                                    <p class="text-gray-400 text-sm">Quantity: {{ $booking->quantity }}</p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <p class="text-gray-400 text-sm mb-2">Vendor</p>
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-indigo-600 rounded-full flex items-center justify-center text-white font-bold text-lg">
                                    {{ substr($booking->resource->vendor->business_name ?? 'N', 0, 1) }}
                                </div>
                                <div>
                                    <p class="text-white font-semibold">{{ $booking->resource->vendor->business_name ?? 'N/A' }}</p>
                                    <p class="text-gray-400 text-sm">Verified Vendor</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6 border-t border-gray-700">
                        <div>
                            <p class="text-gray-400 text-sm mb-2">Start Date</p>
                            <p class="text-white font-semibold">{{ \Carbon\Carbon::parse($booking->booking_date)->format('l, F d, Y') }}</p>
                        </div>

                        <div>
                            <p class="text-gray-400 text-sm mb-2">End Date</p>
                            <p class="text-white font-semibold">{{ $booking->end_date ? \Carbon\Carbon::parse($booking->end_date)->format('l, F d, Y') : 'N/A' }}</p>
                        </div>

                        <div>
                            <p class="text-gray-400 text-sm mb-2">Quantity</p>
                            <p class="text-white font-semibold">{{ $booking->quantity }}</p>
                        </div>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-gray-400 text-sm mb-2">Service</p>
                            <div class="flex items-start gap-3">
                                @if($booking->salonService->image)
                                    <img src="{{ '/storage/' . ltrim($booking->salonService->image ?? '', '/') }}" alt="{{ $booking->salonService->name }}"
                                         class="w-20 h-20 object-cover rounded">
                                @endif
                                <div>
                                    <p class="text-white font-semibold text-lg">{{ $booking->salonService->name }}</p>
                                    {{-- Prefer the dedicated salon service category over the generic
                                         product category -- see SalonService::salonCategory(). --}}
                                    <p class="text-gray-400 text-sm">{{ $booking->salonService->salonCategory->name ?? $booking->salonService->category->name ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <p class="text-gray-400 text-sm mb-2">Service Provider</p>
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-indigo-600 rounded-full flex items-center justify-center text-white font-bold text-lg">
                                    {{ substr($booking->salonService->user->name ?? 'N', 0, 1) }}
                                </div>
                                <div>
                                    <p class="text-white font-semibold">{{ $booking->salonService->user->name ?? 'N/A' }}</p>
                                    <p class="text-gray-400 text-sm">Verified Provider</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6 border-t border-gray-700">
                        <div>
                            <p class="text-gray-400 text-sm mb-2">Date</p>
                            <p class="text-white font-semibold">{{ \Carbon\Carbon::parse($booking->booking_date)->format('l, F d, Y') }}</p>
                        </div>

                        <div>
                            <p class="text-gray-400 text-sm mb-2">Time</p>
                            <p class="text-white font-semibold">{{ \Carbon\Carbon::parse($booking->booking_time)->format('g:i A') }}</p>
                        </div>

                        <div>
                            <p class="text-gray-400 text-sm mb-2">Duration</p>
                            <p class="text-white font-semibold">{{ $booking->duration_minutes }} minutes</p>
                        </div>
                    </div>
                @endif

                <div class="pt-6 border-t border-gray-700">
                    <p class="text-gray-400 text-sm mb-2">Price</p>
                    <p class="text-3xl font-bold text-white">${{ number_format($booking->price, 2) }}</p>
                </div>

                @if($booking->customer_notes)
                    <div class="pt-6 border-t border-gray-700">
                        <p class="text-gray-400 text-sm mb-2">Your Notes</p>
                        <p class="text-white">{{ $booking->customer_notes }}</p>
                    </div>
                @endif

                <div class="pt-6 border-t border-gray-700">
                    <p class="text-gray-400 text-sm mb-3">Timeline</p>
                    <div class="space-y-3">
                        <div class="flex items-start">
                            <div class="w-2 h-2 bg-indigo-500 rounded-full mt-2 mr-3"></div>
                            <div>
                                <p class="text-white text-sm">Booking Created</p>
                                <p class="text-gray-400 text-xs">{{ $booking->created_at->format('M d, Y g:i A') }}</p>
                            </div>
                        </div>
                        @if($booking->completed_at)
                            <div class="flex items-start">
                                <div class="w-2 h-2 bg-green-500 rounded-full mt-2 mr-3"></div>
                                <div>
                                    <p class="text-white text-sm">Completed</p>
                                    <p class="text-gray-400 text-xs">{{ $booking->completed_at->format('M d, Y g:i A') }}</p>
                                </div>
                            </div>
                        @endif
                        @if($booking->cancelled_at)
                            <div class="flex items-start">
                                <div class="w-2 h-2 bg-red-500 rounded-full mt-2 mr-3"></div>
                                <div>
                                    <p class="text-white text-sm">Cancelled</p>
                                    <p class="text-gray-400 text-xs">{{ $booking->cancelled_at->format('M d, Y g:i A') }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                @if($booking->status === 'pending')
                    <div class="bg-yellow-900 border border-yellow-700 rounded-lg p-4">
                        <p class="text-yellow-100 text-sm">
                            <strong>Pending Confirmation:</strong> Your booking is awaiting confirmation from the service provider. 
                            You will be notified once it's confirmed.
                        </p>
                    </div>
                @endif

                @if($booking->status === 'confirmed')
                    <div class="bg-blue-900 border border-blue-700 rounded-lg p-4">
                        <p class="text-blue-100 text-sm">
                            <strong>Confirmed:</strong> Your booking is confirmed! Please arrive on time for your appointment.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

