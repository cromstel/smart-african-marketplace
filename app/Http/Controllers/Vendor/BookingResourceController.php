<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\BookingResource;
use App\Services\BookingResourceService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BookingResourceController extends Controller
{
    public function __construct(protected BookingResourceService $resourceService) {}

    /**
     * Display a listing of the vendor's resources.
     */
    public function index(Request $request)
    {
        $vendorId = auth()->user()->vendor?->id;

        $query = BookingResource::where('vendor_id', $vendorId);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            // Column is `resource_type` -- see store()/update() below for
            // the same naming mismatch across this whole controller.
            $query->where('resource_type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $resources = $query->orderBy('name')->paginate(20);

        $statusCounts = [
            'active' => BookingResource::where('vendor_id', $vendorId)->where('is_active', true)->count(),
            'inactive' => BookingResource::where('vendor_id', $vendorId)->where('is_active', false)->count(),
        ];

        return view('vendor.bookings.resources.index', compact('resources', 'statusCounts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('vendor.bookings.resources.create');
    }

    /**
     * Store a newly created resource.
     */
    public function store(Request $request)
    {
        // Field names here ('type', 'min_rental_hours') are the public
        // form/HTTP contract and are kept as-is; they're mapped onto
        // BookingResource's real columns ('resource_type',
        // 'minimum_rental_hours', ...) below rather than passed straight
        // through, which is what made every submission of this form throw
        // a SQL error (see BookingResourceService::createResource()).
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string|in:equipment,vehicle,property,facility,other',
            'hourly_rate' => 'required|numeric|min:0',
            'daily_rate' => 'nullable|numeric|min:0',
            'quantity_available' => 'required|integer|min:1',
            'min_rental_hours' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $this->resourceService->createResource([
            'vendor_id' => auth()->user()->vendor?->id,
            'resource_type' => $validated['type'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'pricing_type' => 'hourly',
            'hourly_rate' => $validated['hourly_rate'],
            'daily_rate' => $validated['daily_rate'] ?? 0,
            'quantity_total' => $validated['quantity_available'],
            'quantity_available' => $validated['quantity_available'],
            'minimum_rental_hours' => $validated['min_rental_hours'] ?? 1,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('vendor.booking-resources.index')
            ->with('success', 'Resource created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(BookingResource $bookingResource)
    {
        $this->ensureOwnership($bookingResource);

        $bookingResource->loadCount('bookings');

        // Get utilization stats for last 30 days
        $stats = $this->resourceService->getResourceUtilizationStats(
            $bookingResource->id,
            Carbon::now()->subDays(30),
            Carbon::now()
        );

        return view('vendor.bookings.resources.show', compact('bookingResource', 'stats'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BookingResource $bookingResource)
    {
        $this->ensureOwnership($bookingResource);

        return view('vendor.bookings.resources.edit', compact('bookingResource'));
    }

    /**
     * Update the specified resource.
     */
    public function update(Request $request, BookingResource $bookingResource)
    {
        $this->ensureOwnership($bookingResource);

        // See store() above: form field names are kept as the public
        // contract and mapped onto the real column names below.
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string|in:equipment,vehicle,property,facility,other',
            'hourly_rate' => 'required|numeric|min:0',
            'daily_rate' => 'nullable|numeric|min:0',
            'quantity_available' => 'required|integer|min:1',
            'min_rental_hours' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $this->resourceService->updateResource($bookingResource, [
            'resource_type' => $validated['type'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'hourly_rate' => $validated['hourly_rate'],
            'daily_rate' => $validated['daily_rate'] ?? 0,
            'quantity_available' => $validated['quantity_available'],
            'minimum_rental_hours' => $validated['min_rental_hours'] ?? 1,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('vendor.booking-resources.index')
            ->with('success', 'Resource updated successfully.');
    }

    /**
     * Remove the specified resource.
     */
    public function destroy(BookingResource $bookingResource)
    {
        $this->ensureOwnership($bookingResource);

        // Check for active bookings
        $activeBookings = $bookingResource->bookings()
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->count();

        if ($activeBookings > 0) {
            return redirect()->back()->with('error', 'Cannot delete resource with active bookings.');
        }

        $bookingResource->delete();

        return redirect()->route('vendor.booking-resources.index')
            ->with('success', 'Resource deleted successfully.');
    }

    /**
     * Toggle resource status.
     */
    public function toggle(BookingResource $bookingResource)
    {
        $this->ensureOwnership($bookingResource);

        $bookingResource->update([
            'is_active' => ! $bookingResource->is_active,
        ]);

        return redirect()->back()->with('success', 'Resource status updated.');
    }

    /**
     * Get availability calendar for a resource.
     */
    public function availability(Request $request, BookingResource $bookingResource)
    {
        $this->ensureOwnership($bookingResource);

        $startDate = Carbon::parse($request->get('start', Carbon::now()->toDateString()));
        $endDate = Carbon::parse($request->get('end', Carbon::now()->addMonths(3)->toDateString()));

        $unavailableDates = $this->resourceService->getUnavailableDates(
            $bookingResource->id,
            $startDate,
            $endDate
        );

        $bookings = $this->resourceService->getResourceBookings(
            $bookingResource->id,
            $startDate,
            $endDate
        );

        return view('vendor.bookings.resources.availability', compact(
            'bookingResource',
            'unavailableDates',
            'bookings',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Utilization/revenue statistics for the resource over a date range.
     *
     * The route for this existed (`booking-resources.statistics`) but this
     * method didn't -- calling it was a fatal "call to undefined method"
     * error. There was no dedicated stats view either; only show()'s
     * inline 30-day utilization card. Built out as its own page so a
     * vendor can pick a wider/narrower range than the fixed 30 days shown
     * on the resource detail page, reusing the same (already correct)
     * BookingResourceService methods.
     */
    public function statistics(Request $request, BookingResource $bookingResource)
    {
        $this->ensureOwnership($bookingResource);

        $startDate = Carbon::parse($request->get('start', Carbon::now()->subDays(90)->toDateString()));
        $endDate = Carbon::parse($request->get('end', Carbon::now()->toDateString()));

        $stats = $this->resourceService->getResourceUtilizationStats(
            $bookingResource->id,
            $startDate,
            $endDate
        );

        $bookings = $this->resourceService->getResourceBookings(
            $bookingResource->id,
            $startDate,
            $endDate
        );

        return view('vendor.bookings.resources.statistics', compact(
            'bookingResource',
            'stats',
            'bookings',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Ensure the user owns the resource.
     */
    protected function ensureOwnership(BookingResource $resource): void
    {
        if ($resource->vendor_id !== auth()->user()->vendor?->id) {
            abort(403);
        }
    }
}
