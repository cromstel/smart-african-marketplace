@extends('layouts.customer-console')

@section('title', 'Booking Details - Customer Console')

@section('content')
<div class="max-w-2xl mx-auto">
    <h2 class="text-lg font-semibold text-white mb-6">Booking Details</h2>

    <div class="bg-gray-800 border border-gray-700 rounded-lg p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <p class="text-sm text-gray-400">Service</p>
                <p class="text-white">{{ $booking->service_name }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-400">Salon</p>
                <p class="text-white">{{ $booking->salon_name }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-400">Date</p>
                <p class="text-white">{{ $booking->booking_date }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-400">Status</p>
                <span class="px-2 py-1 bg-yellow-900/30 text-yellow-400 rounded text-xs">{{ $booking->status }}</span>
            </div>
        </div>
    </div>
</div>
@endsection
