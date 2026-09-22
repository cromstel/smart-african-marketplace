@extends('admin.layout')

@section('title', 'Booking Types')
@section('header', 'Booking Types')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div>
        <h2 class="text-2xl font-bold text-white">Booking Types</h2>
        <p class="text-gray-400 mt-1">Manage the categories services can be booked under (appointments, reservations, rentals, and so on)</p>
    </div>
    <a href="{{ route('admin.booking-types.create') }}" class="bg-brand-from hover:opacity-90 text-white px-6 py-3 rounded-lg font-medium inline-flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        New Booking Type
    </a>
</div>

<div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-900 border-b border-gray-700">
                <tr>
                    <th class="text-left px-6 py-4 text-sm font-semibold text-gray-300">Booking Type</th>
                    <th class="text-left px-6 py-4 text-sm font-semibold text-gray-300">Default Duration</th>
                    <th class="text-left px-6 py-4 text-sm font-semibold text-gray-300">Booking Window</th>
                    <th class="text-left px-6 py-4 text-sm font-semibold text-gray-300">Cancellation</th>
                    <th class="text-left px-6 py-4 text-sm font-semibold text-gray-300">Bookings</th>
                    <th class="text-left px-6 py-4 text-sm font-semibold text-gray-300">Order</th>
                    <th class="text-left px-6 py-4 text-sm font-semibold text-gray-300">Status</th>
                    <th class="text-right px-6 py-4 text-sm font-semibold text-gray-300">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                @forelse($bookingTypes as $bookingType)
                    <tr class="hover:bg-gray-750 transition">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-gray-700 rounded-lg flex items-center justify-center mr-3 text-gray-400">
                                    {!! $bookingType->icon_html !!}
                                </div>
                                <div>
                                    <p class="text-white font-medium">{{ $bookingType->name }}</p>
                                    <code class="text-purple-400 text-xs">{{ $bookingType->slug }}</code>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-gray-300">
                            {{ $bookingType->default_duration_minutes }} min
                        </td>
                        <td class="px-6 py-4 text-gray-300">
                            {{ $bookingType->booking_window_days }} {{ Str::plural('day', $bookingType->booking_window_days) }}
                        </td>
                        <td class="px-6 py-4 text-gray-300">
                            {{ $bookingType->cancellation_policy_hours }}h notice
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 text-xs rounded-full bg-blue-900 text-blue-300 font-semibold">
                                {{ $bookingType->bookings_count }} {{ Str::plural('booking', $bookingType->bookings_count) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-300">
                            <div class="flex items-center gap-1">
                                <span>{{ $bookingType->sort_order }}</span>
                                <div class="flex flex-col ml-1">
                                    <form action="{{ route('admin.booking-types.reorder', $bookingType) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="direction" value="up">
                                        <button type="submit" aria-label="Move {{ $bookingType->name }} up" class="block text-gray-500 hover:text-white focus:outline-none focus:ring-2 focus:ring-gray-400 rounded leading-none">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.booking-types.reorder', $bookingType) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="direction" value="down">
                                        <button type="submit" aria-label="Move {{ $bookingType->name }} down" class="block text-gray-500 hover:text-white focus:outline-none focus:ring-2 focus:ring-gray-400 rounded leading-none">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <form action="{{ route('admin.booking-types.toggle', $bookingType) }}" method="POST" class="inline">
                                @csrf
                                @if($bookingType->is_active)
                                    <button type="submit" class="px-3 py-1 text-xs rounded-full bg-green-900 text-green-300 font-semibold focus:outline-none focus:ring-2 focus:ring-gray-400">Active</button>
                                @else
                                    <button type="submit" class="px-3 py-1 text-xs rounded-full bg-gray-700 text-gray-400 font-semibold focus:outline-none focus:ring-2 focus:ring-gray-400">Inactive</button>
                                @endif
                            </form>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.booking-types.edit', $bookingType) }}" class="text-brand-from hover:text-orange-300 p-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <form action="{{ route('admin.booking-types.destroy', $bookingType) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Are you sure? This will only work if there are no bookings using this type.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:text-red-300 p-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center">
                            <svg class="w-16 h-16 mx-auto text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <p class="text-gray-400 text-lg">No booking types yet</p>
                            <p class="text-gray-500 mt-2">Create booking types like Appointment, Reservation, or Rental to categorize how services can be booked</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
