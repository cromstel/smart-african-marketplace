@extends('layouts.app')

@section('title', 'Book Service')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <a href="{{ route('salons.show', $service) }}" class="text-indigo-400 hover:text-indigo-300 text-sm">
            ← Back to Service
        </a>
    </div>

    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-white mb-8">Book Service</h1>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2">
                <form action="{{ route('bookings.store', $service) }}" method="POST" class="space-y-6">
                    @csrf

                    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
                        <h2 class="text-xl font-bold text-white mb-4">Select Date & Time</h2>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Booking Date *</label>
                                <input type="date" name="booking_date" id="booking_date" value="{{ old('booking_date') }}" 
                                       min="{{ now()->toDateString() }}" required
                                       class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                                       onchange="loadAvailableSlots()">
                                @error('booking_date') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Time Slot *</label>
                                <div id="time-slots-container">
                                    <p class="text-gray-400 text-sm">Please select a date first to see available time slots</p>
                                </div>
                                @error('booking_time') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
                        <h2 class="text-xl font-bold text-white mb-4">Additional Information</h2>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                Notes (Optional)
                            </label>
                            <textarea name="customer_notes" rows="4" 
                                      class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                                      placeholder="Any special requests or notes for the service provider...">{{ old('customer_notes') }}</textarea>
                            <p class="text-xs text-gray-500 mt-1">Max 500 characters</p>
                            @error('customer_notes') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-3 rounded-lg font-medium">
                            Confirm Booking
                        </button>
                        <a href="{{ route('salons.show', $service) }}" class="bg-gray-700 hover:bg-gray-600 text-white px-8 py-3 rounded-lg font-medium">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>

            <div class="lg:col-span-1">
                <div class="bg-gray-800 rounded-lg border border-gray-700 p-6 sticky top-4">
                    <h3 class="text-lg font-bold text-white mb-4">Booking Summary</h3>

                    @if($service->image)
                        <img src="{{ Storage::url($service->image) }}" alt="{{ $service->name }}" 
                             class="w-full h-32 object-cover rounded mb-4">
                    @endif

                    <h4 class="text-white font-semibold mb-2">{{ $service->name }}</h4>
                    <p class="text-gray-400 text-sm mb-4">{{ Str::limit($service->description, 100) }}</p>

                    <div class="space-y-3 border-t border-gray-700 pt-4">
                        <div class="flex justify-between">
                            <span class="text-gray-400 text-sm">Duration</span>
                            <span class="text-white">{{ $service->duration_minutes }} min</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400 text-sm">Provider</span>
                            <span class="text-white">{{ $service->user->name ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between items-center border-t border-gray-700 pt-3">
                            <span class="text-gray-400">Total Price</span>
                            <span class="text-2xl font-bold text-white">${{ number_format($service->price, 2) }}</span>
                        </div>
                    </div>

                    <div class="bg-gray-900 rounded p-4 mt-6">
                        <h5 class="text-white font-semibold mb-2 text-sm">Cancellation Policy</h5>
                        <p class="text-gray-400 text-xs">
                            You can cancel your booking at any time before the scheduled appointment. 
                            Please notify us as early as possible if you need to cancel.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
async function loadAvailableSlots() {
    const dateInput = document.getElementById('booking_date');
    const container = document.getElementById('time-slots-container');
    
    if (!dateInput.value) {
        container.innerHTML = '<p class="text-gray-400 text-sm">Please select a date first</p>';
        return;
    }

    container.innerHTML = '<p class="text-gray-400 text-sm">Loading available slots...</p>';

    try {
        const response = await fetch(`{{ route('bookings.available-slots', $service) }}?date=${dateInput.value}`);
        const data = await response.json();

        if (data.available_slots && data.available_slots.length > 0) {
            let slotsHtml = '<div class="grid grid-cols-3 gap-2">';
            data.available_slots.forEach(slot => {
                const time12h = new Date('2000-01-01T' + slot).toLocaleTimeString('en-US', {
                    hour: 'numeric',
                    minute: '2-digit',
                    hour12: true
                });
                slotsHtml += `
                    <label class="cursor-pointer">
                        <input type="radio" name="booking_time" value="${slot}" required class="peer sr-only">
                        <div class="bg-gray-700 border-2 border-gray-600 peer-checked:border-indigo-500 peer-checked:bg-indigo-900 hover:border-gray-500 rounded-lg px-3 py-2 text-center transition-colors">
                            <span class="text-white text-sm font-medium">${time12h}</span>
                        </div>
                    </label>
                `;
            });
            slotsHtml += '</div>';
            container.innerHTML = slotsHtml;
        } else {
            container.innerHTML = '<div class="bg-yellow-900 border border-yellow-700 rounded p-4"><p class="text-yellow-100 text-sm">No available time slots for this date. Please select another date.</p></div>';
        }
    } catch (error) {
        container.innerHTML = '<div class="bg-red-900 border border-red-700 rounded p-4"><p class="text-red-100 text-sm">Error loading time slots. Please try again.</p></div>';
    }
}
</script>
@endsection
