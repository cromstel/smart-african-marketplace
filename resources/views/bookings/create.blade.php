@extends('layouts.shop')

@section('title', 'Book Service')

@section('content')
<x-hero-section
    pageIdentifier="bookings"
    fallbackTitle="Book Service"
    fallbackSubtitle="Select your preferred date and time"
    fallbackImage="/images/booking-banner.avif"
    headingTag="h2"
    :animated="true"
/>

<div class="container mx-auto px-4 py-10">
    <div class="mb-6">
        <a href="{{ route('salon.show', $service) }}" class="text-indigo-400 hover:text-indigo-300 text-sm">
            ← Back to Service
        </a>
    </div>

    <div class="max-w-6xl mx-auto space-y-6">
        <h2 class="text-3xl font-bold text-white">Reserve {{ $service->name }}</h2>

        {{--
            NOTE: @json() alone is unsafe here. Blade's @json() is plain
            json_encode() -- correct for embedding inside a <script> block,
            but the JSON it produces still contains literal double-quote
            characters (they delimit every key/string value), which
            prematurely terminates a double-quoted HTML attribute like
            x-data="...". That previously broke this attribute at the very
            first quote in $availableSlots, leaving Alpine unable to parse
            bookingSlotPicker(...) at all (SyntaxError: missing ) after
            argument list) -- the entire slot picker was non-functional.
            Illuminate\Support\Js::from() is Laravel's helper for exactly
            this case: it wraps the JSON as JSON.parse('...') with the
            content hex-escaped so it contains no literal quote characters,
            safe to embed raw inside an HTML attribute.
        --}}
        <form action="{{ route('bookings.store', $service) }}" method="POST"
              x-data="bookingSlotPicker({{ Illuminate\Support\Js::from($availableSlots) }}, '{{ $defaultDate ?? '' }}', {{ Illuminate\Support\Js::from($defaultSlot) }}, '{{ route('bookings.available-slots', $service) }}', '{{ route('bookings.calendar', $service) }}')"
              x-init="init()"
              x-cloak
              class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            @csrf

            <div class="lg:col-span-2 space-y-6">
                <div class="bg-gray-800 rounded-2xl border border-gray-700 p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-xl font-semibold text-white">Available Slots</h3>
                            <p class="text-gray-400 text-sm">Choose a date and time slot below.</p>
                        </div>
                        <button type="button" class="text-xs uppercase tracking-wide text-indigo-300 border border-indigo-500 px-3 py-1 rounded-full"
                                :disabled="loadingDate"
                                @click="refreshSlots(selectedDate)">
                            <span x-text="loadingDate === selectedDate ? 'Refreshing...' : 'Refresh'">Refresh</span>
                        </button>
                    </div>

                    <template x-if="Object.keys(slots).length">
                        <div class="space-y-6">
                            <template x-for="(meta, date) in slots" :key="date">
                                <div class="rounded-xl border border-gray-700 bg-gray-900 p-4 space-y-3">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm text-gray-400" x-text="meta.date_label"></p>
                                            <p class="text-xs text-gray-500" x-show="meta.is_today">Today</p>
                                        </div>
                                        <span class="px-2 py-1 text-xs rounded-full" :class="{
                                            'bg-indigo-600 text-white': selectedDate === date,
                                            'bg-gray-700 text-gray-300': selectedDate !== date
                                        }" x-text="selectedDate === date ? 'Selected' : 'View'">
                                        </span>
                                    </div>

                                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                                        <template x-for="slot in meta.slots" :key="slot.time">
                                            <button type="button"
                                                    class="border rounded-lg px-3 py-3 text-left transition"
                                                    :class="{
                                                        'border-indigo-500 bg-indigo-600 text-white': selectedDate === date && selectedSlot?.time === slot.time,
                                                        'border-gray-600 text-gray-200 bg-gray-800 hover:border-gray-500': slot.is_selectable,
                                                        'border-gray-700 text-gray-500 bg-gray-900 cursor-not-allowed': !slot.is_selectable
                                                    }"
                                                    :disabled="!slot.is_selectable"
                                                    @click="selectSlot(date, slot)">
                                                <div class="flex items-center justify-between">
                                                    <span class="font-medium" x-text="slot.label"></span>
                                                    <span class="text-xs uppercase tracking-wide" x-text="slot.status"></span>
                                                </div>
                                                <p class="text-xs text-gray-400 mt-1">Lead time: <span x-text="slot.lead_time_minutes + 'm'"></span></p>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>

                    <div class="bg-gray-900 border border-yellow-700 text-yellow-200 rounded-lg px-4 py-3" x-show="!Object.keys(slots).length">
                        No slots currently available. Please try another date or contact the vendor directly.
                    </div>

                    <div class="text-sm text-red-400" x-text="errorMessage" x-show="errorMessage"></div>
                </div>

                <input type="hidden" name="booking_date" :value="selectedDate">
                <input type="hidden" name="booking_time" :value="selectedSlot ? selectedSlot.time : ''">
            </div>

            <div class="space-y-6">
                <div class="bg-gray-800 rounded-2xl border border-gray-700 p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-white">Booking Summary</h3>
                        <span class="text-xs uppercase tracking-wide text-gray-400">Status: Pending</span>
                    </div>

                    <div class="space-y-3">
                        @if($service->image)
                            <img src="{{ '/storage/' . ltrim($service->image ?? '', '/') }}" alt="{{ $service->name }}" class="w-full h-32 object-cover rounded-lg">
                        @endif
                        <div>
                            <h4 class="text-white text-lg font-semibold">{{ $service->name }}</h4>
                            <p class="text-gray-400 text-sm">{{ Str::limit($service->description, 120) }}</p>
                        </div>
                    </div>

                    <div class="space-y-2 text-sm text-gray-400">
                        <div class="flex items-center justify-between">
                            <span>Duration</span>
                            <span class="text-white">{{ $service->duration_minutes }} mins</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>Price</span>
                            <span class="text-white">${{ number_format($service->price, 2) }}</span>
                        </div>
                    </div>

                    <div class="p-4 bg-gray-900 rounded-lg border border-gray-700">
                        <p class="text-xs font-semibold text-gray-300">Selected Slot</p>
                        <p class="text-white text-sm" x-text="selectedSlot ? selectedSlot.label : 'Choose a slot'">Choose a slot</p>
                        <p class="text-xs text-gray-500" x-text="selectedDate ? new Date(selectedDate).toLocaleDateString() : ''"></p>
                    </div>

                    <a :href="calendarLink"
                       target="_blank"
                       rel="noopener"
                       class="inline-flex items-center justify-center w-full px-4 py-2 rounded-lg text-white bg-green-600 hover:bg-green-500 transition"
                       :class="{ 'opacity-50 pointer-events-none': !selectedSlot }">
                        Add to calendar
                    </a>

                    <button type="submit"
                            class="w-full px-4 py-3 rounded-lg font-semibold text-white transition"
                            :class="{ 'bg-indigo-600 hover:bg-indigo-500': selectedSlot, 'bg-gray-700 text-gray-400 cursor-not-allowed': !selectedSlot }"
                            :disabled="!selectedSlot">
                        Confirm Booking
                    </button>

                    <p class="text-xs text-gray-400">Cancellation policy: You can cancel up to 24 hours before the appointment.</p>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function bookingSlotPicker(initialSlots, defaultDate, defaultSlot, slotsUrl, calendarUrl) {
        return {
            slots: initialSlots || {},
            selectedDate: defaultDate || Object.keys(initialSlots || {})[0] || null,
            selectedSlot: defaultSlot,
            errorMessage: '',
            loadingDate: null,
            slotsUrl,
            calendarUrl,

            init() {
                if (!this.selectedSlot && this.selectedDate && this.slots[this.selectedDate]) {
                    const slot = this.slots[this.selectedDate].slots.find(s => s.is_selectable);
                    if (slot) {
                        this.selectSlot(this.selectedDate, slot);
                    }
                }
            },

            selectSlot(date, slot) {
                if (!slot?.is_selectable) {
                    this.errorMessage = 'This slot can no longer be selected.';
                    return;
                }

                this.selectedDate = date;
                this.selectedSlot = slot;
                this.errorMessage = '';
            },

            async refreshSlots(date) {
                if (!date) {
                    return;
                }

                this.loadingDate = date;
                this.errorMessage = '';

                try {
                    const response = await fetch(`${this.slotsUrl}?date=${date}`);
                    const data = await response.json();
                    this.slots = {
                        ...this.slots,
                        [date]: {
                            date_label: data.date_label || date,
                            is_today: (data.date || date) === new Date().toISOString().slice(0, 10),
                            slots: data.available_slots || [],
                        },
                    };

                    if (!this.selectedSlot || this.selectedDate !== date) {
                        const slot = (this.slots[date]?.slots || []).find(s => s.is_selectable);
                        if (slot) {
                            this.selectSlot(date, slot);
                        }
                    }
                } catch (error) {
                    this.errorMessage = 'Unable to refresh slots right now.';
                } finally {
                    this.loadingDate = null;
                }
            },

            get calendarLink() {
                if (!this.selectedSlot || !this.selectedDate) {
                    return '#';
                }
                return `${this.calendarUrl}?date=${this.selectedDate}&time=${this.selectedSlot.time}`;
            },
        };
    }
</script>
@endsection

