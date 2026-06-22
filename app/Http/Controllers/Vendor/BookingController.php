<?php

namespace App\Http\Controllers\Vendor;

use App\Events\BookingStatusChanged;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $vendorId = auth()->id();

        $query = Booking::with(['user', 'salonService'])
            ->whereHas('salonService', function ($q) use ($vendorId) {
                $q->where('user_id', $vendorId);
            });

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('booking_number', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $bookings = $query->latest()->paginate(20)->withQueryString();

        $statusCounts = Booking::whereHas('salonService', function ($q) use ($vendorId) {
            $q->where('user_id', $vendorId);
        })->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return view('vendor.bookings.index', compact('bookings', 'statusCounts'));
    }

    public function show(Booking $booking)
    {
        $this->ensureOwnership($booking);

        $booking->load(['user', 'salonService']);

        return view('vendor.bookings.show', compact('booking'));
    }

    public function confirm(Request $request, Booking $booking)
    {
        $this->ensureOwnership($booking);

        if ($booking->status !== 'pending') {
            return redirect()->back()->with('error', 'Only pending bookings can be confirmed.');
        }

        $validated = $request->validate([
            'staff_notes' => 'nullable|string|max:1000',
        ]);

        $oldStatus = $booking->status;
        $booking->update([
            'status' => 'confirmed',
            'staff_notes' => $validated['staff_notes'] ?? $booking->staff_notes,
        ]);

        event(new BookingStatusChanged($booking, $oldStatus, 'confirmed'));

        return redirect()->back()->with('success', 'Booking confirmed successfully.');
    }

    public function cancel(Request $request, Booking $booking)
    {
        $this->ensureOwnership($booking);

        if (in_array($booking->status, ['completed', 'cancelled'], true)) {
            return redirect()->back()->with('error', 'This booking cannot be cancelled.');
        }

        $validated = $request->validate([
            'staff_notes' => 'nullable|string|max:1000',
        ]);

        $oldStatus = $booking->status;
        $booking->update([
            'status' => 'cancelled',
            'staff_notes' => $validated['staff_notes'] ?? $booking->staff_notes,
            'cancelled_at' => now(),
        ]);

        event(new BookingStatusChanged($booking, $oldStatus, 'cancelled'));

        return redirect()->back()->with('success', 'Booking cancelled successfully.');
    }

    public function message(Request $request, Booking $booking, SmsService $smsService)
    {
        $this->ensureOwnership($booking);

        $validated = $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $customer = $booking->user;
        $message = $validated['message'];

        if ($customer?->email) {
            Mail::raw($message, function ($mail) use ($customer) {
                $mail->to($customer->email)
                    ->subject('Message about your booking');
            });
        }

        if ($customer?->phone) {
            $smsService->send($customer->phone, $message);
        }

        return redirect()->back()->with('success', 'Message sent to customer.');
    }

    protected function ensureOwnership(Booking $booking): void
    {
        $vendorId = auth()->id();

        if ($booking->salonService?->user_id !== $vendorId) {
            abort(403);
        }
    }
}
