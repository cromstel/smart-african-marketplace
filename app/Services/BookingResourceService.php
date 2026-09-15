<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingResource;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class BookingResourceService
{
    /**
     * Create a new booking resource.
     *
     * This used to write a field set ('type', 'slug', 'weekly_rate',
     * 'min_rental_hours', 'category', 'requires_deposit', 'metadata', ...)
     * that does not match BookingResource's actual columns
     * ('resource_type', 'quantity_total', 'minimum_rental_hours',
     * 'pricing_type', no slug/weekly_rate/category/deposit/metadata at
     * all). Mass assignment silently dropped every mismatched field, and
     * since the required, defaultless `resource_type` column was never
     * among them, every create() threw a SQL error -- this feature could
     * never actually create a resource. Rewritten to match the real
     * schema.
     */
    public function createResource(array $data): BookingResource
    {
        return BookingResource::create([
            'vendor_id' => $data['vendor_id'],
            'resource_type' => $data['resource_type'] ?? 'equipment',
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'sku' => $data['sku'] ?? null,
            'quantity_total' => $data['quantity_total'] ?? ($data['quantity_available'] ?? 1),
            'quantity_available' => $data['quantity_available'] ?? ($data['quantity_total'] ?? 1),
            'is_active' => $data['is_active'] ?? true,
            'minimum_rental_hours' => $data['minimum_rental_hours'] ?? 1,
            'pricing_type' => $data['pricing_type'] ?? 'hourly',
            'hourly_rate' => $data['hourly_rate'] ?? 0,
            'daily_rate' => $data['daily_rate'] ?? 0,
            'fixed_rate' => $data['fixed_rate'] ?? 0,
            'image' => $data['image'] ?? null,
            'specifications' => $data['specifications'] ?? null,
        ]);
    }

    /**
     * Update a booking resource. See createResource() for why this field
     * list was rewritten to match BookingResource's actual columns.
     */
    public function updateResource(BookingResource $resource, array $data): BookingResource
    {
        $updateableFields = [
            'name', 'description', 'resource_type', 'sku',
            'quantity_total', 'quantity_available', 'is_active',
            'minimum_rental_hours', 'pricing_type',
            'hourly_rate', 'daily_rate', 'fixed_rate',
            'image', 'specifications',
        ];

        $updateData = array_intersect_key($data, array_flip($updateableFields));

        $resource->update($updateData);

        return $resource->fresh();
    }

    /**
     * Get available resources for a vendor
     */
    public function getVendorResources(int $vendorId): Collection
    {
        return BookingResource::where('vendor_id', $vendorId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * Check availability of a resource for a given time period
     */
    public function isResourceAvailable(
        int $resourceId,
        Carbon $startDate,
        Carbon $endDate,
        ?int $excludeBookingId = null
    ): bool {
        $resource = BookingResource::findOrFail($resourceId);

        $bookedQuantity = $this->getBookedQuantity(
            $resourceId,
            $startDate,
            $endDate,
            $excludeBookingId
        );

        return $bookedQuantity < $resource->quantity_available;
    }

    /**
     * Get the quantity of resource already booked
     */
    public function getBookedQuantity(
        int $resourceId,
        Carbon $startDate,
        Carbon $endDate,
        ?int $excludeBookingId = null
    ): int {
        $query = Booking::where('resource_id', $resourceId)
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->where(function ($q) use ($startDate, $endDate) {
                $q->where(function ($q2) use ($startDate, $endDate) {
                    // Overlapping date ranges
                    $q2->where('booking_date', '<=', $endDate->toDateString())
                        ->where(function ($q3) use ($startDate) {
                            $q3->where('end_date', '>=', $startDate->toDateString())
                                ->orWhereNull('end_date');
                        });
                });
            });

        if ($excludeBookingId) {
            $query->where('id', '!=', $excludeBookingId);
        }

        return $query->sum('quantity');
    }

    /**
     * Get available quantity for a resource
     */
    public function getAvailableQuantity(
        int $resourceId,
        Carbon $startDate,
        Carbon $endDate,
        ?int $excludeBookingId = null
    ): int {
        $resource = BookingResource::findOrFail($resourceId);
        $booked = $this->getBookedQuantity($resourceId, $startDate, $endDate, $excludeBookingId);

        return max(0, $resource->quantity_available - $booked);
    }

    /**
     * Get all bookings for a resource within a date range
     */
    public function getResourceBookings(
        int $resourceId,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null
    ): Collection {
        $query = Booking::where('resource_id', $resourceId)
            ->whereNotIn('status', ['cancelled']);

        if ($startDate) {
            $query->where('booking_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('booking_date', '<=', $endDate);
        }

        return $query->orderBy('booking_date')->get();
    }

    /**
     * Get unavailable dates for a resource
     */
    public function getUnavailableDates(
        int $resourceId,
        Carbon $startDate,
        Carbon $endDate
    ): array {
        $bookings = $this->getResourceBookings($resourceId, $startDate, $endDate);

        $unavailableDates = [];

        foreach ($bookings as $booking) {
            $bookingStart = Carbon::parse($booking->booking_date);
            $bookingEnd = $booking->end_date
                ? Carbon::parse($booking->end_date)
                : $bookingStart->copy()->addDays(1);

            $current = $bookingStart->copy();
            while ($current->lt($bookingEnd)) {
                $unavailableDates[] = $current->toDateString();
                $current->addDay();
            }
        }

        return array_unique($unavailableDates);
    }

    /**
     * Get time slots available for a resource on a given date
     */
    public function getAvailableTimeSlots(
        int $resourceId,
        Carbon $date,
        int $durationHours = 1
    ): array {
        $resource = BookingResource::findOrFail($resourceId);

        // Get bookings for this resource on this date
        $bookings = Booking::where('resource_id', $resourceId)
            ->where('booking_date', $date->toDateString())
            ->whereNotIn('status', ['cancelled'])
            ->orderBy('start_time')
            ->get();

        // Generate available slots (assuming 8 AM - 10 PM availability)
        $slots = [];
        $startHour = 8;
        $endHour = 22;

        for ($hour = $startHour; $hour <= $endHour - $durationHours; $hour++) {
            $slotStart = $date->copy()->setHour($hour)->setMinute(0);
            $slotEnd = $slotStart->copy()->addHours($durationHours);

            // Check if slot conflicts with existing bookings
            $isAvailable = true;
            foreach ($bookings as $booking) {
                if ($booking->start_time && $booking->end_time) {
                    $bookingStart = Carbon::parse("{$date->toDateString()} {$booking->start_time}");
                    $bookingEnd = Carbon::parse("{$date->toDateString()} {$booking->end_time}");

                    // Check overlap
                    if ($slotStart->lt($bookingEnd) && $slotEnd->gt($bookingStart)) {
                        $isAvailable = false;
                        break;
                    }
                }
            }

            if ($isAvailable) {
                $slots[] = [
                    'start' => $slotStart->format('H:i'),
                    'end' => $slotEnd->format('H:i'),
                    'label' => $slotStart->format('g:i A').' - '.$slotEnd->format('g:i A'),
                ];
            }
        }

        return $slots;
    }

    /**
     * Calculate rental price for a resource.
     *
     * Previously branched on `monthly_rate`/`weekly_rate` and a
     * `requires_deposit`/`deposit_amount` pair, none of which exist on
     * BookingResource (see createResource() above) -- those columns are
     * always null/undefined, so the monthly/weekly branches could never
     * fire and the "deposit" was always zero. This is currently unused
     * (no controller calls it), but rewritten to match the model's real
     * `pricing_type` discriminator (hourly/daily/fixed, the same one
     * BookingResource::calculatePrice() uses) so it isn't dead-wrong if
     * something wires it up later.
     */
    public function calculateRentalPrice(
        BookingResource $resource,
        int $durationMinutes
    ): array {
        $hours = (int) max(1, ceil($durationMinutes / 60));
        $days = (int) max(1, ceil($hours / 24));

        $price = match ($resource->pricing_type) {
            'daily' => (float) $resource->daily_rate * $days,
            'fixed' => (float) $resource->fixed_rate,
            default => (float) $resource->hourly_rate * $hours,
        };

        return [
            'price' => round($price, 2),
            'rate_type' => $resource->pricing_type ?? 'hourly',
            'hours' => $hours,
            'total' => round($price, 2),
        ];
    }

    /**
     * Release a resource when booking is cancelled
     */
    public function releaseResource(Booking $booking): void
    {
        // Resources are automatically released since we store quantity
        // This method can be used for additional cleanup if needed
        if ($booking->resource) {
            // Log resource release for analytics
            \Log::info("Resource {$booking->resource_id} released for booking {$booking->booking_number}");
        }
    }

    /**
     * Reserve a resource for a booking
     */
    public function reserveResource(Booking $booking): bool
    {
        $resource = $booking->resource;

        if (! $resource) {
            return false;
        }

        $startDate = Carbon::parse($booking->booking_date);
        $endDate = $booking->end_date
            ? Carbon::parse($booking->end_date)
            : $startDate->copy()->addMinutes($booking->duration_minutes);

        if (! $this->isResourceAvailable($resource->id, $startDate, $endDate, $booking->id)) {
            return false;
        }

        return true;
    }

    /**
     * Get resource utilization statistics
     */
    public function getResourceUtilizationStats(
        int $resourceId,
        Carbon $startDate,
        Carbon $endDate
    ): array {
        $resource = BookingResource::findOrFail($resourceId);

        $bookings = $this->getResourceBookings($resourceId, $startDate, $endDate);

        $totalDays = $startDate->diffInDays($endDate) + 1;
        $bookedDays = $bookings->count();

        $totalRevenue = $bookings->sum('total_amount');

        return [
            'resource_id' => $resourceId,
            'resource_name' => $resource->name,
            'period_start' => $startDate->toDateString(),
            'period_end' => $endDate->toDateString(),
            'total_days' => $totalDays,
            'booked_days' => $bookedDays,
            'utilization_rate' => $totalDays > 0 ? round(($bookedDays / $totalDays) * 100, 2) : 0,
            'total_revenue' => $totalRevenue,
            'booking_count' => $bookings->count(),
        ];
    }
}
