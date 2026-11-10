<?php

namespace App\Http\Controllers\Salon;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        return view('salon-console.services.index', [
            'title' => 'Manage Services',
            'services' => [],
        ]);
    }

    public function create()
    {
        return view('salon-console.services.create', [
            'title' => 'Add Service',
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'duration' => 'required|integer|min:15',
        ]);

        return redirect()->route('salon.services.index')
            ->with('success', 'Service created successfully.');
    }

    public function edit($service)
    {
        return view('salon-console.services.edit', [
            'title' => 'Edit Service',
            'service' => null,
        ]);
    }

    public function update(Request $request, $service)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'duration' => 'required|integer|min:15',
        ]);

        return redirect()->route('salon.services.index')
            ->with('success', 'Service updated successfully.');
    }

    public function destroy($service)
    {
        return redirect()->route('salon.services.index')
            ->with('success', 'Service deleted successfully.');
    }
}
