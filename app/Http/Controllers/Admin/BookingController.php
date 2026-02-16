<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\SmsService;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $query = Booking::with(['user', 'salonService']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('booking_number', 'like', '%'.$request->search.'%')
                    ->orWhereHas('user', function ($q) use ($request) {
                        $q->where('name', 'like', '%'.$request->search.'%')
                            ->orWhere('email', 'like', '%'.$request->search.'%');
                    });
            });
        }

        $bookings = $query->latest()->paginate(20);

        return view('admin.bookings.index', compact('bookings'));
    }

    public function show(Booking $booking)
    {
        $booking->load(['user', 'salonService.user']);

        return view('admin.bookings.show', compact('booking'));
    }

    public function updateStatus(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,completed,cancelled',
            'staff_notes' => 'nullable|string',
        ]);

        $booking->update([
            'status' => $validated['status'],
            'staff_notes' => $validated['staff_notes'],
            'completed_at' => $validated['status'] === 'completed' ? now() : $booking->completed_at,
            'cancelled_at' => $validated['status'] === 'cancelled' ? now() : $booking->cancelled_at,
        ]);

        if ($validated['status'] === 'confirmed' && $booking->user->phone) {
            $smsService = app(SmsService::class);
            $smsService->sendBookingConfirmation(
                $booking->user->phone,
                $booking->salonService->name,
                $booking->booking_date->format('M j, Y g:i A')
            );
        }

        return redirect()->back()->with('success', 'Booking status updated successfully');
    }
}
