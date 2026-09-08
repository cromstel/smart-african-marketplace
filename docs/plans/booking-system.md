> **Status: Largely implemented.**
>
> [Documentation Hub](../README.md) › Plans › Booking System
>
> The booking system is built: `Booking`, `BookingType`, `BookingResource`,
> `SalonService`, `SalonStylist` and `ServicePricingTier` models, five services
> under `app/Services/`, `BookingController`, and the `booking:send-reminders`
> command. Read this as design rationale; verify details against the code.
>
> Slot integrity has **no automated test coverage** — see
> [Roadmap 2.2](../product/roadmap.md#22-cover-the-money-and-trust-paths-in-this-order).
> A condensed version lives at
> [Booking system summary](booking-system-summary.md).

# Comprehensive Booking System Implementation Plan

## Smart African Marketplace Multi-Service Booking System

**Document Version:** 1.0  
**Date:** 2024  
**Project:** Smart African Marketplace Platform  
**Status:** Architecture Planning  

---

## 1. Executive Summary

This document outlines a comprehensive implementation plan for expanding the current salon-only booking system into a **universal multi-service booking platform** that supports diverse booking types across the Smart African Marketplace. The existing system is limited to salon services; this expansion will enable appointments, reservations, rentals, consultations, classes, and events while maintaining backward compatibility with existing functionality.

---

## 2. Current System Analysis

### 2.1 Existing Components

| Component | Current State | Location |
|-----------|--------------|----------|
| **Models** | Booking, SalonService, SalonServiceCategory, SalonStylist, Salon | `app/Models/` |
| **Controllers** | Customer, Vendor, Admin booking controllers | `app/Http/Controllers/` |
| **Services** | BookingSlotService, SmsService | `app/Services/` |
| **Events** | BookingStatusChanged | `app/Events/` |
| **Listeners** | SendBookingConfirmation, SendBookingCancellation, CreateBookingNotifications | `app/Listeners/` |
| **Mails** | BookingConfirmed, BookingCancelled, BookingReminder | `app/Mail/` |
| **Views** | Customer, Vendor, Admin interfaces | `resources/views/bookings/`, `vendor/bookings/`, `admin/bookings/` |

### 2.2 Current Booking Flow

```
Customer → Browse Services → Select Service → Choose Date/Time → Confirm Booking → Pending Status
                                                                              ↓
                                                            Vendor Confirms/Declines → Confirmed/Cancelled
```

### 2.3 Identified Limitations

1. **Single Service Type** - Only supports salon/beauty services
2. **Fixed Duration** - No multi-day or variable duration support
3. **Basic Slot Selection** - No resource/equipment availability
4. **No Payment Integration** - Bookings are confirmed without payment
5. **No Recurring Bookings** - Single appointment only
6. **Limited Customization** - No dynamic form fields per service type
7. **No Group Bookings** - Single customer per booking
8. **No Deposit System** - Full payment at venue not supported

---

## 3. Proposed Booking Types

### 3.1 Booking Type Definitions

| Type | Description | Example Use Cases | Duration | Capacity |
|------|-------------|-------------------|----------|----------|
| **Appointment** | Professional service booking | Salon, spa, health, legal | 15min - 4hrs | 1-10 |
| **Reservation** | Space/table booking | Restaurant, venue, coworking | 1hr - multiple days | 1-100+ |
| **Rental** | Equipment/vehicle booking | Cars, cameras, party equipment | 1hr - 30 days | 1+ units |
| **Consultation** | Expert advice session | Coaching, mentoring, expert calls | 15min - 2hrs | 1-5 |
| **Class** | Group learning | Workshops, fitness, courses | 30min - 8hrs | 5-50 |
| **Event** | Private celebration/function | Birthday, wedding, corporate | Hours to days | 10-500+ |

### 3.2 Booking Type Configuration

Each booking type requires specific configuration:

```php
// Example booking type config
$bookingTypes = [
    'appointment' => [
        'duration' => 'flexible',      // Variable based on service
        'slots' => 'time-based',       // 15/30/60 min slots
        'staff_required' => true,      // Staff/stylist assignment
        'resources' => ['room', 'equipment'],
        'pricing' => 'per-service',
        'capacity' => 1,
    ],
    'reservation' => [
        'duration' => 'fixed-period',  // Customer selects duration
        'slots' => 'period-based',     // Morning/Afternoon/Evening
        'staff_required' => false,
        'resources' => ['table', 'space'],
        'pricing' => 'per-duration',
        'capacity' => 'variable',
    ],
    'rental' => [
        'duration' => 'flexible',
        'slots' => 'daily/hourly',
        'staff_required' => false,
        'resources' => ['inventory_item'],
        'pricing' => 'per-unit-time',
        'capacity' => 'inventory_count',
    ],
    'consultation' => [
        'duration' => 'fixed',
        'slots' => 'time-based',
        'staff_required' => true,
        'resources' => [],
        'pricing' => 'per-session',
        'capacity' => 1,
    ],
    'class' => [
        'duration' => 'fixed',
        'slots' => 'session-based',
        'staff_required' => true,
        'resources' => ['room', 'materials'],
        'pricing' => 'per-person',
        'capacity' => 'max_participants',
    ],
    'event' => [
        'duration' => 'multi-day',
        'slots' => 'date-range',
        'staff_required' => true,
        'resources' => ['venue', 'equipment', 'staff'],
        'pricing' => 'package-based',
        'capacity' => 'venue_capacity',
    ],
];
```

---

## 4. Database Schema Changes

### 4.1 New Models Required

#### 4.1.1 BookingType Model

```php
// app/Models/BookingType.php
class BookingType extends Model
{
    protected $fillable = [
        'name',              // appointment, reservation, rental, etc.
        'slug',
        'description',
        'icon',
        'default_duration_minutes',
        'min_duration_minutes',
        'max_duration_minutes',
        'booking_window_days',
        'cancellation_policy_hours',
        'is_active',
        'sort_order',
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
        'default_duration_minutes' => 'integer',
        'min_duration_minutes' => 'integer',
        'max_duration_minutes' => 'integer',
        'booking_window_days' => 'integer',
        'cancellation_policy_hours' => 'integer',
    ];
    
    // Relationships
    public function serviceCategories();
    public function services();
    public function bookings();
}
```

#### 4.1.2 ServiceBookingType Pivot

```php
// Many-to-many relationship between SalonService and BookingType
class ServiceBookingType extends Model
{
    protected $fillable = [
        'salon_service_id',
        'booking_type_id',
        'is_default',
    ];
    
    protected $casts = [
        'is_default' => 'boolean',
    ];
}
```

#### 4.1.3 Enhanced Booking Model

Add new fields to existing Booking model:

```php
// Additions to app/Models/Booking.php
protected $fillable = [
    // ... existing fields ...
    
    // New fields
    'booking_type',              // appointment, reservation, rental, etc.
    'service_booking_type_id',  // Pivot ID for service-booking type
    
    // Multi-day/extended bookings
    'end_date',                  // For rentals, multi-day events
    'start_time',
    'end_time',
    
    // Group/quantity bookings
    'quantity',                  // Number of items/people
    'unit_price',                // Price per unit/item
    
    // Guest bookings
    'guest_name',
    'guest_email',
    'guest_phone',
    
    // Payment status
    'payment_status',           // pending, partial, paid, refunded
    'payment_method',
    'deposit_amount',
    'deposit_paid',
    'total_amount',
    
    // Recurring
    'is_recurring',
    'recurring_frequency',      // daily, weekly, monthly
    'recurring_end_date',
    'parent_booking_id',
    
    // Resources
    'resource_id',              // Specific resource (vehicle, equipment)
    'resource_name',
    
    // Confirmation
    'confirmation_method',      // automatic, manual
    'confirmed_at',
    'confirmation_token',
    
    // Cancellation
    'cancellation_reason',
    'cancellation_fee',
    
    // Custom fields JSON
    'custom_fields',            // JSON for type-specific fields
    'metadata',                 // Additional metadata
];

protected $casts = [
    // ... existing casts ...
    'end_date' => 'date',
    'start_time' => 'datetime',
    'end_time' => 'datetime',
    'quantity' => 'integer',
    'unit_price' => 'decimal:2',
    'deposit_amount' => 'decimal:2',
    'deposit_paid' => 'decimal:2',
    'total_amount' => 'decimal:2',
    'is_recurring' => 'boolean',
    'recurring_end_date' => 'date',
    'confirmed_at' => 'datetime',
    'cancellation_fee' => 'decimal:2',
    'custom_fields' => 'array',
    'metadata' => 'array',
];
```

#### 4.1.4 BookingResource Model

```php
// app/Models/BookingResource.php
class BookingResource extends Model
{
    protected $fillable = [
        'vendor_id',
        'resource_type',         // equipment, vehicle, room, vehicle
        'name',
        'description',
        'sku',
        'quantity_total',        // Total available
        'quantity_available',    // Currently available
        'is_active',
        'minimum_rental_hours',
        'pricing_type',          // hourly, daily, fixed
        'hourly_rate',
        'daily_rate',
        'fixed_rate',
    ];
    
    // Relationships
    public function vendor();
    public function bookings();
    public function availability();
}
```

#### 4.1.5 VendorAvailability Model

```php
// app/Models/VendorAvailability.php
class VendorAvailability extends Model
{
    protected $fillable = [
        'vendor_id',
        'day_of_week',           // 0-6 (Sunday-Saturday)
        'start_time',
        'end_time',
        'is_available',
        'break_start',
        'break_end',
    ];
    
    // For specific date overrides
    protected $fillable_date = [
        'vendor_id',
        'specific_date',
        'start_time',
        'end_time',
        'is_available',
        'reason',
    ];
}
```

#### 4.1.6 ServicePricingTier Model

```php
// app/Models/ServicePricingTier.php
class ServicePricingTier extends Model
{
    protected $fillable = [
        'salon_service_id',
        'tier_name',             // Peak, Off-peak, Weekend, etc.
        'day_of_week',           // Applicable day
        'start_time',
        'end_time',
        'price_modifier',        // Percentage or fixed
        'price_modifier_type',   // percentage, fixed
        'minimum_duration',
    ];
}
```

---

## 5. Service Layer Architecture

### 5.1 BookingService Facade

```php
// app/Services/BookingService.php
class BookingService
{
    // Core booking operations
    public function createBooking(array $data): Booking;
    public function updateBooking(Booking $booking, array $data): Booking;
    public function cancelBooking(Booking $booking, ?string $reason = null): Booking;
    public function confirmBooking(Booking $booking): Booking;
    public function rescheduleBooking(Booking $booking, $newDate, $newTime): Booking;
    
    // Availability
    public function getAvailableSlots(Service $service, $date, $bookingType = null): array;
    public function getAvailableResources(Service $service, $date, $duration): array;
    public function checkAvailability($vendorId, $date, $time, $duration, $bookingType): bool;
    
    // Pricing
    public function calculatePrice(Service $service, $date, $time, $duration, $quantity = 1): float;
    public function applyPricingTiers(float $basePrice, $date, $time): float;
    
    // Validation
    public function validateBooking(array $data): ValidationResult;
    public function isSlotAvailable($service, $date, $time, $duration): bool;
    
    // Notifications
    public function sendBookingConfirmation(Booking $booking): void;
    public function sendBookingReminder(Booking $booking): void;
    public function sendBookingCancellation(Booking $booking): void;
    public function sendBookingUpdate(Booking $booking): void;
}
```

### 5.2 BookingSlotService Enhancements

```php
// Enhancements to existing app/Services/BookingSlotService.php

class BookingSlotService
{
    // Generate slots based on booking type
    public function generateSlots(
        Service $service,
        ?Carbon $date = null,
        ?int $days = null,
        ?int $ignoreBookingId = null,
        ?string $bookingType = null
    ): array;
    
    // Time-based slots (existing - for appointments/consultations)
    public function generateTimeSlots(Service $service, Carbon $date): array;
    
    // Period-based slots (new - for reservations)
    public function generatePeriodSlots(Service $service, Carbon $date): array;
    
    // Daily slots (new - for rentals)
    public function generateDailySlots(Service $service, Carbon $startDate, Carbon $endDate): array;
    
    // Date range slots (new - for events)
    public function generateDateRangeSlots(Service $service, Carbon $startDate, Carbon $endDate): array;
    
    // Check resource availability
    public function getResourceAvailability(
        Service $service,
        Carbon $date,
        int $duration,
        ?int $quantity = null
    ): array;
    
    // Vendor availability
    public function getVendorAvailability(int $vendorId, Carbon $date, ?string $bookingType = null): array;
}
```

### 5.3 New: BookingPricingService

```php
// app/Services/BookingPricingService.php
class BookingPricingService
{
    public function calculate(
        Service $service,
        Carbon $date,
        ?Carbon $startTime = null,
        ?Carbon $endTime = null,
        int $quantity = 1,
        ?string $bookingType = null
    ): PricingResult;
    
    public function applyTierPricing(float $basePrice, Service $service, Carbon $date, Carbon $time): float;
    public function applyQuantityDiscount(float $unitPrice, int $quantity): float;
    public function calculateDeposit(float $totalAmount, ?float $depositPercent = null): float;
    public function calculateCancellationFee(Booking $booking): float;
}
```

### 5.4 New: BookingResourceService

```php
// app/Services/BookingResourceService.php
class BookingResourceService
{
    public function getAvailableResources(
        int $vendorId,
        Carbon $date,
        ?Carbon $startTime = null,
        ?Carbon $endTime = null,
        ?string $resourceType = null
    ): Collection;
    
    public function reserveResource(
        int $resourceId,
        Carbon $startDateTime,
        Carbon $endDateTime,
        int $quantity = 1
    ): bool;
    
    public function releaseResource(
        int $resourceId,
        Carbon $startDateTime,
        Carbon $endDateTime
    ): bool;
    
    public function getResourceBookings(
        int $resourceId,
        Carbon $startDate,
        Carbon $endDate
    ): Collection;
}
```

---

## 6. Controller Architecture

### 6.1 Unified BookingController

```php
// app/Http/Controllers/BookingController.php
// Enhanced to handle all booking types

class BookingController extends Controller
{
    // Customer endpoints
    public function index(Request $request);                    // List user's bookings
    public function show(Booking $booking);                    // View booking details
    public function create(Request $request, ?Service $service = null);  // Show booking form
    public function store(Request $request, Service $service); // Create new booking
    public function edit(Booking $booking);                    // Edit/reschedule form
    public function update(Request $request, Booking $booking); // Update/reschedule
    public function cancel(Request $request, Booking $booking); // Cancel booking
    
    // AJAX endpoints
    public function availableSlots(Request $request, Service $service);
    public function calculatePrice(Request $request, Service $service);
    public function checkAvailability(Request $request);
    
    // Guest booking
    public function guestBook(Request $request);
    public function confirmGuestBooking($token);
    
    // Public booking page
    public function publicBooking($vendorSlug, ?Service $service = null);
}
```

### 6.2 Vendor BookingController Enhancements

```php
// app/Http/Controllers/Vendor/BookingController.php

class BookingController extends Controller
{
    public function index(Request $request);
    public function show(Booking $booking);
    public function calendar(Request $request);              // Calendar view
    
    // Management actions
    public function confirm(Request $request, Booking $booking);
    public function decline(Request $request, Booking $booking);
    public function complete(Booking $booking);
    public function markNoShow(Booking $booking);
    
    // Bulk actions
    public function bulkUpdateStatus(Request $request);
    public function export(Request $request);
    
    // Availability
    public function availability();
    public function updateAvailability(Request $request);
    
    // Resources
    public function resources();
    public function manageResource(Request $request, ?BookingResource $resource = null);
}
```

### 6.3 Admin BookingController Enhancements

```php
// app/Http/Controllers/Admin/BookingController.php

class BookingController extends Controller
{
    public function index(Request $request);
    public function show(Booking $booking);
    public function calendar(Request $request);
    public function updateStatus(Request $request, Booking $booking);
    
    // Booking types management
    public function types();                                  // List booking types
    public function createType();                             // Create type form
    public function storeType(Request $request);              // Create type
    public function editType(BookingType $type);              // Edit type form
    public function updateType(Request $request, BookingType $type);
    public function deleteType(BookingType $type);
    
    // Analytics
    public function analytics(Request $request);
    public function reports(Request $request);
    
    // Settings
    public function settings();
    public function updateSettings(Request $request);
}
```

---

## 7. View/Template Architecture

### 7.1 Dynamic Booking Form Components

Create flexible Blade components for different booking types:

```
resources/views/
├── components/
│   ├── booking/
│   │   ├── booking-form.blade.php         # Main booking form wrapper
│   │   ├── date-picker.blade.php         # Date selection
│   │   ├── time-slot-picker.blade.php    # Time-based slots
│   │   ├── period-picker.blade.php       # Period-based (AM/PM/Evening)
│   │   ├── duration-picker.blade.php     # Duration selection
│   │   ├── quantity-selector.blade.php   # For rentals/group bookings
│   │   ├── resource-selector.blade.php   # Resource/equipment selection
│   │   ├── guest-details.blade.php       # Guest booking form
│   │   ├── custom-fields.blade.php        # Dynamic custom fields
│   │   ├── pricing-summary.blade.php     # Price calculation display
│   │   ├── booking-summary.blade.php     # Final booking summary
│   │   └── confirmation-modal.blade.php  # Confirmation dialog
│   │
│   ├── booking-types/
│   │   ├── appointment-form.blade.php
│   │   ├── reservation-form.blade.php
│   │   ├── rental-form.blade.php
│   │   ├── consultation-form.blade.php
│   │   ├── class-form.blade.php
│   │   └── event-form.blade.php
```

### 7.2 Booking Form Component Specifications

#### 7.2.1 Main Booking Form Wrapper

```blade
{{-- resources/views/components/booking/booking-form.blade.php --}}
<div class="booking-form" 
     x-data="bookingForm({
         service: @js($service ?? null),
         bookingType: '{{ $bookingType ?? 'appointment' }}',
         routes: {
             slots: '{{ route('bookings.available-slots', $service ?? 0) }}',
             price: '{{ route('bookings.calculate-price', $service ?? 0) }}',
             store: '{{ route('bookings.store', $service ?? 0) }}',
         }
     })">
    
    {{-- Booking Type Selector (if multiple types available) --}}
    @if($allowBookingTypeSelection)
        @include('components.booking.booking-type-selector')
    @endif
    
    {{-- Dynamic Form Based on Booking Type --}}
    @switch($bookingType)
        @case('appointment')
            @include('components.booking-types.appointment-form')
            @break
        @case('reservation')
            @include('components.booking-types.reservation-form')
            @break
        @case('rental')
            @include('components.booking-types.rental-form')
            @break
        @case('consultation')
            @include('components.booking-types.consultation-form')
            @break
        @case('class')
            @include('components.booking-types.class-form')
            @break
        @case('event')
            @include('components.booking-types.event-form')
            @break
    @endswitch
    
    {{-- Common Elements --}}
    @include('components.booking.pricing-summary')
    @include('components.booking.booking-summary')
</div>
```

#### 7.2.2 Time Slot Picker (Enhanced)

```blade
{{-- resources/views/components/booking/time-slot-picker.blade.php --}}
<div class="time-slot-picker"
     x-data="timeSlotPicker({
         initialSlots: @js($availableSlots),
         selectedDate: '{{ $selectedDate ?? '' }}',
         bookingType: '{{ $bookingType ?? 'appointment' }}',
         slotDuration: {{ $slotDuration ?? 30 }},
         startTime: '{{ $serviceHours['start'] ?? '08:00' }}',
         endTime: '{{ $serviceHours['end'] ?? '20:00' }}',
         breakStart: '{{ $serviceHours['break_start'] ?? '12:00' }}',
         breakEnd: '{{ $serviceHours['break_end'] ?? '13:00' }}',
     })">
    
    <!-- Date Navigation -->
    <div class="date-navigation flex items-center justify-between mb-4">
        <button type="button" @click="previousWeek()" class="p-2 rounded hover:bg-gray-100">
            ←
        </button>
        <span x-text="dateRangeLabel" class="font-medium"></span>
        <button type="button" @click="nextWeek()" class="p-2 rounded hover:bg-gray-100">
            →
        </button>
    </div>
    
    <!-- Date Selection -->
    <div class="date-grid grid grid-cols-7 gap-2 mb-4">
        <template x-for="day in visibleDays" :key="day.date">
            <button type="button"
                    @click="selectDate(day.date)"
                    :class="{
                        'bg-indigo-600 text-white': selectedDate === day.date,
                        'bg-gray-100 text-gray-700 hover:bg-gray-200': selectedDate !== day.date,
                        'opacity-50 cursor-not-allowed': !day.available,
                    }"
                    class="p-2 rounded-lg text-center">
                <div class="text-xs" x-text="day.dayName"></div>
                <div class="text-lg font-bold" x-text="day.dayNumber"></div>
                <div class="text-xs" x-text="day.month"></div>
            </button>
        </template>
    </div>
    
    <!-- Time Slots Grid -->
    <div class="time-slots" x-show="selectedDate">
        <div class="grid grid-cols-4 sm:grid-cols-6 gap-2">
            <template x-for="slot in availableSlots" :key="slot.time">
                <button type="button"
                        @click="selectSlot(slot)"
                        :disabled="!slot.available"
                        :class="{
                            'bg-indigo-600 text-white ring-2 ring-indigo-300': selectedSlot?.time === slot.time,
                            'bg-gray-100 text-gray-700 hover:bg-gray-200': selectedSlot?.time !== slot.time && slot.available,
                            'bg-gray-200 text-gray-400 cursor-not-allowed': !slot.available,
                        }"
                        class="p-2 rounded-lg text-sm font-medium transition">
                    <span x-text="slot.time"></span>
                    <span x-show="!slot.available" class="block text-xs">Booked</span>
                </button>
            </template>
        </div>
        
        <!-- No Slots Available Message -->
        <div x-show="availableSlots.length === 0" class="text-center py-8 text-gray-500">
            <p>No available slots for this date.</p>
            <button type="button" @click="selectDate(nextAvailableDate)" class="text-indigo-600 hover:underline mt-2">
                View next available date
            </button>
        </div>
    </div>
</div>
```

#### 7.2.3 Rental Form (New)

```blade
{{-- resources/views/components/booking-types/rental-form.blade.php --}}
<div class="rental-form" x-data="rentalForm()">
    <!-- Date Range Picker -->
    <div class="mb-6">
        <label class="block text-sm font-medium text-gray-700 mb-2">Rental Period</label>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Start Date</label>
                <input type="date" 
                       x-model="startDate" 
                       @change="calculateTotals()"
                       :min="'{{ now()->toDateString() }}'"
                       class="w-full border rounded-lg px-4 py-2">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">End Date</label>
                <input type="date" 
                       x-model="endDate" 
                       @change="calculateTotals()"
                       :min="startDate"
                       class="w-full border rounded-lg px-4 py-2">
            </div>
        </div>
    </div>
    
    <!-- Quantity Selector -->
    <div class="mb-6">
        <label class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
        <div class="flex items-center gap-4">
            <button type="button" @click="decrementQuantity()" class="p-2 rounded bg-gray-100 hover:bg-gray-200">-</button>
            <span x-text="quantity" class="text-xl font-bold"></span>
            <button type="button" @click="incrementQuantity()" class="p-2 rounded bg-gray-100 hover:bg-gray-200">+</button>
            <span class="text-sm text-gray-500" x-text="availableText"></span>
        </div>
    </div>
    
    <!-- Resource Selection -->
    <div class="mb-6" x-show="showResourceSelection">
        <label class="block text-sm font-medium text-gray-700 mb-2">Select Equipment</label>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            <template x-for="resource in availableResources" :key="resource.id">
                <div @click="selectResource(resource)"
                     :class="{'ring-2 ring-indigo-600': selectedResource?.id === resource.id}"
                     class="border rounded-lg p-4 cursor-pointer hover:border-indigo-400">
                    <img :src="resource.image" class="w-full h-32 object-cover rounded mb-2">
                    <h4 class="font-medium" x-text="resource.name"></h4>
                    <p class="text-sm text-gray-500" x-text="resource.description"></p>
                    <p class="text-indigo-600 font-bold mt-2" x-text="resource.price_label"></p>
                </div>
            </template>
        </div>
    </div>
    
    <!-- Pricing Breakdown -->
    <div class="bg-gray-50 rounded-lg p-4">
        <h4 class="font-medium mb-3">Pricing Breakdown</h4>
        <div class="space-y-2">
            <div class="flex justify-between">
                <span>Daily Rate</span>
                <span x-text="formatCurrency(dailyRate)"></span>
            </div>
            <div class="flex justify-between">
                <span>Number of Days</span>
                <span x-text="numberOfDays"></span>
            </div>
            <div class="flex justify-between">
                <span>Quantity</span>
                <span x-text="quantity"></span>
            </div>
            <template x-if="quantity >= 3">
                <div class="flex justify-between text-green-600">
                    <span>Group Discount (10%)</span>
                    <span x-text="formatCurrency(discount)"></span>
                </div>
            </template>
            <div class="flex justify-between font-bold border-t pt-2">
                <span>Total</span>
                <span x-text="formatCurrency(total)"></span>
            </div>
        </div>
    </div>
</div>
```

---

## 8. Calendar Integration

### 8.1 Enhanced Calendar Views

```
Admin/Vendor Calendar Features:
├── Monthly overview with booking counts
├── Weekly view with time slots
├── Daily view with detailed schedule
├── Resource/room view
├── Staff/stylist schedule view
├── Drag-and-drop rescheduling
├── Availability overlay
└── Export to iCal/Google Calendar
```

### 8.2 Calendar Component

```blade
{{-- resources/views/components/booking/calendar.blade.php --}}
<div class="booking-calendar"
     x-data="bookingCalendar({
         initialBookings: @js($bookings),
         view: '{{ $view ?? 'month' }}',
         routes: {
             events: '{{ route('bookings.calendar-events') }}',
             update: '{{ route('bookings.calendar-update') }}',
         }
     })">
    
    <!-- Calendar Header -->
    <div class="calendar-header flex items-center justify-between mb-6">
        <div class="flex items-center gap-4">
            <button @click="previous()" class="p-2 rounded hover:bg-gray-100">←</button>
            <h2 class="text-xl font-bold" x-text="currentPeriodLabel"></h2>
            <button @click="next()" class="p-2 rounded hover:bg-gray-100">→</button>
        </div>
        <div class="flex gap-2">
            <button @click="today()" class="px-4 py-2 rounded bg-gray-100 hover:bg-gray-200">Today</button>
            <select x-model="view" @change="changeView()" class="border rounded px-4 py-2">
                <option value="month">Month</option>
                <option value="week">Week</option>
                <option value="day">Day</option>
                <option value="resource">Resource</option>
            </select>
        </div>
    </div>
    
    <!-- Month View -->
    <div x-show="view === 'month'" class="month-view">
        <div class="grid grid-cols-7 gap-px bg-gray-200">
            @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
                <div class="bg-gray-50 p-2 text-center font-medium">{{ $day }}</div>
            @endforeach
        </div>
        <div class="grid grid-cols-7 gap-px bg-gray-200">
            <template x-for="day in days" :key="day.date">
                <div :class="{'bg-gray-50': !day.isCurrentMonth, 'bg-white': day.isCurrentMonth}"
                     class="min-h-24 p-2">
                    <div class="flex justify-between items-start">
                        <span :class="{'text-gray-400': !day.isCurrentMonth}"
                              x-text="day.dayNumber"></span>
                        <span x-show="day.bookings.length" 
                              class="text-xs bg-indigo-100 text-indigo-700 px-1 rounded"
                              x-text="day.bookings.length"></span>
                    </div>
                    <div class="mt-1 space-y-1">
                        <template x-for="booking in day.bookings.slice(0, 3)" :key="booking.id">
                            <div @click="viewBooking(booking)"
                                 :class="statusClass(booking.status)"
                                 class="text-xs p-1 rounded truncate cursor-pointer"
                                 x-text="booking.title"></div>
                        </template>
                    </div>
                </div>
            </template>
        </div>
    </div>
    
    <!-- Week/Day View -->
    <div x-show="view === 'week' || view === 'day'" class="time-grid">
        <!-- Time column -->
        <div class="grid" :class="view === 'week' ? 'grid-cols-8' : 'grid-cols-1'">
            <template x-for="hour in hours" :key="hour">
                <div class="border-b border-r p-2 text-sm text-gray-500" x-text="formatHour(hour)"></div>
            </template>
        </div>
        <!-- Day columns -->
        <template x-for="(day, index) in visibleDays" :key="day.date">
            <div class="border-r relative">
                <template x-for="hour in hours" :key="hour">
                    <div class="border-b h-16 relative hover:bg-gray-50 cursor-pointer"
                         @click="createBookingAt(day.date, hour)">
                        <template x-for="booking in getBookingsAt(day.date, hour)" :key="booking.id">
                            <div @click.stop="viewBooking(booking)"
                                 :class="statusClass(booking.status)"
                                 class="absolute inset-x-0 mx-1 p-1 rounded text-xs overflow-hidden"
                                 :style="bookingStyle(booking)">
                                <span x-text="booking.time"></span>
                                <span x-text="booking.title"></span>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </template>
    </div>
</div>
```

---

## 9. Notification System

### 9.1 Booking Notification Types

| Event | Customer Email | Customer SMS | Vendor Email | Vendor SMS | In-App |
|-------|---------------|--------------|--------------|------------|--------|
| Booking Created | ✅ | ✅ | ✅ | ✅ | ✅ |
| Booking Confirmed | ✅ | ✅ | - | - | ✅ |
| Booking Declined | ✅ | ✅ | - | - | ✅ |
| Booking Reminder (24h) | ✅ | ✅ | ✅ | ✅ | ✅ |
| Booking Reminder (1h) | ✅ | - | ✅ | - | ✅ |
| Booking Rescheduled | ✅ | ✅ | ✅ | ✅ | ✅ |
| Booking Cancelled | ✅ | ✅ | ✅ | ✅ | ✅ |
| Booking Completed | ✅ | - | ✅ | - | ✅ |
| Payment Received | ✅ | - | ✅ | - | ✅ |
| Deposit Required | ✅ | ✅ | - | - | ✅ |

### 9.2 Notification Service Extensions

```php
// app/Services/BookingNotificationService.php
class BookingNotificationService
{
    public function sendBookingCreated(Booking $booking): void;
    public function sendBookingConfirmed(Booking $booking): void;
    public function sendBookingDeclined(Booking $booking): void;
    public function sendBookingReminder(Booking $booking, int $hoursBefore): void;
    public function sendBookingRescheduled(Booking $booking, $oldDate, $oldTime): void;
    public function sendBookingCancelled(Booking $booking): void;
    public function sendBookingCompleted(Booking $booking): void;
    public function sendDepositRequest(Booking $booking): void;
    public function sendPaymentConfirmation(Booking $booking): void;
    
    // Bulk notifications
    public function sendBulkReminders(): int;  // Returns count sent
    
    // Template management
    public function getTemplate(string $type, string $channel): ?NotificationTemplate;
    public function renderTemplate(NotificationTemplate $template, array $data): string;
}
```

---

## 10. Payment Integration

### 10.1 Payment Flow Options

```
Booking Payment Options:
├── Pay Later (at venue)
├── Pay Now (full)
├── Deposit Only (percentage)
└── Installments (if enabled)
```

### 10.2 Booking Payment Service

```php
// app/Services/BookingPaymentService.php
class BookingPaymentService
{
    // Payment processing
    public function processPayment(Booking $booking, PaymentData $data): PaymentResult;
    public function processDeposit(Booking $booking, PaymentData $data): PaymentResult;
    public function processFullPayment(Booking $booking, PaymentData $data): PaymentResult;
    public function processInstallment(Booking $booking, PaymentData $data): PaymentResult;
    public function processRefund(Booking $booking, float $amount): RefundResult;
    
    // Payment status
    public function getPaymentStatus(Booking $booking): string;
    public function calculateRefundAmount(Booking $booking): float;
    public function calculateCancellationFee(Booking $booking): float;
    
    // Deposit calculations
    public function calculateDeposit(float $totalAmount, ?float $percent = null): float;
    public function calculateRemaining(float $totalAmount, float $depositPaid): float;
}
```

---

## 11. Admin Management Interface

### 11.1 Booking Types Management

```
Admin Booking Types CRUD:
├── List All Booking Types
│   ├── Active/Inactive toggle
│   ├── Sort order management
│   └── Quick actions (edit, delete)
├── Create Booking Type
│   ├── Name, slug, description
│   ├── Default/min/max duration
│   ├── Booking window configuration
│   ├── Cancellation policy
│   └── Icon selection
├── Edit Booking Type
├── Delete Booking Type (with confirmation)
└── View Services using Type
```

### 11.2 Admin Booking Dashboard

```blade
{{-- resources/views/admin/bookings/dashboard.blade.php --}}
<div class="booking-dashboard">
    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-gray-500 text-sm">Total Bookings</h3>
            <p class="text-3xl font-bold">{{ $stats['total'] }}</p>
            <p class="text-green-600 text-sm">+{{ $stats['total_change'] }}%</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-gray-500 text-sm">Pending</h3>
            <p class="text-3xl font-bold text-yellow-600">{{ $stats['pending'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-gray-500 text-sm">Confirmed</h3>
            <p class="text-3xl font-bold text-green-600">{{ $stats['confirmed'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-gray-500 text-sm">Revenue</h3>
            <p class="text-3xl font-bold text-indigo-600">${{ number_format($stats['revenue']) }}</p>
        </div>
    </div>
    
    <!-- Booking by Type Chart -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <h3 class="text-lg font-bold mb-4">Bookings by Type</h3>
        <canvas id="bookingsByTypeChart" height="300"></canvas>
    </div>
    
    <!-- Recent Activity -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-bold mb-4">Recent Bookings</h3>
        <table class="w-full">
            <thead>
                <tr class="border-b">
                    <th class="text-left py-3">Booking</th>
                    <th class="text-left py-3">Customer</th>
                    <th class="text-left py-3">Type</th>
                    <th class="text-left py-3">Date</th>
                    <th class="text-left py-3">Status</th>
                    <th class="text-left py-3">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentBookings as $booking)
                <tr class="border-b hover:bg-gray-50">
                    <td class="py-3">
                        <a href="{{ route('admin.bookings.show', $booking) }}" class="font-medium text-indigo-600">
                            {{ $booking->booking_number }}
                        </a>
                    </td>
                    <td class="py-3">{{ $booking->user->name }}</td>
                    <td class="py-3">
                        <span class="px-2 py-1 rounded text-xs bg-gray-100">
                            {{ $booking->booking_type }}
                        </span>
                    </td>
                    <td class="py-3">{{ $booking->booking_date->format('M d, Y') }}</td>
                    <td class="py-3">
                        <span class="px-2 py-1 rounded text-xs {{ $statusClass($booking->status) }}">
                            {{ $booking->status }}
                        </span>
                    </td>
                    <td class="py-3">${{ number_format($booking->total_amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
```

---

## 12. Vendor Dashboard Interface

### 12.1 Enhanced Vendor Booking Views

```
Vendor Booking Management:
├── Booking Calendar
│   ├── Monthly/Weekly/Daily views
│   ├── Staff schedule view
│   └── Resource availability view
├── Booking List
│   ├── Filter by status, date, service
│   ├── Search by customer/booking number
│   └── Bulk actions
├── Booking Details
│   ├── Customer information
│   ├── Service details
│   ├── Payment status
│   ├── Action buttons (confirm, decline, complete)
│   └── Communication (SMS, email)
├── Availability Settings
│   ├── Weekly schedule
│   ├── Specific date overrides
│   ├── Break times
│   └── Holiday closures
├── Resource Management
│   ├── Add/Edit/Delete resources
│   ├── Inventory tracking
│   └── Availability calendar
├── Pricing Tiers
│   ├── Peak/Off-peak pricing
│   ├── Weekend/Premium pricing
│   └── Duration-based pricing
└── Analytics
    ├── Booking trends
    ├── Revenue reports
    └── Popular services
```

### 12.2 Vendor Availability Settings

```blade
{{-- resources/views/vendor/bookings/availability.blade.php --}}
<div class="availability-settings" x-data="availabilitySettings()">
    <!-- Weekly Schedule -->
    <div class="weekly-schedule mb-8">
        <h3 class="text-lg font-bold mb-4">Weekly Schedule</h3>
        @foreach(['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'] as $index => $day)
        <div class="day-row flex items-center gap-4 mb-4 p-4 bg-gray-50 rounded-lg">
            <div class="w-24 font-medium">{{ $day }}</div>
            <div class="flex items-center gap-2">
                <input type="checkbox" 
                       x-model="schedule[{{ $index }}].is_available"
                       class="rounded text-indigo-600">
                <span class="text-sm text-gray-600">Available</span>
            </div>
            <template x-if="schedule[{{ $index }}].is_available">
                <div class="flex items-center gap-2">
                    <input type="time" 
                           x-model="schedule[{{ $index }}].start_time"
                           class="border rounded px-2 py-1">
                    <span>to</span>
                    <input type="time" 
                           x-model="schedule[{{ $index }}].end_time"
                           class="border rounded px-2 py-1">
                </div>
            </template>
            <template x-if="schedule[{{ $index }}].is_available && schedule[{{ $index }}].has_break">
                <div class="flex items-center gap-2 ml-4">
                    <span class="text-sm text-gray-500">Break:</span>
                    <input type="time" x-model="schedule[{{ $index }}].break_start" class="border rounded px-2 py-1">
                    <span>to</span>
                    <input type="time" x-model="schedule[{{ $index }}].break_end" class="border rounded px-2 py-1">
                </div>
            </template>
        </div>
        @endforeach
    </div>
    
    <!-- Date-Specific Overrides -->
    <div class="overrides mb-8">
        <h3 class="text-lg font-bold mb-4">Special Hours / Closures</h3>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-2">Date</th>
                        <th class="text-left py-2">Status</th>
                        <th class="text-left py-2">Hours</th>
                        <th class="text-left py-2">Reason</th>
                        <th class="text-right py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($overrides as $override)
                    <tr class="border-b">
                        <td class="py-2">{{ $override->specific_date->format('M d, Y') }}</td>
                        <td class="py-2">
                            <span :class="$override->is_available ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" 
                                  class="px-2 py-1 rounded text-xs">
                                {{ $override->is_available ? 'Open' : 'Closed' }}
                            </span>
                        </td>
                        <td class="py-2">
                            {{ $override->start_time }} - {{ $override->end_time }}
                        </td>
                        <td class="py-2">{{ $override->reason ?? '-' }}</td>
                        <td class="py-2 text-right">
                            <button class="text-red-600 hover:text-red-800">Delete</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-4 text-center text-gray-500">No special hours configured</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <button @click="addOverride()" class="mt-4 px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
            Add Special Hours
        </button>
    </div>
</div>
```

---

## 13. Customer Booking Experience

### 13.1 Unified Booking Flow

```mermaid
graph TD
    A[Browse Services] --> B{Service Type?}
    B -->|Single Service| C[Select Service]
    B -->|Multiple Types| D[Select Booking Type]
    D --> C
    C --> E[View Service Details]
    E --> F[Click Book Now]
    F --> G{Booking Type}
    G -->|Appointment| H[Select Date & Time]
    G -->|Reservation| I[Select Date & Duration]
    G -->|Rental| J[Select Date Range & Quantity]
    G -->|Consultation| K[Select Date & Time]
    G -->|Class| L[Select Session & Participants]
    G -->|Event| M[Select Date Range & Details]
    H --> N[Select Staff/Stylist (optional)]
    I --> N
    J --> O[Select Specific Item (optional)]
    K --> N
    L --> P[Review Participants]
    M --> N
    N --> Q[View Booking Summary]
    O --> Q
    P --> Q
    Q --> R{Full Payment Required?}
    R -->|Yes| S[Complete Payment]
    R -->|No| T[Confirm Booking]
    S --> T
    T --> U[Booking Confirmation]
    U --> V[Receive Confirmation Email/SMS]
```

### 13.2 My Bookings Page

```blade
{{-- resources/views/bookings/index.blade.php --}}
<div class="my-bookings">
    <!-- Filter Tabs -->
    <div class="tabs flex gap-2 mb-6 border-b">
        <a href="{{ route('bookings.index', ['status' => 'all']) }}" 
           class="px-4 py-2 {{ request('status', 'all') === 'all' ? 'border-b-2 border-indigo-600 text-indigo-600' : 'text-gray-500' }}">
            All
        </a>
        <a href="{{ route('bookings.index', ['status' => 'pending']) }}" 
           class="px-4 py-2 {{ request('status') === 'pending' ? 'border-b-2 border-indigo-600 text-indigo-600' : 'text-gray-500' }}">
            Pending
        </a>
        <a href="{{ route('bookings.index', ['status' => 'confirmed']) }}" 
           class="px-4 py-2 {{ request('status') === 'confirmed' ? 'border-b-2 border-indigo-600 text-indigo-600' : 'text-gray-500' }}">
            Confirmed
        </a>
        <a href="{{ route('bookings.index', ['status' => 'completed']) }}" 
           class="px-4 py-2 {{ request('status') === 'completed' ? 'border-b-2 border-indigo-600 text-indigo-600' : 'text-gray-500' }}">
            Completed
        </a>
        <a href="{{ route('bookings.index', ['status' => 'cancelled']) }}" 
           class="px-4 py-2 {{ request('status') === 'cancelled' ? 'border-b-2 border-indigo-600 text-indigo-600' : 'text-gray-500' }}">
            Cancelled
        </a>
    </div>
    
    <!-- Booking Cards -->
    <div class="space-y-4">
        @forelse($bookings as $booking)
        <div class="booking-card bg-white rounded-lg shadow hover:shadow-lg transition p-6">
            <div class="flex flex-col md:flex-row gap-4">
                <!-- Service Image -->
                <div class="w-full md:w-48">
                    @if($booking->salonService?->image)
                        <img src="{{ $booking->salonService->image }}" 
                             alt="{{ $booking->salonService->name }}"
                             class="w-full h-32 object-cover rounded-lg">
                    @else
                        <div class="w-full h-32 bg-gray-100 rounded-lg flex items-center justify-center">
                            <span class="text-4xl">📅</span>
                        </div>
                    @endif
                </div>
                
                <!-- Booking Details -->
                <div class="flex-1">
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="px-2 py-1 text-xs rounded 
                                {{ $booking->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $booking->status === 'confirmed' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $booking->status === 'completed' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $booking->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}">
                                {{ ucfirst($booking->booking_type) }} - {{ ucfirst($booking->status) }}
                            </span>
                            <h3 class="text-lg font-bold mt-2">
                                {{ $booking->salonService->name ?? 'Service' }}
                            </h3>
                            <p class="text-sm text-gray-500">#{{ $booking->booking_number }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-2xl font-bold text-indigo-600">
                                ${{ number_format($booking->total_amount ?? $booking->price, 2) }}
                            </p>
                        </div>
                    </div>
                    
                    <!-- Date/Time Info -->
                    <div class="mt-4 flex flex-wrap gap-4 text-sm">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span>{{ $booking->booking_date->format('l, F d, Y') }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>
                                {{ $booking->booking_time ? \Carbon\Carbon::parse($booking->booking_time)->format('g:i A') : '' }}
                                @if($booking->end_time)
                                    - {{ \Carbon\Carbon::parse($booking->end_time)->format('g:i A') }}
                                @endif
                            </span>
                        </div>
                        @if($booking->quantity > 1)
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span>{{ $booking->quantity }} people/items</span>
                        </div>
                        @endif
                    </div>
                    
                    <!-- Actions -->
                    <div class="mt-4 flex gap-2">
                        <a href="{{ route('bookings.show', $booking) }}" 
                           class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded text-sm">
                            View Details
                        </a>
                        @if(in_array($booking->status, ['pending', 'confirmed']) && $booking->booking_date->isFuture())
                            <a href="{{ route('bookings.reschedule', $booking) }}" 
                               class="px-4 py-2 bg-indigo-100 hover:bg-indigo-200 text-indigo-700 rounded text-sm">
                                Reschedule
                            </a>
                            <form action="{{ route('bookings.cancel', $booking) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-4 py-2 bg-red-100 hover:bg-red-200 text-red-700 rounded text-sm"
                                        onclick="return confirm('Are you sure?')">
                                    Cancel
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-12">
            <div class="text-6xl mb-4">📅</div>
            <h3 class="text-xl font-bold text-gray-700">No bookings yet</h3>
            <p class="text-gray-500 mt-2">Start by browsing our services</p>
            <a href="{{ route('salons.index') }}" class="inline-block mt-4 px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                Browse Services
            </a>
        </div>
        @endforelse
    </div>
    
    <!-- Pagination -->
    @if($bookings->hasPages())
    <div class="mt-8">
        {{ $bookings->links() }}
    </div>
    @endif
</div>
```

---

## 14. Responsive Design (Including 912x1368)

### 14.1 Mobile Booking Experience

The booking system must work seamlessly on all devices including 912x1368 (Surface Pro 7) which should be treated as mobile:

```css
/* Tailwind responsive breakpoints */
/* Default (mobile): 0-639px */
/* sm: 640px */
/* md: 768px */
/* lg: 1024px - Primary mobile/desktop cutoff */
/* xl: 1280px */
/* 2xl: 1536px */

/* For 912x1368 viewport - should trigger lg (mobile) styles */
@screen lg {
    /* Mobile navigation and layout */
}
```

### 14.2 Booking Form Mobile Optimizations

```blade
{{-- Mobile-optimized booking form --}}
<div class="booking-form-mobile">
    <!-- Sticky bottom action bar for mobile -->
    <div class="fixed bottom-0 left-0 right-0 bg-white border-t shadow-lg p-4 lg:relative lg:p-0 lg:bg-transparent lg:border-0 lg:shadow-none">
        <div class="flex gap-2">
            <button @click="scrollToSummary()" class="flex-1 bg-gray-100 hover:bg-gray-200 py-3 rounded-lg lg:hidden">
                View Summary
            </button>
            <button type="submit" 
                    :disabled="!isValid"
                    class="flex-1 bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white py-3 rounded-lg font-medium">
                <span x-show="requiresPayment">Pay ${{ $total }}</span>
                <span x-show="!requiresPayment">Confirm Booking</span>
            </button>
        </div>
    </div>
    
    <!-- Collapsible sections for mobile -->
    <div class="space-y-4 lg:space-y-6">
        <div x-data="{ expanded: true }" class="border rounded-lg overflow-hidden">
            <button @click="expanded = !expanded" class="w-full flex items-center justify-between p-4 bg-gray-50 lg:bg-transparent">
                <span class="font-medium">1. Select {{ ucfirst($bookingType) }} Details</span>
                <svg class="w-5 h-5 transform transition lg:hidden" :class="{'rotate-180': expanded}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="expanded" class="p-4">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
```

---

## 15. Implementation Phases

### Phase 1: Foundation (Week 1-2)
- [ ] Database migrations for new models
- [ ] Create BookingType model and seeder
- [ ] Update Booking model with new fields
- [ ] Create BookingResource model
- [ ] Create VendorAvailability model

### Phase 2: Core Services (Week 3-4)
- [ ] Enhance BookingService
- [ ] Create BookingPricingService
- [ ] Create BookingResourceService
- [ ] Update BookingSlotService for multiple types

### Phase 3: Controllers & Routes (Week 5-6)
- [ ] Update BookingController
- [ ] Add new vendor routes
- [ ] Add new admin routes
- [ ] Implement AJAX endpoints

### Phase 4: Frontend Views (Week 7-10)
- [ ] Create booking form components
- [ ] Implement type-specific forms
- [ ] Update calendar views
- [ ] Mobile optimization

### Phase 5: Notifications & Payments (Week 11-12)
- [ ] Enhance notification service
- [ ] Add payment integration
- [ ] Add deposit system
- [ ] Email/SMS templates

### Phase 6: Testing & Refinement (Week 13-14)
- [ ] Integration testing
- [ ] Performance optimization
- [ ] Bug fixes
- [ ] Documentation

---

## 16. Acceptance Criteria

### Functional Requirements
- [ ] Users can create bookings for all 6 booking types
- [ ] Vendors can manage availability for all types
- [ ] Admins can CRUD booking types
- [ ] Calendar views work for all booking types
- [ ] Notifications are sent for all booking events
- [ ] Payment integration works for required bookings
- [ ] Responsive design works on 912x1368 viewport

### Non-Functional Requirements
- [ ] Page load time < 2 seconds
- [ ] Booking form submission < 1 second
- [ ] Calendar renders smoothly with 1000+ bookings
- [ ] All forms have proper validation
- [ ] Error handling provides helpful messages
- [ ] All text is properly localized-ready

---

## 17. Dependencies & Requirements

### Laravel/PHP
- Laravel 10+
- PHP 8.2+
- Composer packages:
  - `spatie/laravel-permission` (roles/permissions)
  - `laravel/slack-notification-channel` (notifications)
  - `propaganistas/laravel-phone` (phone validation)
  - `nesbot/carbon` (date handling)

### Frontend
- Tailwind CSS 3+
- Alpine.js 3+
- FullCalendar (calendar UI) or custom calendar
- date-fns (date utilities)

### External Services
- SMS provider (existing: SMS service)
- Email provider (existing: Mail service)
- Payment gateway (Stripe/Paystack - existing)

---

## 18. Backward Compatibility

The implementation must maintain full backward compatibility with existing booking functionality:

1. **Existing Bookings**: All existing bookings continue to work
2. **Default Booking Type**: Existing services default to 'appointment' type
3. **API Stability**: Existing API endpoints continue to work
4. **Database**: Existing booking records maintain their structure
5. **Views**: Existing views remain functional (with type defaults)

```php
// Default booking type for existing services
$defaultBookingType = $service->booking_type ?? 'appointment';
```

---

*Document prepared for Smart African Marketplace Booking System Implementation*
