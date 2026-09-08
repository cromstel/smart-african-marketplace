<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class BookingService
{
    /**
     * Create a new booking with all booking types support
     */
    public function createBooking(array $data): Booking
    {
        $bookingType = $data['booking_type'] ?? 'appointment';

        // Generate unique booking number
        $bookingNumber = $this->generateBookingNumber($bookingType);

        // Set default values based on booking type
        $defaults = $this->getDefaultsForType($bookingType);

        $bookingData = array_merge($defaults, [
            'booking_number' => $bookingNumber,
            'booking_type' => $bookingType,
            'booking_type_id' => $data['booking_type_id'] ?? null,
            'status' => $data['status'] ?? 'pending',
            'user_id' => $data['user_id'] ?? null,
            'salon_service_id' => $data['salon_service_id'] ?? null,
            'salon_owner_id' => $data['salon_owner_id'] ?? null,
            'stylist_id' => $data['stylist_id'] ?? null,
            'booking_date' => $data['booking_date'] ?? Carbon::today(),
            'booking_time' => $data['booking_time'] ?? null,
            'start_time' => $data['start_time'] ?? null,
            'end_time' => $data['end_time'] ?? null,
            'end_date' => $data['end_date'] ?? null,
            'duration_minutes' => $data['duration_minutes'] ?? $this->getDefaultDuration($bookingType),
            'quantity' => $data['quantity'] ?? 1,
            'unit_price' => $data['unit_price'] ?? 0,
            'subtotal' => $data['subtotal'] ?? 0,
            'tax_amount' => $data['tax_amount'] ?? 0,
            'discount_amount' => $data['discount_amount'] ?? 0,
            'total_amount' => $data['total_amount'] ?? 0,
            'deposit_amount' => $data['deposit_amount'] ?? 0,
            'deposit_paid' => $data['deposit_paid'] ?? false,
            'payment_status' => $data['payment_status'] ?? 'pending',
            'guest_name' => $data['guest_name'] ?? null,
            'guest_email' => $data['guest_email'] ?? null,
            'guest_phone' => $data['guest_phone'] ?? null,
            'guest_count' => $data['guest_count'] ?? null,
            'resource_id' => $data['resource_id'] ?? null,
            'resource_name' => $data['resource_name'] ?? null,
            'customer_notes' => $data['customer_notes'] ?? null,
            'staff_notes' => $data['staff_notes'] ?? null,
            'custom_fields' => $data['custom_fields'] ?? [],
            'metadata' => $data['metadata'] ?? [],
            'is_recurring' => $data['is_recurring'] ?? false,
            'recurring_interval' => $data['recurring_interval'] ?? null,
            'recurring_count' => $data['recurring_count'] ?? null,
            'parent_booking_id' => $data['parent_booking_id'] ?? null,
        ]);

        $booking = Booking::create($bookingData);

        // Handle recurring bookings
        if ($bookingData['is_recurring'] && $bookingData['recurring_count'] > 1) {
            $this->createRecurringSeries($booking, $bookingData);
        }

        return $booking;
    }

    /**
     * Update an existing booking
     */
    public function updateBooking(Booking $booking, array $data): Booking
    {
        $updateableFields = [
            'booking_date', 'booking_time', 'start_time', 'end_time', 'end_date',
            'duration_minutes', 'quantity', 'unit_price', 'subtotal', 'tax_amount',
            'discount_amount', 'total_amount', 'deposit_amount', 'deposit_paid',
            'payment_status', 'guest_name', 'guest_email', 'guest_phone', 'guest_count',
            'resource_id', 'resource_name', 'customer_notes', 'staff_notes',
            'custom_fields', 'metadata', 'stylist_id', 'booking_type_id',
        ];

        $updateData = array_intersect_key($data, array_flip($updateableFields));

        $booking->update($updateData);

        return $booking->fresh();
    }

    /**
     * Confirm a booking
     */
    public function confirmBooking(Booking $booking): Booking
    {
        $booking->update([
            'status' => 'confirmed',
        ]);

        return $booking->fresh();
    }

    /**
     * Cancel a booking
     */
    public function cancelBooking(Booking $booking, ?string $reason = null): Booking
    {
        $booking->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'staff_notes' => $booking->staff_notes."\nCancellation reason: ".($reason ?? 'Not provided'),
        ]);

        // Release resource if this is a rental
        if ($booking->resource_id) {
            app(BookingResourceService::class)->releaseResource($booking);
        }

        return $booking->fresh();
    }

    /**
     * Complete a booking
     */
    public function completeBooking(Booking $booking): Booking
    {
        $booking->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return $booking->fresh();
    }

    /**
     * Generate a unique booking number
     */
    public function generateBookingNumber(string $type): string
    {
        $prefix = match ($type) {
            'appointment' => 'APT',
            'reservation' => 'RES',
            'rental' => 'RTL',
            'consultation' => 'CNS',
            'class' => 'CLS',
            'event' => 'EVT',
            default => 'BKG',
        };

        $timestamp = now()->format('ymd');
        $random = strtoupper(Str::random(4));

        return "{$prefix}-{$timestamp}-{$random}";
    }

    /**
     * Get default values for a specific booking type
     */
    protected function getDefaultsForType(string $type): array
    {
        $bookingType = BookingType::where('slug', $type)->first();

        if (! $bookingType) {
            return [];
        }

        return [
            'booking_type_id' => $bookingType->id,
            'duration_minutes' => $bookingType->default_duration_minutes,
        ];
    }

    /**
     * Get default duration for a booking type
     */
    protected function getDefaultDuration(string $type): int
    {
        $bookingType = BookingType::where('slug', $type)->first();

        return $bookingType?->default_duration_minutes ?? 60;
    }

    /**
     * Create a series of recurring bookings
     */
    protected function createRecurringSeries(Booking $parent, array $data): void
    {
        $interval = $data['recurring_interval'] ?? 'week';
        $count = $data['recurring_count'] ?? 1;

        $currentDate = Carbon::parse($data['booking_date']);

        for ($i = 1; $i < $count; $i++) {
            $nextDate = match ($interval) {
                'day' => $currentDate->addDay(),
                'week' => $currentDate->addWeek(),
                'biweek' => $currentDate->addWeeks(2),
                'month' => $currentDate->addMonth(),
                default => $currentDate->addWeek(),
            };

            Booking::create([
                'booking_number' => $this->generateBookingNumber($data['booking_type']),
                'booking_type' => $data['booking_type'],
                'booking_type_id' => $data['booking_type_id'],
                'status' => 'pending',
                'user_id' => $data['user_id'],
                'salon_service_id' => $data['salon_service_id'],
                'salon_owner_id' => $data['salon_owner_id'],
                'booking_date' => $nextDate,
                'booking_time' => $data['booking_time'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'duration_minutes' => $data['duration_minutes'],
                'quantity' => $data['quantity'],
                'unit_price' => $data['unit_price'],
                'subtotal' => $data['subtotal'],
                'tax_amount' => $data['tax_amount'],
                'discount_amount' => $data['discount_amount'],
                'total_amount' => $data['total_amount'],
                'guest_name' => $data['guest_name'],
                'guest_email' => $data['guest_email'],
                'guest_phone' => $data['guest_phone'],
                'parent_booking_id' => $parent->id,
                'is_recurring' => true,
                'recurring_interval' => $interval,
            ]);
        }
    }

    /**
     * Get booking statistics for a vendor
     */
    public function getVendorBookingStats(int $vendorId, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $query = Booking::where('salon_owner_id', $vendorId);

        if ($startDate) {
            $query->where('booking_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('booking_date', '<=', $endDate);
        }

        $totalBookings = $query->count();
        $confirmedBookings = (clone $query)->where('status', 'confirmed')->count();
        $completedBookings = (clone $query)->where('status', 'completed')->count();
        $cancelledBookings = (clone $query)->where('status', 'cancelled')->count();
        $pendingBookings = (clone $query)->where('status', 'pending')->count();

        $totalRevenue = (clone $query)->where('status', '!=', 'cancelled')
            ->sum('total_amount');

        $bookingsByType = (clone $query)
            ->selectRaw('booking_type, COUNT(*) as count')
            ->groupBy('booking_type')
            ->pluck('count', 'booking_type')
            ->toArray();

        return [
            'total' => $totalBookings,
            'confirmed' => $confirmedBookings,
            'completed' => $completedBookings,
            'cancelled' => $cancelledBookings,
            'pending' => $pendingBookings,
            'total_revenue' => $totalRevenue,
            'by_type' => $bookingsByType,
        ];
    }

    /**
     * Get upcoming bookings for a user
     */
    public function getUserUpcomingBookings(int $userId, int $limit = 10): Collection
    {
        return Booking::where('user_id', $userId)
            ->where('booking_date', '>=', Carbon::today())
            ->whereNotIn('status', ['cancelled'])
            ->orderBy('booking_date')
            ->orderBy('booking_time')
            ->limit($limit)
            ->get();
    }

    /**
     * Check if a slot is available for booking
     */
    public function isSlotAvailable(
        int $serviceOrResourceId,
        Carbon $date,
        ?string $time,
        ?int $duration = null,
        ?int $excludeBookingId = null
    ): bool {
        $duration = $duration ?? 60;

        $query = Booking::where('booking_date', $date->toDateString())
            ->whereNotIn('status', ['cancelled'])
            ->where(function ($q) use ($serviceOrResourceId) {
                $q->where('salon_service_id', $serviceOrResourceId)
                    ->orWhere('resource_id', $serviceOrResourceId);
            });

        if ($excludeBookingId) {
            $query->where('id', '!=', $excludeBookingId);
        }

        $existingBookings = $query->get();

        if ($time) {
            // Check specific time slot
            $requestedStart = Carbon::parse("{$date->toDateString()} {$time}");
            $requestedEnd = $requestedStart->copy()->addMinutes($duration);

            foreach ($existingBookings as $booking) {
                $bookingStart = Carbon::parse("{$date->toDateString()} {$booking->booking_time}");
                $bookingEnd = $bookingStart->copy()->addMinutes($booking->duration_minutes);

                // Check for overlap
                if ($requestedStart < $bookingEnd && $requestedEnd > $bookingStart) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Get available booking types
     */
    public function getAvailableBookingTypes(): Collection
    {
        return BookingType::where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Get booking type by slug
     */
    public function getBookingTypeBySlug(string $slug): ?BookingType
    {
        return BookingType::where('slug', $slug)->first();
    }
}
