@extends('admin.layout')

@section('title', 'Bookings')
@section('header', 'Booking Management')

@section('content')
<div class="mb-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h3 class="text-2xl font-bold text-white">All Bookings</h3>
            <p class="text-gray-400 mt-1">Manage salon service bookings and appointments</p>
        </div>
        <a href="{{ route('admin.bookings.calendar') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg">
            View Calendar
        </a>
    </div>
</div>

<div class="mb-6 bg-gray-800 rounded-lg border border-gray-700 p-4">
    <form method="GET" class="flex flex-wrap gap-4">
        <div class="flex-1 min-w-[220px]">
            <input type="text" name="search" value="{{ request('search') }}" 
                   placeholder="Search by booking number, customer name, or email..."
                   class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
        </div>
        <div>
            <select name="vendor" class="bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                <option value="">All Vendors</option>
                @foreach($vendors as $vendor)
                    <option value="{{ $vendor->id }}" {{ request('vendor') == $vendor->id ? 'selected' : '' }}>
                        {{ $vendor->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <select name="service" class="bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                <option value="">All Services</option>
                @foreach($services as $service)
                    <option value="{{ $service->id }}" {{ request('service') == $service->id ? 'selected' : '' }}>
                        {{ $service->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <select name="status" class="bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                <option value="">All Statuses</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
        </div>
        <div>
            <input type="date" name="date_from" value="{{ request('date_from') }}"
                   class="bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
        </div>
        <div>
            <input type="date" name="date_to" value="{{ request('date_to') }}"
                   class="bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
        </div>
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg">
            Filter
        </button>
        @if(request()->hasAny(['search', 'status', 'vendor', 'service', 'date_from', 'date_to']))
            <a href="{{ route('admin.bookings.index') }}" class="bg-gray-700 hover:bg-gray-600 text-white px-6 py-2 rounded-lg">
                Clear
            </a>
        @endif
    </form>
</div>

<div class="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden">
    <table class="min-w-full divide-y divide-gray-700">
        <thead class="bg-gray-900">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Booking</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Customer</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Service</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Date & Time</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Price</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-700">
            @forelse($bookings as $booking)
                <tr class="hover:bg-gray-750">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-bold text-white">{{ $booking->booking_number }}</div>
                        <div class="text-xs text-gray-400">{{ $booking->created_at->format('M d, Y') }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-white">{{ $booking->user->name }}</div>
                        <div class="text-xs text-gray-400">{{ $booking->user->email }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-white">{{ $booking->salonService->name }}</div>
                        <div class="text-xs text-gray-400">{{ $booking->duration_minutes }} minutes</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-white">{{ \Carbon\Carbon::parse($booking->booking_date)->format('M d, Y') }}</div>
                        <div class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($booking->booking_time)->format('g:i A') }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-white">
                        ${{ number_format($booking->price, 2) }}
                    </td>
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
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="{{ route('admin.bookings.show', $booking) }}" class="text-indigo-400 hover:text-indigo-300">
                            View Details
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-gray-400">
                        No bookings found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($bookings->hasPages())
    <div class="mt-6">
        {{ $bookings->links() }}
    </div>
@endif
@endsection

