<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookingType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BookingTypeController extends Controller
{
    /**
     * Display a listing of booking types.
     */
    public function index()
    {
        $bookingTypes = BookingType::withCount('bookings')->orderBy('sort_order')->orderBy('name')->get();

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
        $validated = $request->validate($this->rules($request));

        // The slug is what bookings.booking_type and BookingService match on,
        // so two names that slugify identically ("Class" / "class!") must be
        // rejected here rather than by the unique index.
        $validated['slug'] = Str::slug($validated['name']);
        if ($validated['slug'] === '' || BookingType::where('slug', $validated['slug'])->exists()) {
            throw ValidationException::withMessages([
                'name' => 'This name produces a slug that is empty or already in use.',
            ]);
        }

        BookingType::create($this->normalize($request, $validated));

        return redirect()->route('admin.booking-types.index')
            ->with('success', 'Booking type created successfully.');
    }

    /**
     * Show the form for editing the specified booking type.
     */
    public function edit(BookingType $bookingType)
    {
        $bookingType->loadCount('bookings');

        return view('admin.booking-types.edit', compact('bookingType'));
    }

    /**
     * Update the specified booking type.
     *
     * The slug is deliberately left unchanged: existing bookings reference
     * their type by slug, so regenerating it from a renamed display name
     * would orphan every one of them.
     */
    public function update(Request $request, BookingType $bookingType)
    {
        $validated = $request->validate($this->rules($request, $bookingType));

        $bookingType->update($this->normalize($request, $validated));

        return redirect()->route('admin.booking-types.index')
            ->with('success', 'Booking type updated successfully.');
    }

    /**
     * Remove the specified booking type.
     */
    public function destroy(BookingType $bookingType)
    {
        if ($bookingType->bookings()->exists()) {
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

    /**
     * Validation rules shared by store and update.
     */
    protected function rules(Request $request, ?BookingType $bookingType = null): array
    {
        $rules = [
            'name' => 'required|string|max:255|unique:booking_types,name'.($bookingType ? ','.$bookingType->id : ''),
            'description' => 'nullable|string|max:2000',
            'icon' => 'nullable|string|max:50',
            'default_duration_minutes' => 'required|integer|min:15',
            'min_duration_minutes' => 'nullable|integer|min:1',
            'max_duration_minutes' => 'nullable|integer|min:15',
            'booking_window_days' => 'required|integer|min:1',
            'cancellation_policy_hours' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ];

        if ($request->filled('min_duration_minutes')) {
            $rules['default_duration_minutes'] .= '|gte:min_duration_minutes';
            $rules['max_duration_minutes'] .= '|gte:min_duration_minutes';
        }
        if ($request->filled('max_duration_minutes')) {
            $rules['default_duration_minutes'] .= '|lte:max_duration_minutes';
        }

        return $rules;
    }

    /**
     * Apply form-level defaults to validated input.
     */
    protected function normalize(Request $request, array $validated): array
    {
        // Unchecked checkboxes send nothing; the forms post a hidden 0 so the
        // absence of the field only happens for non-form clients.
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        return $validated;
    }
}
