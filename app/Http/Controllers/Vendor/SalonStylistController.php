<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Salon;
use App\Models\SalonStylist;
use Illuminate\Http\Request;

class SalonStylistController extends Controller
{
    public function index()
    {
        $salonIds = Salon::where('user_id', auth()->id())->pluck('id');
        $stylists = SalonStylist::whereIn('salon_id', $salonIds)->latest()->paginate(20);

        return view('vendor.salon-stylists.index', compact('stylists'));
    }

    public function create()
    {
        $salons = Salon::where('user_id', auth()->id())->orderBy('name')->get();

        return view('vendor.salon-stylists.create', compact('salons'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'salon_id' => 'required|exists:salons,id',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:30',
            'bio' => 'nullable|string',
            'years_experience' => 'nullable|integer|min:0|max:60',
            'is_active' => 'nullable|boolean',
        ]);

        $salon = Salon::findOrFail($validated['salon_id']);
        if ($salon->user_id !== auth()->id()) {
            abort(403);
        }

        SalonStylist::create($validated);

        return redirect()->route('vendor.salon-stylists.index')
            ->with('success', 'Stylist created successfully.');
    }

    public function edit(SalonStylist $salonStylist)
    {
        $salons = Salon::where('user_id', auth()->id())->orderBy('name')->get();

        if ($salonStylist->salon && $salonStylist->salon->user_id !== auth()->id()) {
            abort(403);
        }

        return view('vendor.salon-stylists.edit', compact('salonStylist', 'salons'));
    }

    public function update(Request $request, SalonStylist $salonStylist)
    {
        if ($salonStylist->salon && $salonStylist->salon->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'salon_id' => 'required|exists:salons,id',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:30',
            'bio' => 'nullable|string',
            'years_experience' => 'nullable|integer|min:0|max:60',
            'is_active' => 'nullable|boolean',
        ]);

        $salon = Salon::findOrFail($validated['salon_id']);
        if ($salon->user_id !== auth()->id()) {
            abort(403);
        }

        $salonStylist->update($validated);

        return redirect()->route('vendor.salon-stylists.index')
            ->with('success', 'Stylist updated successfully.');
    }

    public function destroy(SalonStylist $salonStylist)
    {
        if ($salonStylist->salon && $salonStylist->salon->user_id !== auth()->id()) {
            abort(403);
        }

        $salonStylist->delete();

        return redirect()->route('vendor.salon-stylists.index')
            ->with('success', 'Stylist deleted successfully.');
    }
}
