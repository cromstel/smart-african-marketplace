<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingResource;
use App\Services\BookingResourceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Customer-facing rental booking creation -- the live counterpart to
 * BookingController's salon-service flow, for the "rental" BookingType.
 * BookingResourceService already had solid, previously-fixed availability/
 * pricing logic (see its own docblocks); nothing before this called it to
 * actually create a Booking. Booking::create() here mirrors
 * BookingController::store()'s direct-creation pattern rather than routing
 * through BookingService::createBooking(), which still references columns
 * that don't exist on `bookings` (see Booking::$fillable's docblock) and
 * isn't touched by this feature.
 */
class ResourceBookingController extends Controller
{
    public function __construct(
        protected BookingResourceService $resourceService
    ) {}

    public function create(BookingResource $resource)
    {
        if (! $resource->is_active) {
            abort(404);
        }

        $resource->load('vendor');

        $startDate = now()->addDay()->startOfDay();
        $unavailableDates = $this->resourceService->getUnavailableDates(
            $resource->id,
            $startDate,
            $startDate->copy()->addDays(60)
        );

        return view('resource-bookings.create', compact('resource', 'unavailableDates'));
    }

    public function store(Request $request, BookingResource $resource)
    {
        if (! $resource->is_active) {
            abort(404);
        }

        $validated = $request->validate([
            'start_date' => 'required|date|after_or_equal:tomorrow',
            'end_date' => 'required|date|after_or_equal:start_date',
            'quantity' => 'required|integer|min:1',
            'customer_notes' => 'nullable|string|max:500',
        ]);

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);
        $days = $startDate->diffInDays($endDate) + 1;
        $quantity = (int) $validated['quantity'];

        $availableQuantity = $this->resourceService->getAvailableQuantity($resource->id, $startDate, $endDate);

        if ($quantity > $availableQuantity) {
            return redirect()->back()->withInput()->with(
                'error',
                $availableQuantity > 0
                    ? "Only {$availableQuantity} available for those dates."
                    : 'Not available for those dates.'
            );
        }

        $unitPrice = $resource->calculatePrice($days * 24, $days);
        $totalAmount = round($unitPrice * $quantity, 2);

        $booking = Booking::create([
            'booking_number' => 'RTL-'.now()->format('ymd').'-'.strtoupper(Str::random(4)),
            'booking_type' => 'rental',
            'user_id' => auth()->id(),
            'resource_id' => $resource->id,
            'resource_name' => $resource->name,
            'salon_owner_id' => $resource->vendor->user_id,
            'booking_date' => $startDate,
            'end_date' => $endDate,
            'duration_minutes' => $days * 1440,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'price' => $totalAmount,
            'total_amount' => $totalAmount,
            'status' => 'pending',
            'payment_status' => 'pending',
            'customer_notes' => $validated['customer_notes'] ?? null,
        ]);

        return redirect()->route('bookings.show', $booking)
            ->with('success', 'Rental request submitted. We will confirm availability shortly.');
    }
}
