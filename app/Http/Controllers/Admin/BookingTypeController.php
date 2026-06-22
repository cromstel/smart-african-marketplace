<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookingType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BookingTypeController extends Controller
{
    /**
     * Display a listing of booking types.
     */
    public function index()
    {
        $bookingTypes = BookingType::orderBy('sort_order')->get();

        return view('admin.booking-types.index', compact('bookingTypes'));
    }

    /**
     * Show the form for creating a new booking type.
     */
    public function create()
    {
        return view('admin.booking-types.create');
    }

    /**
     * Store a newly created booking type.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:booking_types,name',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'default_duration_minutes' => 'required|integer|min:15',
            'min_duration_minutes' => 'nullable|integer|min:1',
            'max_duration_minutes' => 'nullable|integer|min:15',
            'booking_window_days' => 'required|integer|min:1',
            'cancellation_policy_hours' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active', true);

        BookingType::create($validated);

        return redirect()->route('admin.booking-types.index')
            ->with('success', 'Booking type created successfully.');
    }

    /**
     * Display the specified booking type.
     */
    public function show(BookingType $bookingType)
    {
        $bookingType->loadCount('bookings');

        return view('admin.booking-types.show', compact('bookingType'));
    }

    /**
     * Show the form for editing the specified booking type.
     */
    public function edit(BookingType $bookingType)
    {
        return view('admin.booking-types.edit', compact('bookingType'));
    }

    /**
     * Update the specified booking type.
     */
    public function update(Request $request, BookingType $bookingType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:booking_types,name,'.$bookingType->id,
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'default_duration_minutes' => 'required|integer|min:15',
            'min_duration_minutes' => 'nullable|integer|min:1',
            'max_duration_minutes' => 'nullable|integer|min:15',
            'booking_window_days' => 'required|integer|min:1',
            'cancellation_policy_hours' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active', true);

        $bookingType->update($validated);

        return redirect()->route('admin.booking-types.index')
            ->with('success', 'Booking type updated successfully.');
    }

    /**
     * Remove the specified booking type.
     */
    public function destroy(BookingType $bookingType)
    {
        if ($bookingType->bookings()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete booking type with existing bookings.');
        }

        $bookingType->delete();

        return redirect()->route('admin.booking-types.index')
            ->with('success', 'Booking type deleted successfully.');
    }

    /**
     * Toggle booking type status.
     */
    public function toggle(BookingType $bookingType)
    {
        $bookingType->update([
            'is_active' => ! $bookingType->is_active,
        ]);

        return redirect()->back()->with('success', 'Booking type status updated.');
    }
}
