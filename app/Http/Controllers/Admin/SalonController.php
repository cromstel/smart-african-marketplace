<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Salon;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SalonController extends Controller
{
    public function index()
    {
        $salons = Salon::with('user')->latest()->paginate(20);

        return view('admin.salon.index', compact('salons'));
    }

    public function create()
    {
        $owners = User::query()
            ->whereIn('role', ['vendor', 'salon_owner'])
            ->orderBy('name')
            ->get();

        return view('admin.salon.create', compact('owners'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
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

        $validated['slug'] = Str::slug($validated['name']);

        Salon::create($validated);

        return redirect()->route('admin.salon.index')
            ->with('success', 'Salon created successfully.');
    }

    public function edit(Salon $salon)
    {
        $owners = User::query()
            ->whereIn('role', ['vendor', 'salon_owner'])
            ->orderBy('name')
            ->get();

        return view('admin.salon.edit', compact('salon', 'owners'));
    }

    public function update(Request $request, Salon $salon)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
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

        $validated['slug'] = Str::slug($validated['name']);

        $salon->update($validated);

        return redirect()->route('admin.salon.index')
            ->with('success', 'Salon updated successfully.');
    }

    public function destroy(Salon $salon)
    {
        $salon->delete();

        return redirect()->route('admin.salon.index')
            ->with('success', 'Salon deleted successfully.');
    }
}
