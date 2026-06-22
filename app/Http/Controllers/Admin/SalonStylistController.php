<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Salon;
use App\Models\SalonStylist;
use Illuminate\Http\Request;

class SalonStylistController extends Controller
{
    public function index()
    {
        $stylists = SalonStylist::with('salon')->latest()->paginate(20);

        return view('admin.salon-stylists.index', compact('stylists'));
    }

    public function create()
    {
        $salons = Salon::orderBy('name')->get();

        return view('admin.salon-stylists.create', compact('salons'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'salon_id' => 'nullable|exists:salons,id',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:30',
            'bio' => 'nullable|string',
            'years_experience' => 'nullable|integer|min:0|max:60',
            'is_active' => 'nullable|boolean',
        ]);

        SalonStylist::create($validated);

        return redirect()->route('admin.salon-stylists.index')
            ->with('success', 'Stylist created successfully.');
    }

    public function edit(SalonStylist $salonStylist)
    {
        $salons = Salon::orderBy('name')->get();

        return view('admin.salon-stylists.edit', compact('salonStylist', 'salons'));
    }

    public function update(Request $request, SalonStylist $salonStylist)
    {
        $validated = $request->validate([
            'salon_id' => 'nullable|exists:salons,id',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:30',
            'bio' => 'nullable|string',
            'years_experience' => 'nullable|integer|min:0|max:60',
            'is_active' => 'nullable|boolean',
        ]);

        $salonStylist->update($validated);

        return redirect()->route('admin.salon-stylists.index')
            ->with('success', 'Stylist updated successfully.');
    }

    public function destroy(SalonStylist $salonStylist)
    {
        $salonStylist->delete();

        return redirect()->route('admin.salon-stylists.index')
            ->with('success', 'Stylist deleted successfully.');
    }
}
