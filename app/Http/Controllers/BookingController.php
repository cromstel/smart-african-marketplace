<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\SalonService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookingController extends Controller
{
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

        return view('bookings.create', compact('service', 'existingBookings'));
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

        DB::beginTransaction();

        try {
            $booking = Booking::create([
                'booking_number' => 'BK-'.strtoupper(Str::random(10)),
                'user_id' => auth()->id(),
                'salon_service_id' => $service->id,
                'booking_date' => $validated['booking_date'],
                'booking_time' => $validated['booking_time'],
                'duration_minutes' => $service->duration_minutes,
                'price' => $service->price,
                'status' => 'pending',
                'customer_notes' => $validated['customer_notes'],
            ]);

            DB::commit();

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

        $booking->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Booking cancelled successfully');
    }

    public function getAvailableSlots(Request $request, SalonService $service)
    {
        $date = $request->query('date', now()->toDateString());

        $existingBookings = Booking::where('salon_service_id', $service->id)
            ->where('booking_date', $date)
            ->where('status', '!=', 'cancelled')
            ->pluck('booking_time')
            ->toArray();

        $workingHours = ['09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00'];

        $availableSlots = array_diff($workingHours, $existingBookings);

        return response()->json(['available_slots' => array_values($availableSlots)]);
    }
}
