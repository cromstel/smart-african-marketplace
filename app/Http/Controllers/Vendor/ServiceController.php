<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\VendorService;
use App\Models\VendorServiceCategory;
use App\Services\ImageOptimizationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $vendor = $request->user()->vendor;

        if (! $vendor) {
            return redirect()->route('home')->with('error', 'Vendor profile not found');
        }

        $services = VendorService::with('category')
            ->where('vendor_id', $vendor->id)
            ->latest()
            ->paginate(20);

        return view('vendor.services.index', compact('services', 'vendor'));
    }

    public function create()
    {
        $vendor = auth()->user()->vendor;

        if (! $vendor) {
            return redirect()->route('home')->with('error', 'Vendor profile not found');
        }

        $categories = VendorServiceCategory::active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('vendor.services.create', compact('categories', 'vendor'));
    }

    public function store(Request $request)
    {
        $vendor = auth()->user()->vendor;

        if (! $vendor) {
            return redirect()->route('home')->with('error', 'Vendor profile not found');
        }

        $validated = $request->validate([
            'vendor_service_category_id' => [
                'required',
                Rule::exists('vendor_service_categories', 'id')->where('is_active', true),
            ],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:15|max:480',
            'status' => 'required|in:available,booked,unavailable',
            'is_featured' => 'boolean',
            'image' => 'nullable|image|max:5120',
        ]);

        $data = [
            'vendor_id' => $vendor->id,
            'vendor_service_category_id' => $validated['vendor_service_category_id'],
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'duration_minutes' => $validated['duration_minutes'],
            'status' => $validated['status'],
            'is_featured' => $validated['is_featured'] ?? false,
        ];

        if ($request->hasFile('image')) {
            $imageService = app(ImageOptimizationService::class);
            $result = $imageService->uploadAndOptimize(
                $request->file('image'),
                'vendor-services/'.$vendor->id,
                800,
                600,
                'vendor_service',
                null,
                true
            );
            $data['image'] = $result['original'];
        }

        VendorService::create($data);

        return redirect()->route('vendor.services.index')
            ->with('success', 'Service created successfully.');
    }

    public function edit(VendorService $service)
    {
        $vendor = auth()->user()->vendor;

        if (! $vendor || $service->vendor_id !== $vendor->id) {
            return redirect()->route('vendor.services.index')
                ->with('error', 'Service not found or unauthorized.');
        }

        $categories = VendorServiceCategory::active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('vendor.services.edit', compact('service', 'categories', 'vendor'));
    }

    public function update(Request $request, VendorService $service)
    {
        $vendor = auth()->user()->vendor;

        if (! $vendor || $service->vendor_id !== $vendor->id) {
            return redirect()->route('vendor.services.index')
                ->with('error', 'Service not found or unauthorized.');
        }

        $validated = $request->validate([
            'vendor_service_category_id' => [
                'required',
                Rule::exists('vendor_service_categories', 'id')->where('is_active', true),
            ],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:15|max:480',
            'status' => 'required|in:available,booked,unavailable',
            'is_featured' => 'boolean',
            'image' => 'nullable|image|max:5120',
        ]);

        $data = [
            'vendor_service_category_id' => $validated['vendor_service_category_id'],
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'duration_minutes' => $validated['duration_minutes'],
            'status' => $validated['status'],
            'is_featured' => $validated['is_featured'] ?? false,
        ];

        if ($request->hasFile('image')) {
            $imageService = app(ImageOptimizationService::class);
            $result = $imageService->uploadAndOptimize(
                $request->file('image'),
                'vendor-services/'.$vendor->id,
                800,
                600,
                'vendor_service',
                null,
                true
            );
            $data['image'] = $result['original'];
        }

        $service->update($data);

        return redirect()->route('vendor.services.index')
            ->with('success', 'Service updated successfully.');
    }

    public function destroy(VendorService $service)
    {
        $vendor = auth()->user()->vendor;

        if (! $vendor || $service->vendor_id !== $vendor->id) {
            return redirect()->route('vendor.services.index')
                ->with('error', 'Service not found or unauthorized.');
        }

        $service->delete();

        return redirect()->route('vendor.services.index')
            ->with('success', 'Service deleted successfully.');
    }
}
