<?php

namespace App\Http\Controllers;

use App\Events\BookingStatusChanged;
use App\Models\Booking;
use App\Models\SalonService;
use App\Services\BookingSlotService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    public function __construct(
        protected BookingSlotService $slotService
    ) {}

    public function index()
    {
        // Eager-loads both salonService and resource -- a booking has
        // exactly one or the other (see ResourceBookingController), so
        // loading both unconditionally is harmless (null relation, no
        // query fired for the side that doesn't apply) and keeps this
        // query correct regardless of which type a given row is.
        $bookings = Booking::where('user_id', auth()->id())
            ->with(['salonService.user', 'resource.vendor'])
            ->latest()
            ->paginate(20);

        return view('bookings.index', compact('bookings'));
    }

    public function show(Booking $booking)
    {
        if ($booking->user_id !== auth()->id()) {
            abort(403);
        }

        $booking->load(['salonService.user', 'resource.vendor']);

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

        // Rescheduling is a time-slot concept (generateSlots() below is
        // typed to a SalonService, and would fatal on a null one) --
        // rental bookings are date-range based and have no equivalent
        // page. Cancel-and-rebook is the path for those.
        if (! $booking->salon_service_id) {
            return redirect()->route('bookings.show', $booking)
                ->with('error', 'This booking cannot be rescheduled. Cancel it and submit a new request instead.');
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

        // See the matching guard in edit().
        if (! $booking->salon_service_id) {
            return redirect()->route('bookings.show', $booking)
                ->with('error', 'This booking cannot be rescheduled. Cancel it and submit a new request instead.');
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
        // Matches CartController's is_active guard on the equivalent
        // commit-to-purchase step for products: the show/create pages stay
        // viewable for a deactivated service (consistent with how product
        // pages work), but actually creating a booking against one a vendor
        // has turned off was previously unguarded here.
        if (! $service->is_active) {
            return redirect()->back()->with('error', 'This service is no longer available for booking.');
        }

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
                // customer_notes is nullable -- $request->validate() omits the
                // key entirely from $validated when the field wasn't submitted
                // at all (rather than setting it to null), so accessing it
                // directly threw "Undefined array key" whenever a booking was
                // created without notes. update() below already guards this
                // the same way; store() didn't.
                'customer_notes' => $validated['customer_notes'] ?? null,
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
            'PRODID:-//'.config('app.name').' Booking//EN',
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
}
