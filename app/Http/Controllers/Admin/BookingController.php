<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\BookingReminder;
use App\Models\Booking;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $query = Booking::with(['user', 'salonService.user']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('vendor')) {
            $vendorId = $request->vendor;
            $query->whereHas('salonService', function ($q) use ($vendorId) {
                $q->where('user_id', $vendorId);
            });
        }

        if ($request->filled('service')) {
            $query->where('salon_service_id', $request->service);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('booking_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('booking_date', '<=', $request->date_to);
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

        $bookings = $query->latest()->paginate(20)->withQueryString();

        $vendors = \App\Models\User::where('role', 'vendor')->orderBy('name')->get();
        $services = \App\Models\SalonService::orderBy('name')->get();

        return view('admin.bookings.index', compact('bookings', 'vendors', 'services'));
    }

    public function show(Booking $booking)
    {
        $booking->load(['user', 'salonService.user']);

        return view('admin.bookings.show', compact('booking'));
    }

    public function calendar(Request $request)
    {
        $month = $request->query('month', now()->format('Y-m'));
        $startOfMonth = \Carbon\Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        $bookings = Booking::with(['user', 'salonService'])
            ->whereBetween('booking_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->get()
            ->groupBy(fn (Booking $booking) => $booking->booking_date->toDateString());

        return view('admin.bookings.calendar', [
            'month' => $startOfMonth,
            'bookingsByDate' => $bookings,
        ]);
    }

    public function updateStatus(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,completed,cancelled',
            'staff_notes' => 'nullable|string',
        ]);

        $oldStatus = $booking->status;
        $booking->update([
            'status' => $validated['status'],
            'staff_notes' => $validated['staff_notes'],
            'completed_at' => $validated['status'] === 'completed' ? now() : $booking->completed_at,
            'cancelled_at' => $validated['status'] === 'cancelled' ? now() : $booking->cancelled_at,
        ]);

        if ($oldStatus !== $validated['status']) {
            event(new \App\Events\BookingStatusChanged($booking, $oldStatus, $validated['status']));
        }

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

    public function forceConfirm(Booking $booking)
    {
        $oldStatus = $booking->status;
        $booking->update([
            'status' => 'confirmed',
        ]);

        if ($oldStatus !== 'confirmed') {
            event(new \App\Events\BookingStatusChanged($booking, $oldStatus, 'confirmed'));
        }

        if ($booking->user->phone) {
            app(SmsService::class)->sendBookingConfirmation(
                $booking->user->phone,
                $booking->salonService->name,
                $booking->booking_date->format('M j, Y g:i A')
            );
        }

        return redirect()->back()->with('success', 'Booking confirmed successfully.');
    }

    public function resendReminder(Booking $booking)
    {
        $booking->load(['user', 'salonService']);

        if ($booking->user?->email) {
            Mail::to($booking->user->email)->send(new BookingReminder($booking));
        }

        if ($booking->user?->phone) {
            app(SmsService::class)->sendBookingReminder(
                $booking->user->phone,
                $booking->salonService->name,
                $booking->booking_date->format('M d, Y').' '.\Carbon\Carbon::parse($booking->booking_time)->format('g:i A')
            );
        }

        return redirect()->back()->with('success', 'Reminder sent successfully.');
    }
}
