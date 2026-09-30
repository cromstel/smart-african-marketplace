@extends('admin.layout')

@section('title', 'Booking Types')
@section('header', 'Booking Types')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
    <div>
        <h2 class="text-2xl font-bold text-white">Booking Types</h2>
        <p class="text-gray-400 mt-1">Durations, booking windows and cancellation notice for each kind of booking</p>
    </div>
    <a href="{{ route('admin.booking-types.create') }}" class="bg-brand-from hover:opacity-90 text-white px-6 py-3 rounded-lg font-medium inline-flex items-center self-start focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-900 focus:ring-brand-from">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
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
                    <th scope="col" class="text-left px-6 py-4 text-sm font-semibold text-gray-300">Type</th>
                    <th scope="col" class="text-left px-6 py-4 text-sm font-semibold text-gray-300">Duration</th>
                    <th scope="col" class="text-left px-6 py-4 text-sm font-semibold text-gray-300">Window</th>
                    <th scope="col" class="text-left px-6 py-4 text-sm font-semibold text-gray-300">Cancellation</th>
                    <th scope="col" class="text-left px-6 py-4 text-sm font-semibold text-gray-300">Bookings</th>
                    <th scope="col" class="text-left px-6 py-4 text-sm font-semibold text-gray-300">Order</th>
                    <th scope="col" class="text-left px-6 py-4 text-sm font-semibold text-gray-300">Status</th>
                    <th scope="col" class="text-right px-6 py-4 text-sm font-semibold text-gray-300">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                @forelse($bookingTypes as $bookingType)
                    <tr>
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-gray-700 rounded-lg flex items-center justify-center mr-3 text-gray-300 shrink-0" aria-hidden="true">
                                    {!! $bookingType->icon_html !!}
                                </div>
                                <div>
                                    <p class="text-white font-medium">{{ Str::headline($bookingType->name) }}</p>
                                    <code class="text-purple-400 text-xs">{{ $bookingType->slug }}</code>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-gray-300 text-sm whitespace-nowrap">
                            {{ $bookingType->default_duration_minutes }} min
                            @if($bookingType->min_duration_minutes || $bookingType->max_duration_minutes)
                                <span class="block text-gray-500 text-xs">
                                    {{ $bookingType->min_duration_minutes ?? '—' }}–{{ $bookingType->max_duration_minutes ?? '—' }} min
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-gray-300 text-sm whitespace-nowrap">{{ $bookingType->booking_window_days }} days</td>
                        <td class="px-6 py-4 text-gray-300 text-sm whitespace-nowrap">{{ $bookingType->cancellation_policy_hours }}h notice</td>
                        <td class="px-6 py-4 text-gray-300 text-sm">{{ number_format($bookingType->bookings_count) }}</td>
                        <td class="px-6 py-4 text-gray-300 text-sm">{{ $bookingType->sort_order }}</td>
                        <td class="px-6 py-4">
                            <form action="{{ route('admin.booking-types.toggle', $bookingType) }}" method="POST">
                                @csrf
                                <button type="submit"
                                        class="px-3 py-1 text-xs rounded-full font-semibold focus:outline-none focus:ring-2 focus:ring-brand-from {{ $bookingType->is_active ? 'bg-green-900 text-green-300' : 'bg-gray-700 text-gray-400' }}"
                                        aria-label="{{ $bookingType->is_active ? 'Deactivate' : 'Activate' }} {{ $bookingType->name }}">
                                    {{ $bookingType->is_active ? 'Active' : 'Inactive' }}
                                </button>
                            </form>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.booking-types.edit', $bookingType) }}" class="text-brand-from hover:text-orange-300 p-2 rounded focus:outline-none focus:ring-2 focus:ring-brand-from" aria-label="Edit {{ $bookingType->name }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                @if($bookingType->bookings_count === 0)
                                    <form action="{{ route('admin.booking-types.destroy', $bookingType) }}" method="POST"
                                          onsubmit="return confirm('Delete this booking type?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-400 hover:text-red-300 p-2 rounded focus:outline-none focus:ring-2 focus:ring-red-400" aria-label="Delete {{ $bookingType->name }}">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center">
                            <p class="text-gray-400 text-lg">No booking types yet</p>
                            <p class="text-gray-500 mt-2">Run the BookingTypeSeeder for the six built-in types, or create one.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
