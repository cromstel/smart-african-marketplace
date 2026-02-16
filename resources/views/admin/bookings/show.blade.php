@extends('admin.layout')

@section('title', 'Booking Details')
@section('header', 'Booking: ' . $booking->booking_number)

@section('content')
<div class="max-w-4xl space-y-6">
    <div class="flex gap-4">
        <a href="{{ route('admin.bookings.index') }}" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
            ← Back to Bookings
        </a>
    </div>

    <div class="grid grid-cols-3 gap-6">
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h3 class="text-lg font-bold text-white mb-4">Booking Status</h3>
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

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h3 class="text-lg font-bold text-white mb-4">Booking Date</h3>
            <p class="text-white">{{ \Carbon\Carbon::parse($booking->booking_date)->format('M d, Y') }}</p>
            <p class="text-gray-400 text-sm">{{ \Carbon\Carbon::parse($booking->booking_time)->format('g:i A') }}</p>
        </div>

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h3 class="text-lg font-bold text-white mb-4">Price</h3>
            <p class="text-2xl font-bold text-white">${{ number_format($booking->price, 2) }}</p>
        </div>
    </div>

    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
        <h3 class="text-lg font-bold text-white mb-4">Customer Information</h3>
        <dl class="grid grid-cols-2 gap-4">
            <div>
                <dt class="text-sm text-gray-400">Name</dt>
                <dd class="text-white">{{ $booking->user->name }}</dd>
            </div>
            <div>
                <dt class="text-sm text-gray-400">Email</dt>
                <dd class="text-white">{{ $booking->user->email }}</dd>
            </div>
            <div>
                <dt class="text-sm text-gray-400">Phone</dt>
                <dd class="text-white">{{ $booking->user->phone ?? 'N/A' }}</dd>
            </div>
            <div>
                <dt class="text-sm text-gray-400">Booking Number</dt>
                <dd class="text-white font-mono">{{ $booking->booking_number }}</dd>
            </div>
        </dl>
    </div>

    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
        <h3 class="text-lg font-bold text-white mb-4">Service Details</h3>
        <div class="flex items-start gap-4">
            @if($booking->salonService->image)
                <img src="{{ Storage::url($booking->salonService->image) }}" 
                     alt="{{ $booking->salonService->name }}" 
                     class="w-32 h-24 object-cover rounded">
            @endif
            <div class="flex-1">
                <h4 class="text-white font-semibold">{{ $booking->salonService->name }}</h4>
                <p class="text-gray-400 text-sm mt-2">{{ $booking->salonService->description }}</p>
                <div class="mt-4 flex gap-6">
                    <div>
                        <span class="text-gray-400 text-sm">Duration:</span>
                        <span class="text-white ml-2">{{ $booking->duration_minutes }} minutes</span>
                    </div>
                    <div>
                        <span class="text-gray-400 text-sm">Service Provider:</span>
                        <span class="text-white ml-2">{{ $booking->salonService->user->name ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($booking->customer_notes)
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h3 class="text-lg font-bold text-white mb-4">Customer Notes</h3>
            <p class="text-gray-300">{{ $booking->customer_notes }}</p>
        </div>
    @endif

    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
        <h3 class="text-lg font-bold text-white mb-4">Update Status</h3>
        <form action="{{ route('admin.bookings.update-status', $booking) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Status</label>
                <select name="status" required class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                    <option value="pending" {{ $booking->status == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="confirmed" {{ $booking->status == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="completed" {{ $booking->status == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ $booking->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Staff Notes</label>
                <textarea name="staff_notes" rows="3" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                          placeholder="Add internal notes about this booking...">{{ old('staff_notes', $booking->staff_notes) }}</textarea>
            </div>

            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-medium">
                Update Booking
            </button>
        </form>
    </div>

    @if($booking->staff_notes)
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h3 class="text-lg font-bold text-white mb-4">Current Staff Notes</h3>
            <p class="text-gray-300">{{ $booking->staff_notes }}</p>
        </div>
    @endif

    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
        <h3 class="text-lg font-bold text-white mb-4">Booking Timeline</h3>
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
</div>
@endsection
