<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Salon;
use App\Models\SalonService;
use App\Models\SalonServiceCategory;
use App\Models\SalonStylist;
use App\Services\ImageOptimizationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SalonServiceController extends Controller
{
    public function index()
    {
        $services = SalonService::with(['category', 'user'])
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

        return view('admin.salon-services.create', compact('categories', 'salonCategories', 'salons', 'stylists'));
    }

    public function store(Request $request)
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

        $validated['user_id'] = auth()->id();
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
        if ($salonService->image) {
            Storage::disk('public')->delete($salonService->image);
        }

        $salonService->delete();

        return redirect()->route('admin.salon-services.index')
            ->with('success', 'Salon service deleted successfully');
    }
}
