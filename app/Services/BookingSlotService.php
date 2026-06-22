<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingResource;
use App\Models\BookingType;
use App\Models\SalonService;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class BookingSlotService
{
    protected $slotWindowDays = 7;

    /**
     * Generate slots for service-based booking (appointments, consultations)
     */
    public function generateSlots(
        SalonService $service,
        ?Carbon $startDate = null,
        ?int $days = null,
        ?int $ignoreBookingId = null
    ): array {
        return $this->generateServiceSlots($service, $startDate, $days, $ignoreBookingId);
    }

    /**
     * Generate slots for a specific booking type
     */
    public function generateTypeSlots(
        int $vendorId,
        string $bookingType,
        ?Carbon $startDate = null,
        ?int $days = null,
        ?int $serviceId = null,
        ?int $resourceId = null
    ): array {
        $startDate = $startDate ?? Carbon::now();
        $days = $days ?? $this->slotWindowDays;

        $bookingTypeModel = BookingType::where('slug', $bookingType)->first();

        if (! $bookingTypeModel) {
            return [];
        }

        return match ($bookingType) {
            'rental' => $this->generateRentalSlots($vendorId, $resourceId, $startDate, $days),
            'reservation' => $this->generateReservationSlots($vendorId, $serviceId, $startDate, $days),
            'class' => $this->generateClassSlots($vendorId, $serviceId, $startDate, $days),
            'event' => $this->generateEventSlots($vendorId, $serviceId, $startDate, $days),
            default => $this->generateServiceSlots(
                $serviceId ? SalonService::find($serviceId) : null,
                $startDate,
                $days
            ),
        };
    }

    /**
     * Generate slots for service-based bookings (appointment, consultation)
     */
    protected function generateServiceSlots(
        ?SalonService $service,
        ?Carbon $startDate = null,
        ?int $days = null,
        ?int $ignoreBookingId = null
    ): array {
        $startDate = $startDate ?? Carbon::now();
        $days = $days ?? $this->slotWindowDays;

        $vendor = $service?->user?->vendor;
        $availabilities = $vendor ? $vendor->availabilities()->active()->get()->groupBy('day_of_week') : collect();

        $endDate = $startDate->copy()->addDays($days - 1);

        $bookings = Booking::where('salon_service_id', $service?->id)
            ->whereBetween('booking_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->where('status', '!=', 'cancelled')
            ->when($ignoreBookingId, function ($query) use ($ignoreBookingId) {
                $query->where('id', '!=', $ignoreBookingId);
            })
            ->get()
            ->groupBy(fn (Booking $booking) => $booking->booking_date->toDateString());

        $slots = [];

        for ($day = 0; $day < $days; $day++) {
            $current = $startDate->copy()->addDays($day);
            $dayOfWeek = (int) $current->dayOfWeekIso;
            $dayAvailabilities = $availabilities->get($dayOfWeek, collect());

            if ($dayAvailabilities->isEmpty()) {
                $dayAvailabilities = collect([(object) [
                    'start_time' => '09:00:00',
                    'end_time' => '17:00:00',
                    'slot_interval_minutes' => 60,
                    'min_lead_time_minutes' => 60,
                    'buffer_minutes' => 0,
                    'is_active' => true,
                ]]);
            }

            $rowSlots = [];
            $dayBookings = $bookings->get($current->toDateString(), collect());

            foreach ($dayAvailabilities as $availability) {
                $slotInterval = $availability->slot_interval_minutes ?: 30;
                $minLead = $availability->min_lead_time_minutes ?: 60;

                $slotTime = Carbon::parse($current->toDateString().' '.$availability->start_time);
                $endTime = Carbon::parse($current->toDateString().' '.$availability->end_time);
                $minLeadTime = Carbon::now()->copy()->addMinutes($minLead);

                while ($slotTime->lessThan($endTime)) {
                    $slotKey = $slotTime->format('H:i');
                    $slotStatus = $this->determineStatus($dayBookings, $slotKey);

                    $isPastLead = $slotTime->lessThan($minLeadTime);
                    $isSelectable = $slotStatus === 'available' && ! $isPastLead;

                    $rowSlots[] = [
                        'time' => $slotKey,
                        'label' => $slotTime->format('g:i A'),
                        'status' => $slotStatus,
                        'is_selectable' => $isSelectable,
                        'lead_time_minutes' => $minLead,
                        'buffer_minutes' => $availability->buffer_minutes ?? 0,
                    ];

                    $slotTime = $slotTime->copy()->addMinutes($slotInterval);
                }
            }

            if ($rowSlots) {
                $slots[$current->toDateString()] = [
                    'date_label' => $current->format('l, M j'),
                    'is_today' => $current->isToday(),
                    'slots' => $rowSlots,
                ];
            }
        }

        return $slots;
    }

    /**
     * Generate slots for rental bookings
     */
    protected function generateRentalSlots(
        int $vendorId,
        ?int $resourceId,
        Carbon $startDate,
        int $days
    ): array {
        if (! $resourceId) {
            return [];
        }

        $resource = BookingResource::find($resourceId);

        if (! $resource) {
            return [];
        }

        $endDate = $startDate->copy()->addDays($days - 1);

        // Get bookings for this resource
        $bookings = Booking::where('resource_id', $resourceId)
            ->whereBetween('booking_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->whereNotIn('status', ['cancelled'])
            ->get()
            ->groupBy(fn (Booking $booking) => $booking->booking_date->toDateString());

        $slots = [];

        for ($day = 0; $day < $days; $day++) {
            $current = $startDate->copy()->addDays($day);
            $dayBookings = $bookings->get($current->toDateString(), collect());

            // For rentals, show availability status for the whole day
            $isAvailable = $dayBookings->sum('quantity') < $resource->quantity_available;

            $slots[$current->toDateString()] = [
                'date_label' => $current->format('l, M j'),
                'is_today' => $current->isToday(),
                'available_quantity' => $isAvailable ? $resource->quantity_available - $dayBookings->sum('quantity') : 0,
                'is_available' => $isAvailable,
                'is_selectable' => $isAvailable,
                'price_label' => $resource->price_label ?? '$'.$resource->hourly_rate.'/hr',
            ];
        }

        return $slots;
    }

    /**
     * Generate slots for reservation bookings
     */
    protected function generateReservationSlots(
        int $vendorId,
        ?int $serviceId,
        Carbon $startDate,
        int $days
    ): array {
        // Similar to service slots but with different duration options
        return $this->generateServiceSlots(null, $startDate, $days);
    }

    /**
     * Generate slots for class bookings
     */
    protected function generateClassSlots(
        int $vendorId,
        ?int $serviceId,
        Carbon $startDate,
        int $days
    ): array {
        $endDate = $startDate->copy()->addDays($days - 1);

        // Get class schedule bookings
        $bookings = Booking::where('salon_service_id', $serviceId)
            ->whereBetween('booking_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->whereNotIn('status', ['cancelled'])
            ->where('booking_type', 'class')
            ->get()
            ->groupBy(fn (Booking $booking) => $booking->booking_date->toDateString());

        $slots = [];

        for ($day = 0; $day < $days; $day++) {
            $current = $startDate->copy()->addDays($day);
            $dayBookings = $bookings->get($current->toDateString(), collect());

            // For classes, show available spots
            $totalSpots = 20; // Default class size
            $bookedSpots = $dayBookings->sum('guest_count') ?: $dayBookings->count();
            $availableSpots = $totalSpots - $bookedSpots;

            $slots[$current->toDateString()] = [
                'date_label' => $current->format('l, M j'),
                'is_today' => $current->isToday(),
                'available_spots' => $availableSpots,
                'total_spots' => $totalSpots,
                'is_available' => $availableSpots > 0,
                'is_selectable' => $availableSpots > 0,
                'sessions' => $dayBookings->map(fn ($b) => [
                    'time' => $b->booking_time,
                    'label' => $b->booking_time ? Carbon::parse($b->booking_time)->format('g:i A') : null,
                    'spots' => $totalSpots - ($b->guest_count ?? 1),
                ])->toArray(),
            ];
        }

        return $slots;
    }

    /**
     * Generate slots for event bookings
     */
    protected function generateEventSlots(
        int $vendorId,
        ?int $serviceId,
        Carbon $startDate,
        int $days
    ): array {
        $endDate = $startDate->copy()->addDays(min($days, 30)); // Limit to 30 days for events

        // For events, show available dates (multi-day bookings)
        $slots = [];

        for ($day = 0; $day < min($days, 30); $day++) {
            $current = $startDate->copy()->addDays($day);

            $slots[$current->toDateString()] = [
                'date_label' => $current->format('l, M j, Y'),
                'is_today' => $current->isToday(),
                'is_available' => true, // Events can be booked on any day
                'is_selectable' => true,
                'min_duration_hours' => 4,
                'max_duration_hours' => 168,
            ];
        }

        return $slots;
    }

    public function findSlotStatus(array $slots, string $date, string $time): ?string
    {
        return Arr::first($slots[$date]['slots'] ?? [], fn ($item) => $item['time'] === $time)['status'] ?? null;
    }

    public function isSlotAvailable(array $slots, string $date, string $time): bool
    {
        $status = $this->findSlotStatus($slots, $date, $time);

        return $status === 'available';
    }

    public function getFirstSelectableSlot(array $slots): array
    {
        foreach ($slots as $date => $payload) {
            // Check if it's a time-slot format or date-availability format
            if (isset($payload['slots'])) {
                foreach ($payload['slots'] as $slot) {
                    if (! empty($slot['is_selectable'])) {
                        return [$date, $slot];
                    }
                }
            } elseif (! empty($payload['is_selectable'])) {
                return [$date, $payload];
            }
        }

        $firstDate = array_key_first($slots);
        if ($firstDate === null) {
            return [null, null];
        }

        return [$firstDate, $slots[$firstDate]['slots'][0] ?? $slots[$firstDate] ?? null];
    }

    private function determineStatus(Collection $bookings, string $time): string
    {
        $booking = $bookings->first(fn (Booking $booking) => $booking->booking_time === $time);

        if (! $booking) {
            return 'available';
        }

        if ($booking->status === 'pending') {
            return 'tentative';
        }

        return 'booked';
    }
}
