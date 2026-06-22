@props([
    'vendor' => null,
    'bookingTypes' => [],
    'selectedType' => null,
    'selectedOption' => null,
    'availableSlots' => [],
    'calculatedPrice' => null,
    'config' => [],
])

@php
    $isRental = $selectedType === 'rental';
    $isClass = $selectedType === 'class';
    $isEvent = $selectedType === 'event';
    $isReservation = $selectedType === 'reservation';
    $isAppointment = $selectedType === 'appointment';
    $isConsultation = $selectedType === 'consultation';
    
    // Determine pricing type labels
    $durationLabel = match($selectedType) {
        'rental' => 'Rental Period',
        'class' => 'Class Duration',
        'event' => 'Event Duration',
        default => 'Duration'
    };
    
    $priceLabel = match($selectedType) {
        'rental' => 'Rental Fee',
        'class' => 'Class Fee',
        'event' => 'Event Fee',
        'reservation' => 'Reservation Fee',
        'consultation' => 'Consultation Fee',
        default => 'Service Fee'
    };
@endphp

<div 
    x-data="multiTypeBookingForm({
        bookingTypes: {{ json_encode($bookingTypes) }},
        selectedType: '{{ $selectedType ?? '' }}',
        selectedOption: {{ json_encode($selectedOption) }},
        slots: {{ json_encode($availableSlots) }},
        price: {{ json_encode($calculatedPrice) }},
        routes: {
            types: '{{ route('api.bookings.types') }}',
            options: '{{ route('api.bookings.options') }}',
            slots: '{{ route('api.bookings.slots') }}',
            price: '{{ route('api.bookings.price') }}',
        }
    })"
    x-init="init()"
    x-cloak
    class="booking-form"
>
    {{-- Step 1: Select Booking Type --}}
    <div x-show="step === 1" class="space-y-6">
        <div class="bg-gray-800 rounded-2xl border border-gray-700 p-6">
            <h3 class="text-xl font-semibold text-white mb-4">What would you like to book?</h3>
            
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <template x-for="type in bookingTypes" :key="type.id">
                    <button 
                        type="button"
                        class="booking-type-card p-4 rounded-xl border-2 transition-all text-left"
                        :class="{
                            'border-indigo-500 bg-indigo-600/20': selectedType === type.id,
                            'border-gray-600 bg-gray-700 hover:border-gray-500': selectedType !== type.id,
                            'opacity-50 cursor-not-allowed': !type.is_available
                        }"
                        :disabled="!type.is_available"
                        @click="selectType(type)"
                    >
                        <div class="flex items-center gap-3">
                            <span x-text="type.icon" class="text-2xl"></span>
                            <div>
                                <p class="font-semibold text-white" x-text="type.name"></p>
                                <p class="text-xs text-gray-400" x-text="type.description"></p>
                            </div>
                        </div>
                    </button>
                </template>
            </div>
        </div>
        
        <div class="flex justify-end">
            <button 
                type="button"
                class="px-6 py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold rounded-lg transition"
                :disabled="!selectedType"
                :class="{ 'opacity-50 cursor-not-allowed': !selectedType }"
                @click="nextStep()"
            >
                Continue →
            </button>
        </div>
    </div>

    {{-- Step 2: Select Service/Resource --}}
    <div x-show="step === 2" class="space-y-6">
        <div class="bg-gray-800 rounded-2xl border border-gray-700 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-semibold text-white">
                    <span x-text="getTypeName()"></span> Options
                </h3>
                <button type="button" @click="step = 1" class="text-gray-400 hover:text-white">
                    ← Back
                </button>
            </div>
            
            <div x-show="loadingOptions" class="text-center py-8">
                <p class="text-gray-400">Loading options...</p>
            </div>
            
            <div x-show="!loadingOptions && options.length" class="space-y-3">
                <template x-for="option in options" :key="option.id">
                    <button 
                        type="button"
                        class="w-full p-4 rounded-xl border-2 transition-all text-left"
                        :class="{
                            'border-indigo-500 bg-indigo-600/20': selectedOption?.id === option.id,
                            'border-gray-600 bg-gray-700 hover:border-gray-500': selectedOption?.id !== option.id
                        }"
                        @click="selectOption(option)"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <p class="font-semibold text-white" x-text="option.name"></p>
                                <p class="text-sm text-gray-400" x-text="option.description"></p>
                                <div class="flex gap-4 mt-2 text-xs text-gray-500">
                                    <span x-show="option.duration_minutes">
                                        <i class="fas fa-clock"></i> <span x-text="option.duration_minutes + ' min'"></span>
                                    </span>
                                    <span x-show="option.duration_hours">
                                        <i class="fas fa-clock"></i> <span x-text="option.duration_hours + ' hours'"></span>
                                    </span>
                                    <span x-show="option.capacity">
                                        <i class="fas fa-users"></i> <span x-text="option.capacity + ' spots'"></span>
                                    </span>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-bold text-white" x-text="formatPrice(option.price)"></p>
                                <p class="text-xs text-gray-400" x-text="option.price_type"></p>
                            </div>
                        </div>
                    </button>
                </template>
            </div>
            
            <div x-show="!loadingOptions && !options.length" class="text-center py-8">
                <p class="text-gray-400">No options available for this booking type.</p>
            </div>
        </div>
        
        <div class="flex justify-end">
            <button 
                type="button"
                class="px-6 py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold rounded-lg transition"
                :disabled="!selectedOption"
                :class="{ 'opacity-50 cursor-not-allowed': !selectedOption }"
                @click="nextStep()"
            >
                Continue →
            </button>
        </div>
    </div>

    {{-- Step 3: Select Date & Time --}}
    <div x-show="step === 3" class="space-y-6">
        <div class="bg-gray-800 rounded-2xl border border-gray-700 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-semibold text-white">Select Date & Time</h3>
                <button type="button" @click="step = 2" class="text-gray-400 hover:text-white">
                    ← Back
                </button>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Date Selection --}}
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Select Date</label>
                    <input 
                        type="date" 
                        x-model="selectedDate"
                        :min="minDate"
                        class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        @change="loadSlots()"
                    >
                </div>
                
                {{-- Time Slots --}}
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Available Times</label>
                    <div x-show="loadingSlots" class="text-gray-400 py-4 text-center">
                        Loading available times...
                    </div>
                    
                    <div x-show="!loadingSlots && availableSlots.length" class="grid grid-cols-3 gap-2 max-h-64 overflow-y-auto">
                        <template x-for="slot in availableSlots" :key="slot.time">
                            <button 
                                type="button"
                                class="p-2 rounded-lg border text-center transition"
                                :class="{
                                    'border-indigo-500 bg-indigo-600 text-white': selectedSlot?.time === slot.time,
                                    'border-gray-600 bg-gray-700 text-gray-200 hover:border-gray-500': selectedSlot?.time !== slot.time && slot.is_available,
                                    'border-gray-700 bg-gray-800 text-gray-500 cursor-not-allowed': !slot.is_available
                                }"
                                :disabled="!slot.is_available"
                                @click="selectSlot(slot)"
                            >
                                <p class="font-medium" x-text="slot.label"></p>
                                <p class="text-xs" x-text="slot.is_available ? slot.status : 'Unavailable'"></p>
                            </button>
                        </template>
                    </div>
                    
                    <div x-show="!loadingSlots && !availableSlots.length" class="text-gray-400 py-4 text-center">
                        No slots available for this date.
                    </div>
                </div>
            </div>
            
            {{-- Rental-specific: Date Range --}}
            <div x-show="{{ $isRental ? 'true' : 'false' }}" class="mt-6 pt-6 border-t border-gray-700">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Pick-up Date</label>
                        <input type="date" x-model="rentalStartDate" :min="minDate" class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Return Date</label>
                        <input type="date" x-model="rentalEndDate" :min="rentalStartDate" class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white">
                    </div>
                </div>
            </div>
            
            {{-- Class/Event-specific: Attendees --}}
            <div x-show="{{ $isClass .' || ' . $isEvent }}" class="mt-6 pt-6 border-t border-gray-700">
                <label class="block text-sm font-medium text-gray-300 mb-2">Number of Guests</label>
                <input 
                    type="number" 
                    x-model="attendees" 
                    :min="1"
                    :max="selectedOption?.capacity || 100"
                    class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white"
                >
                <p class="text-xs text-gray-400 mt-1">Maximum: <span x-text="selectedOption?.capacity || 100"></span> guests</p>
            </div>
        </div>
        
        <div class="flex justify-end">
            <button 
                type="button"
                class="px-6 py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold rounded-lg transition"
                :disabled="!canProceedToSummary()"
                :class="{ 'opacity-50 cursor-not-allowed': !canProceedToSummary() }"
                @click="nextStep()"
            >
                Continue →
            </button>
        </div>
    </div>

    {{-- Step 4: Booking Summary --}}
    <div x-show="step === 4" class="space-y-6">
        <div class="bg-gray-800 rounded-2xl border border-gray-700 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-semibold text-white">Booking Summary</h3>
                <button type="button" @click="step = 3" class="text-gray-400 hover:text-white">
                    ← Back
                </button>
            </div>
            
            <div class="space-y-4">
                {{-- Booking Type --}}
                <div class="flex justify-between items-center py-3 border-b border-gray-700">
                    <span class="text-gray-400">Booking Type</span>
                    <span class="text-white font-medium" x-text="getTypeName()"></span>
                </div>
                
                {{-- Service/Resource --}}
                <div class="flex justify-between items-center py-3 border-b border-gray-700">
                    <span class="text-gray-400">Service</span>
                    <span class="text-white font-medium" x-text="selectedOption?.name"></span>
                </div>
                
                {{-- Date & Time --}}
                <div class="flex justify-between items-center py-3 border-b border-gray-700">
                    <span class="text-gray-400">Date & Time</span>
                    <span class="text-white font-medium">
                        <template x-if="selectedDate && selectedSlot">
                            <span x-text="formatDate(selectedDate) + ' at ' + selectedSlot.label"></span>
                        </template>
                        <template x-if="rentalStartDate && rentalEndDate">
                            <span x-text="formatDate(rentalStartDate) + ' - ' + formatDate(rentalEndDate)"></span>
                        </template>
                    </span>
                </div>
                
                {{-- Attendees (for classes/events) --}}
                <div x-show="{{ $isClass .' || ' . $isEvent }}" class="flex justify-between items-center py-3 border-b border-gray-700">
                    <span class="text-gray-400">Guests</span>
                    <span class="text-white font-medium" x-text="attendees"></span>
                </div>
                
                {{-- Pricing Breakdown --}}
                <div class="py-3 space-y-2">
                    <p class="text-gray-400 font-medium">Pricing</p>
                    <div x-show="priceBreakdown" class="space-y-2">
                        <template x-for="item in priceBreakdown" :key="item.label">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500" x-text="item.label"></span>
                                <span class="text-white" x-text="formatPrice(item.amount)"></span>
                            </div>
                        </template>
                    </div>
                    <div class="flex justify-between pt-2 border-t border-gray-700">
                        <span class="text-white font-semibold">Total</span>
                        <span class="text-indigo-400 font-bold text-xl" x-text="formatPrice(totalPrice)"></span>
                    </div>
                </div>
            </div>
            
            {{-- Additional Notes --}}
            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-300 mb-2">Special Requests (Optional)</label>
                <textarea 
                    x-model="notes" 
                    rows="3"
                    class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="Any special requests or requirements..."
                ></textarea>
            </div>
        </div>
        
        <div class="flex justify-between">
            <button 
                type="button"
                @click="step = 3"
                class="px-6 py-3 bg-gray-700 hover:bg-gray-600 text-white font-semibold rounded-lg transition"
            >
                ← Back
            </button>
            <button 
                type="submit"
                class="px-8 py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold rounded-lg transition"
                :disabled="submitting"
                :class="{ 'opacity-50': submitting }"
            >
                <span x-show="!submitting">Confirm Booking</span>
                <span x-show="submitting">Processing...</span>
            </button>
        </div>
    </div>
    
    {{-- Step Indicators --}}
    <div class="flex items-center justify-center mt-8 gap-2">
        <template x-for="i in 4" :key="i">
            <div 
                class="w-3 h-3 rounded-full transition-colors"
                :class="step >= i ? 'bg-indigo-500' : 'bg-gray-600'"
            ></div>
        </template>
    </div>
</div>

<script>
function multiTypeBookingForm(config) {
    return {
        step: 1,
        bookingTypes: config.bookingTypes || [],
        selectedType: config.selectedType || null,
        selectedTypeData: null,
        options: [],
        selectedOption: config.selectedOption || null,
        availableSlots: config.slots || [],
        selectedDate: null,
        selectedSlot: null,
        rentalStartDate: null,
        rentalEndDate: null,
        attendees: 1,
        notes: '',
        priceBreakdown: [],
        totalPrice: 0,
        loadingOptions: false,
        loadingSlots: false,
        submitting: false,
        routes: config.routes,
        
        get minDate() {
            const today = new Date();
            return today.toISOString().split('T')[0];
        },
        
        init() {
            if (this.selectedType && this.bookingTypes.length === 0) {
                this.loadBookingTypes();
            }
        },
        
        async loadBookingTypes() {
            try {
                const response = await fetch(`${this.routes.types}?vendor_id={{ $vendor?->id }}`);
                const data = await response.json();
                this.bookingTypes = data.types || [];
            } catch (error) {
                console.error('Failed to load booking types:', error);
            }
        },
        
        selectType(type) {
            if (!type.is_available) return;
            this.selectedType = type.id;
            this.selectedTypeData = type;
            this.selectedOption = null;
            this.options = [];
            this.loadOptions();
        },
        
        getTypeName() {
            if (this.selectedTypeData) {
                return this.selectedTypeData.name;
            }
            const type = this.bookingTypes.find(t => t.id === this.selectedType);
            return type?.name || '';
        },
        
        async loadOptions() {
            this.loadingOptions = true;
            try {
                const response = await fetch(`${this.routes.options}?type=${this.selectedType}&vendor_id={{ $vendor?->id }}`);
                const data = await response.json();
                this.options = data.options || [];
            } catch (error) {
                console.error('Failed to load options:', error);
            } finally {
                this.loadingOptions = false;
            }
        },
        
        selectOption(option) {
            this.selectedOption = option;
            this.loadPrice();
        },
        
        async loadSlots() {
            if (!this.selectedDate) return;
            this.loadingSlots = true;
            try {
                const response = await fetch(
                    `${this.routes.slots}?type=${this.selectedType}&option_id=${this.selectedOption?.id}&date=${this.selectedDate}`
                );
                const data = await response.json();
                this.availableSlots = data.slots || [];
            } catch (error) {
                console.error('Failed to load slots:', error);
            } finally {
                this.loadingSlots = false;
            }
        },
        
        selectSlot(slot) {
            if (!slot.is_available) return;
            this.selectedSlot = slot;
            this.loadPrice();
        },
        
        async loadPrice() {
            try {
                const params = new URLSearchParams({
                    type: this.selectedType,
                    option_id: this.selectedOption?.id,
                    date: this.selectedDate,
                    time: this.selectedSlot?.time,
                    rental_start: this.rentalStartDate,
                    rental_end: this.rentalEndDate,
                    attendees: this.attendees
                });
                
                const response = await fetch(`${this.routes.price}?${params}`);
                const data = await response.json();
                this.priceBreakdown = data.breakdown || [];
                this.totalPrice = data.total || 0;
            } catch (error) {
                console.error('Failed to load price:', error);
            }
        },
        
        nextStep() {
            this.step++;
            if (this.step === 3 && !this.selectedDate) {
                this.selectedDate = this.minDate;
                this.loadSlots();
            }
            if (this.step === 4) {
                this.loadPrice();
            }
        },
        
        canProceedToSummary() {
            if (this.selectedType === 'rental') {
                return this.selectedOption && this.rentalStartDate && this.rentalEndDate;
            }
            return this.selectedOption && this.selectedDate && this.selectedSlot;
        },
        
        formatPrice(amount) {
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: '{{ config('currency.code', 'USD') }}'
            }).format(amount || 0);
        },
        
        formatDate(dateStr) {
            if (!dateStr) return '';
            return new Date(dateStr).toLocaleDateString('en-US', {
                weekday: 'short',
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            });
        }
    };
}
</script>

