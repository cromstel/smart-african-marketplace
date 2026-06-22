<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\BookingResource;
use App\Services\BookingResourceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BookingResourceController extends Controller
{
    public function __construct(protected BookingResourceService $resourceService) {}

    /**
     * Display a listing of the vendor's resources.
     */
    public function index(Request $request)
    {
        $vendorId = auth()->id();

        $query = BookingResource::where('vendor_id', $vendorId);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
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
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string|in:equipment,vehicle,property,facility,other',
            'category' => 'nullable|string|max:100',
            'hourly_rate' => 'required|numeric|min:0',
            'daily_rate' => 'nullable|numeric|min:0',
            'weekly_rate' => 'nullable|numeric|min:0',
            'monthly_rate' => 'nullable|numeric|min:0',
            'quantity_available' => 'required|integer|min:1',
            'min_rental_hours' => 'nullable|integer|min:1',
            'max_rental_hours' => 'nullable|integer|min:1',
            'requires_deposit' => 'boolean',
            'deposit_amount' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['vendor_id'] = auth()->id();
        $validated['requires_deposit'] = $request->boolean('requires_deposit', false);
        $validated['is_active'] = $request->boolean('is_active', true);

        $this->resourceService->createResource($validated);

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

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string|in:equipment,vehicle,property,facility,other',
            'category' => 'nullable|string|max:100',
            'hourly_rate' => 'required|numeric|min:0',
            'daily_rate' => 'nullable|numeric|min:0',
            'weekly_rate' => 'nullable|numeric|min:0',
            'monthly_rate' => 'nullable|numeric|min:0',
            'quantity_available' => 'required|integer|min:1',
            'min_rental_hours' => 'nullable|integer|min:1',
            'max_rental_hours' => 'nullable|integer|min:1',
            'requires_deposit' => 'boolean',
            'deposit_amount' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['requires_deposit'] = $request->boolean('requires_deposit', false);
        $validated['is_active'] = $request->boolean('is_active', true);

        $this->resourceService->updateResource($bookingResource, $validated);

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
     * Ensure the user owns the resource.
     */
    protected function ensureOwnership(BookingResource $resource): void
    {
        if ($resource->vendor_id !== auth()->id()) {
            abort(403);
        }
    }
}
