<?php

namespace App\Http\Controllers;

use App\Events\BookingStatusChanged;
use App\Models\Booking;
use App\Models\BookingResource;
use App\Models\SalonService;
use App\Services\BookingPricingService;
use App\Services\BookingResourceService;
use App\Services\BookingService;
use App\Services\BookingSlotService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    public function __construct(
        protected BookingSlotService $slotService,
        protected BookingService $bookingService,
        protected BookingPricingService $pricingService,
        protected BookingResourceService $resourceService
    ) {}

    /**
     * Get available booking types
     */
    public function getBookingTypes()
    {
        $types = $this->bookingService->getAvailableBookingTypes();

        return response()->json([
            'booking_types' => $types->map(fn ($type) => [
                'id' => $type->id,
                'name' => $type->name,
                'slug' => $type->slug,
                'description' => $type->description,
                'icon' => $type->icon,
                'default_duration_minutes' => $type->default_duration_minutes,
            ]),
        ]);
    }

    /**
     * Get services or resources for a booking type
     */
    public function getBookingOptions(Request $request)
    {
        $type = $request->query('type', 'appointment');
        $vendorId = $request->query('vendor_id');

        $options = [];

        switch ($type) {
            case 'rental':
                $options['resources'] = $this->resourceService
                    ->getVendorResources($vendorId)
                    ->map(fn ($r) => [
                        'id' => $r->id,
                        'name' => $r->name,
                        'description' => $r->description,
                        'hourly_rate' => $r->hourly_rate,
                        'daily_rate' => $r->daily_rate,
                        'category' => $r->category,
                    ]);
                break;

            case 'appointment':
            case 'consultation':
                $options['services'] = SalonService::where('user_id', $vendorId)
                    ->where('is_active', true)
                    ->with('category')
                    ->get()
                    ->map(fn ($s) => [
                        'id' => $s->id,
                        'name' => $s->name,
                        'description' => $s->description,
                        'price' => $s->price,
                        'duration_minutes' => $s->duration_minutes,
                        'category' => $s->category?->name,
                    ]);
                break;

            default:
                $options['services'] = SalonService::where('user_id', $vendorId)
                    ->where('is_active', true)
                    ->with('category')
                    ->get()
                    ->map(fn ($s) => [
                        'id' => $s->id,
                        'name' => $s->name,
                        'description' => $s->description,
                        'price' => $s->price,
                        'duration_minutes' => $s->duration_minutes,
                    ]);
        }

        return response()->json($options);
    }

    /**
     * Get available slots for a booking type
     */
    public function getAvailableSlots(Request $request)
    {
        $type = $request->query('type', 'appointment');
        $vendorId = $request->query('vendor_id');
        $serviceId = $request->query('service_id');
        $resourceId = $request->query('resource_id');
        $date = $request->query('date', now()->toDateString());

        $slots = $this->slotService->generateTypeSlots(
            $vendorId,
            $type,
            Carbon::parse($date),
            7,
            $serviceId,
            $resourceId
        );

        $payload = $slots[$date] ?? [
            'date_label' => Carbon::parse($date)->format('l, M j'),
            'is_today' => Carbon::parse($date)->isToday(),
            'slots' => [],
            'is_available' => true,
        ];

        return response()->json([
            'date' => $date,
            'booking_type' => $type,
            'date_label' => $payload['date_label'] ?? Carbon::parse($date)->format('l, M j'),
            'available_slots' => $payload['slots'] ?? [],
            'is_available' => $payload['is_available'] ?? true,
            'available_quantity' => $payload['available_quantity'] ?? null,
            'available_spots' => $payload['available_spots'] ?? null,
        ]);
    }

    /**
     * Calculate booking price
     */
    public function calculatePrice(Request $request)
    {
        $validated = $request->validate([
            'booking_type' => 'required|string',
            'vendor_id' => 'required|integer',
            'service_id' => 'nullable|integer',
            'resource_id' => 'nullable|integer',
            'booking_date' => 'required|date',
            'start_time' => 'nullable',
            'duration_minutes' => 'nullable|integer',
            'quantity' => 'nullable|integer|min:1',
        ]);

        $pricing = $this->pricingService->calculatePricing($validated);

        return response()->json($pricing);
    }

    public function index()
    {
        $bookings = Booking::where('user_id', auth()->id())
            ->with('salonService.user')
            ->latest()
            ->paginate(20);

        return view('bookings.index', compact('bookings'));
    }

    public function show(Booking $booking)
    {
        if ($booking->user_id !== auth()->id()) {
            abort(403);
        }

        $booking->load('salonService.user');

        return view('bookings.show', compact('booking'));
    }

    public function create(SalonService $service)
    {
        $existingBookings = Booking::where('salon_service_id', $service->id)
            ->where('status', 'confirmed')
            ->where('booking_date', '>=', now()->toDateString())
            ->get();

        $availableSlots = $this->slotService->generateSlots($service);
        [$defaultDate, $defaultSlot] = $this->slotService->getFirstSelectableSlot($availableSlots);

        return view('bookings.create', compact(
            'service',
            'existingBookings',
            'availableSlots',
            'defaultDate',
            'defaultSlot'
        ));
    }

    public function edit(Booking $booking)
    {
        if ($booking->user_id !== auth()->id()) {
            abort(403);
        }

        if (in_array($booking->status, ['completed', 'cancelled'], true)) {
            return redirect()->back()->with('error', 'This booking cannot be rescheduled.');
        }

        if (Carbon::parse($booking->booking_date)->isPast()) {
            return redirect()->back()->with('error', 'This booking is in the past and cannot be rescheduled.');
        }

        $service = $booking->salonService;
        $availableSlots = $this->slotService->generateSlots($service, null, null, $booking->id);

        $defaultDate = Carbon::parse($booking->booking_date)->toDateString();
        $defaultSlot = null;
        if (isset($availableSlots[$defaultDate])) {
            foreach ($availableSlots[$defaultDate]['slots'] as $slot) {
                if ($slot['time'] === $booking->booking_time) {
                    $defaultSlot = $slot;
                    break;
                }
            }
        }

        if (! $defaultSlot) {
            [$fallbackDate, $fallbackSlot] = $this->slotService->getFirstSelectableSlot($availableSlots);
            $defaultDate = $fallbackDate ?? $defaultDate;
            $defaultSlot = $fallbackSlot;
        }

        return view('bookings.reschedule', compact(
            'booking',
            'service',
            'availableSlots',
            'defaultDate',
            'defaultSlot'
        ));
    }

    public function update(Request $request, Booking $booking)
    {
        if ($booking->user_id !== auth()->id()) {
            abort(403);
        }

        if (in_array($booking->status, ['completed', 'cancelled'], true)) {
            return redirect()->back()->with('error', 'This booking cannot be rescheduled.');
        }

        $validated = $request->validate([
            'booking_date' => 'required|date|after_or_equal:today',
            'booking_time' => 'required|date_format:H:i',
            'customer_notes' => 'nullable|string|max:500',
        ]);

        $service = $booking->salonService;

        $existingBooking = Booking::where('salon_service_id', $service->id)
            ->where('booking_date', $validated['booking_date'])
            ->where('booking_time', $validated['booking_time'])
            ->where('status', '!=', 'cancelled')
            ->where('id', '!=', $booking->id)
            ->first();

        if ($existingBooking) {
            return redirect()->back()->with('error', 'This time slot is already booked. Please choose another time.');
        }

        $slots = $this->slotService->generateSlots($service, Carbon::parse($validated['booking_date']), 1, $booking->id);

        if (! $this->slotService->isSlotAvailable($slots, $validated['booking_date'], $validated['booking_time'])) {
            return redirect()->back()->with('error', 'Selected slot is no longer available; please choose another slot.');
        }

        $start = Carbon::parse($validated['booking_date'].' '.$validated['booking_time']);
        $end = $start->copy()->addMinutes($service->duration_minutes);

        $oldStatus = $booking->status;

        $booking->update([
            'booking_date' => $validated['booking_date'],
            'booking_time' => $validated['booking_time'],
            'booking_end_date' => $end,
            'duration_minutes' => $service->duration_minutes,
            'price' => $service->price,
            'stylist_id' => $service->stylist_id,
            'status' => 'pending',
            'customer_notes' => $validated['customer_notes'] ?? $booking->customer_notes,
            'cancelled_at' => null,
            'completed_at' => null,
        ]);

        if ($oldStatus !== 'pending') {
            event(new BookingStatusChanged($booking, $oldStatus, 'pending'));
        }

        return redirect()->route('bookings.show', $booking)
            ->with('success', 'Booking rescheduled successfully. We will confirm the new time shortly.');
    }

    public function store(Request $request, SalonService $service)
    {
        $validated = $request->validate([
            'booking_date' => 'required|date|after_or_equal:today',
            'booking_time' => 'required|date_format:H:i',
            'customer_notes' => 'nullable|string|max:500',
        ]);

        $existingBooking = Booking::where('salon_service_id', $service->id)
            ->where('booking_date', $validated['booking_date'])
            ->where('booking_time', $validated['booking_time'])
            ->where('status', '!=', 'cancelled')
            ->first();

        if ($existingBooking) {
            return redirect()->back()->with('error', 'This time slot is already booked. Please choose another time.');
        }

        $slots = $this->slotService->generateSlots($service, Carbon::parse($validated['booking_date']), 1);

        if (! $this->slotService->isSlotAvailable($slots, $validated['booking_date'], $validated['booking_time'])) {
            return redirect()->back()->with('error', 'Selected slot is no longer available; please choose another slot.');
        }

        DB::beginTransaction();

        try {
            $start = Carbon::parse($validated['booking_date'].' '.$validated['booking_time']);
            $end = $start->copy()->addMinutes($service->duration_minutes);

            $booking = Booking::create([
                'booking_number' => 'BK-'.strtoupper(Str::random(10)),
                'user_id' => auth()->id(),
                'salon_service_id' => $service->id,
                'salon_owner_id' => $service->user_id,
                'stylist_id' => $service->stylist_id,
                'booking_date' => $validated['booking_date'],
                'booking_time' => $validated['booking_time'],
                'booking_end_date' => $end,
                'duration_minutes' => $service->duration_minutes,
                'price' => $service->price,
                'status' => 'pending',
                'customer_notes' => $validated['customer_notes'],
            ]);

            DB::commit();

            event(new BookingStatusChanged($booking, 'new', $booking->status));

            return redirect()->route('bookings.show', $booking)
                ->with('success', 'Booking created successfully! We will confirm your booking soon.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Failed to create booking: '.$e->getMessage());
        }
    }

    public function cancel(Booking $booking)
    {
        if ($booking->user_id !== auth()->id()) {
            abort(403);
        }

        if (in_array($booking->status, ['completed', 'cancelled'])) {
            return redirect()->back()->with('error', 'Cannot cancel this booking');
        }

        $oldStatus = $booking->status;
        $booking->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        event(new BookingStatusChanged($booking, $oldStatus, 'cancelled'));

        return redirect()->back()->with('success', 'Booking cancelled successfully');
    }

    public function calendarPreview(Request $request, SalonService $service)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'time' => 'required|date_format:H:i',
        ]);

        $start = Carbon::parse("{$validated['date']} {$validated['time']}");
        $end = $start->copy()->addMinutes($service->duration_minutes);

        $ics = implode("\r\n", [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//African Market Booking//EN',
            'BEGIN:VEVENT',
            'UID:booking-'.uniqid(),
            'DTSTAMP:'.$start->format('Ymd\\THis').'Z',
            'DTSTART:'.$start->format('Ymd\\THis'),
            'DTEND:'.$end->format('Ymd\\THis'),
            'SUMMARY:'.addslashes($service->name),
            'DESCRIPTION:Booking confirmed with '.addslashes($service->user->name ?? 'the provider'),
            'END:VEVENT',
            'END:VCALENDAR',
        ]);

        return response($ics, 200, [
            'Content-Type' => 'text/calendar',
            'Content-Disposition' => 'attachment; filename="booking-preview.ics"',
        ]);
    }

    public function getServiceSlots(Request $request, SalonService $service)
    {
        $date = $request->query('date', now()->toDateString());
        $slots = $this->slotService->generateSlots($service, Carbon::parse($date), 1);
        $payload = $slots[$date] ?? [
            'date_label' => Carbon::parse($date)->format('l, M j'),
            'is_today' => Carbon::parse($date)->isToday(),
            'slots' => [],
        ];

        return response()->json([
            'date' => $date,
            'date_label' => $payload['date_label'],
            'available_slots' => $payload['slots'],
        ]);
    }

    /**
     * Show vendor booking page with all booking types
     */
    public function vendorBooking(\App\Models\Vendor $vendor)
    {
        // Get booking types enabled for this vendor
        $bookingTypes = $this->bookingService->getAvailableBookingTypes();

        // Check if vendor has any booking-enabled services or resources
        $hasServices = SalonService::where('user_id', $vendor->user_id)->where('is_active', true)->exists();
        $hasResources = BookingResource::where('vendor_id', $vendor->user_id)->where('is_active', true)->exists();

        if ($bookingTypes->isEmpty() && ! $hasServices) {
            return redirect()->route('vendors.show', $vendor)
                ->with('info', 'This vendor does not accept bookings at the moment.');
        }

        return view('bookings.vendor-booking', [
            'vendor' => $vendor,
            'bookingTypes' => $bookingTypes,
        ]);
    }
}
