<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Salon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SalonController extends Controller
{
    public function index()
    {
        $salons = Salon::where('user_id', auth()->id())->latest()->paginate(20);

        return view('vendor.salon.index', compact('salons'));
    }

    public function create()
    {
        return view('vendor.salon.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'is_active' => 'nullable|boolean',
        ]);

        Salon::create([
            ...$validated,
            'user_id' => auth()->id(),
            'slug' => Str::slug($validated['name']),
        ]);

        return redirect()->route('vendor.salon.index')
            ->with('success', 'Salon created successfully.');
    }

    public function edit(Salon $salon)
    {
        if ($salon->user_id !== auth()->id()) {
            abort(403);
        }

        return view('vendor.salon.edit', compact('salon'));
    }

    public function update(Request $request, Salon $salon)
    {
        if ($salon->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'is_active' => 'nullable|boolean',
        ]);

        $salon->update([
            ...$validated,
            'slug' => Str::slug($validated['name']),
        ]);

        return redirect()->route('vendor.salon.index')
            ->with('success', 'Salon updated successfully.');
    }

    public function destroy(Salon $salon)
    {
        if ($salon->user_id !== auth()->id()) {
            abort(403);
        }

        $salon->delete();

        return redirect()->route('vendor.salon.index')
            ->with('success', 'Salon deleted successfully.');
    }
}
