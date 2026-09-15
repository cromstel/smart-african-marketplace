@extends('vendor.layout')

@section('title', 'Availability: ' . $bookingResource->name)
@section('header', 'Resource Availability')

@section('content')
<div class="max-w-4xl space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('vendor.booking-resources.show', $bookingResource) }}" class="text-gray-400 hover:text-white transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h3 class="text-2xl font-bold text-white">{{ $bookingResource->name }}</h3>
            <p class="text-gray-400 mt-1 text-sm">
                {{ $startDate->format('M d, Y') }} &ndash; {{ $endDate->format('M d, Y') }}
            </p>
        </div>
    </div>

    <form method="GET" action="{{ route('vendor.booking-resources.availability', $bookingResource) }}" class="bg-gray-800 rounded-lg border border-gray-700 p-6 flex flex-wrap items-end gap-4">
        <div>
            <label for="start" class="block text-sm font-medium text-gray-300 mb-2">From</label>
            <input type="date" id="start" name="start" value="{{ $startDate->toDateString() }}"
                   class="bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-indigo-500">
        </div>
        <div>
            <label for="end" class="block text-sm font-medium text-gray-300 mb-2">To</label>
            <input type="date" id="end" name="end" value="{{ $endDate->toDateString() }}"
                   class="bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-indigo-500">
        </div>
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg font-semibold">
            Update Range
        </button>
    </form>

    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
        <h4 class="text-lg font-semibold text-white mb-4">Unavailable Dates</h4>
        @if(count($unavailableDates) > 0)
            <div class="flex flex-wrap gap-2">
                @foreach($unavailableDates as $date)
                    <span class="px-3 py-1 text-xs rounded bg-red-900 text-red-300">
                        {{ \Carbon\Carbon::parse($date)->format('M d, Y') }}
                    </span>
                @endforeach
            </div>
        @else
            <p class="text-gray-400">Fully available across this date range.</p>
        @endif
    </div>

    <div class="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-700">
            <h4 class="text-lg font-semibold text-white">Bookings in Range</h4>
        </div>
        <table class="w-full">
            <thead class="bg-gray-900">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Booking</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Dates</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Qty</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                @forelse($bookings as $booking)
                    <tr class="hover:bg-gray-700">
                        <td class="px-6 py-4 whitespace-nowrap text-gray-300 font-mono text-sm">{{ $booking->booking_number }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-300 text-sm">{{ $booking->user->name ?? $booking->guest_name ?? 'Guest' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-300 text-sm">
                            {{ \Carbon\Carbon::parse($booking->booking_date)->format('M d, Y') }}
                            @if($booking->end_date && \Carbon\Carbon::parse($booking->end_date)->ne(\Carbon\Carbon::parse($booking->booking_date)))
                                &ndash; {{ \Carbon\Carbon::parse($booking->end_date)->format('M d, Y') }}
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-300 text-sm">{{ $booking->quantity ?? 1 }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $statusColors = [
                                    'pending' => 'bg-yellow-900 text-yellow-300',
                                    'confirmed' => 'bg-blue-900 text-blue-300',
                                    'completed' => 'bg-green-900 text-green-300',
                                    'cancelled' => 'bg-red-900 text-red-300',
                                ];
                            @endphp
                            <span class="px-2 py-1 text-xs rounded {{ $statusColors[$booking->status] ?? 'bg-gray-700 text-gray-300' }}">
                                {{ ucfirst($booking->status) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-400">No bookings in this date range.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
