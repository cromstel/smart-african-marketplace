<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Salon;
use App\Models\SalonService;
use App\Models\SalonServiceCategory;
use App\Models\SalonStylist;
use App\Models\Vendor;
use App\Services\ImageOptimizationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SalonServiceController extends Controller
{
    public function index()
    {
        $services = SalonService::with(['category', 'salonCategory', 'user'])
            ->latest()
            ->paginate(20);

        return view('admin.salon-services.index', compact('services'));
    }

    public function create()
    {
        $categories = Category::all();
        $salonCategories = SalonServiceCategory::active()->orderBy('sort_order')->orderBy('name')->get();
        $salons = Salon::orderBy('name')->get();
        $stylists = SalonStylist::orderBy('name')->get();
        $vendors = Vendor::where('status', 'approved')->orderBy('business_name')->get();

        return view('admin.salon-services.create', compact('categories', 'salonCategories', 'salons', 'stylists', 'vendors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            // The owning vendor's user_id, not the admin's -- salon_services.user_id
            // is read as the vendor owner everywhere else in the app (the vendor's
            // own controller sets it from $request->user()->id, and this admin
            // panel's own index view renders $service->user->name as "Vendor").
            'user_id' => [
                'required',
                Rule::exists('vendors', 'user_id')->where('status', 'approved'),
            ],
            'salon_id' => 'nullable|exists:salons,id',
            'stylist_id' => 'nullable|exists:salon_stylists,id',
            'category_id' => 'required|exists:categories,id',
            'salon_category_id' => 'nullable|exists:salon_service_categories,id',
            'price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:15',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,avif|max:2048',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->has('is_active');

        if ($request->hasFile('image')) {
            $imageService = app(ImageOptimizationService::class);
            $result = $imageService->uploadAndOptimize($request->file('image'), 'salon-services', 800, 600);
            $validated['image'] = $result['original'];
        }

        SalonService::create($validated);

        return redirect()->route('admin.salon-services.index')
            ->with('success', 'Salon service created successfully');
    }

    public function edit(SalonService $salonService)
    {
        $salonService->load('user.vendor');
        $categories = Category::all();
        $salonCategories = SalonServiceCategory::active()->orderBy('sort_order')->orderBy('name')->get();
        $salons = Salon::orderBy('name')->get();
        $stylists = SalonStylist::orderBy('name')->get();

        return view('admin.salon-services.edit', compact('salonService', 'categories', 'salonCategories', 'salons', 'stylists'));
    }

    public function update(Request $request, SalonService $salonService)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'salon_id' => 'nullable|exists:salons,id',
            'stylist_id' => 'nullable|exists:salon_stylists,id',
            'category_id' => 'required|exists:categories,id',
            'salon_category_id' => 'nullable|exists:salon_service_categories,id',
            'price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:15',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,avif|max:2048',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->has('is_active');

        if ($request->hasFile('image')) {
            $imageService = app(ImageOptimizationService::class);

            if ($salonService->image) {
                $imageService->deleteImage($salonService->image);
            }

            $result = $imageService->uploadAndOptimize($request->file('image'), 'salon-services', 800, 600);
            $validated['image'] = $result['original'];
        }

        $salonService->update($validated);

        return redirect()->route('admin.salon-services.index')
            ->with('success', 'Salon service updated successfully');
    }

    public function destroy(SalonService $salonService)
    {
        if ($salonService->bookings()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete a service with existing bookings.');
        }

        if ($salonService->image) {
            Storage::disk('public')->delete($salonService->image);
        }

        $salonService->delete();

        return redirect()->route('admin.salon-services.index')
            ->with('success', 'Salon service deleted successfully');
    }
}
