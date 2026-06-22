<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\VendorAvailability;
use Illuminate\Http\Request;

class VendorAvailabilityController extends Controller
{
    public function index()
    {
        $vendor = auth()->user()->vendor;

        $availabilities = $vendor?->availabilities()
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get() ?? collect();

        return view('vendor.settings.availability.index', compact('availabilities'));
    }

    public function store(Request $request)
    {
        $vendor = auth()->user()->vendor;

        $validated = $this->validateAvailability($request);
        $validated['vendor_id'] = $vendor->id;

        VendorAvailability::create($validated);

        return redirect()
            ->route('vendor.settings.availability.index')
            ->with('success', 'Availability added successfully.');
    }

    public function update(Request $request, VendorAvailability $availability)
    {
        $vendor = auth()->user()->vendor;

        if ($availability->vendor_id !== $vendor->id) {
            abort(403);
        }

        $validated = $this->validateAvailability($request);

        $availability->update($validated);

        return redirect()
            ->route('vendor.settings.availability.index')
            ->with('success', 'Availability updated successfully.');
    }

    public function destroy(VendorAvailability $availability)
    {
        $vendor = auth()->user()->vendor;

        if ($availability->vendor_id !== $vendor->id) {
            abort(403);
        }

        $availability->delete();

        return redirect()
            ->route('vendor.settings.availability.index')
            ->with('success', 'Availability removed.');
    }

    protected function validateAvailability(Request $request): array
    {
        $validated = $request->validate([
            'day_of_week' => 'required|integer|between:1,7',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'slot_interval_minutes' => 'required|integer|min:10|max:240',
            'min_lead_time_minutes' => 'required|integer|min:0|max:1440',
            'buffer_minutes' => 'nullable|integer|min:0|max:240',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}
