<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Salon;
use App\Models\SalonService;
use App\Models\SalonServiceCategory;
use App\Models\SalonStylist;
use App\Services\ImageOptimizationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SalonServiceController extends Controller
{
    public function index(Request $request)
    {
        $services = SalonService::with(['category', 'salonCategory'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return view('vendor.salon-services.index', compact('services'));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $salonCategories = SalonServiceCategory::active()->orderBy('sort_order')->orderBy('name')->get();
        $salons = Salon::where('user_id', auth()->id())->orderBy('name')->get();
        $stylists = SalonStylist::whereHas('salon', function ($query) {
            $query->where('user_id', auth()->id());
        })->orderBy('name')->get();

        return view('vendor.salon-services.create', compact('categories', 'salonCategories', 'salons', 'stylists'));
    }

    public function store(Request $request)
    {
        $salonIds = Salon::where('user_id', $request->user()->id)->pluck('id');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'salon_id' => [
                'nullable',
                Rule::exists('salons', 'id')->where('user_id', $request->user()->id),
            ],
            'stylist_id' => [
                'nullable',
                Rule::exists('salon_stylists', 'id')->where(function ($query) use ($salonIds) {
                    $query->whereIn('salon_id', $salonIds);
                }),
            ],
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->where('is_active', true),
            ],
            'salon_category_id' => [
                'nullable',
                Rule::exists('salon_service_categories', 'id')->where('is_active', true),
            ],
            'price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:15',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,avif|max:2048',
            'is_active' => 'boolean',
        ]);

        $validated['user_id'] = $request->user()->id;
        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->has('is_active');

        if ($request->hasFile('image')) {
            $imageService = app(ImageOptimizationService::class);
            $result = $imageService->uploadAndOptimize($request->file('image'), 'salon-services', 800, 600);
            $validated['image'] = $result['original'];
        }

        SalonService::create($validated);

        return redirect()->route('vendor.salon-services.index')
            ->with('success', 'Salon service created successfully');
    }

    public function edit(SalonService $salonService)
    {
        if ($salonService->user_id !== auth()->id()) {
            abort(403);
        }

        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $salonCategories = SalonServiceCategory::active()->orderBy('sort_order')->orderBy('name')->get();
        $salons = Salon::where('user_id', auth()->id())->orderBy('name')->get();
        $stylists = SalonStylist::whereHas('salon', function ($query) {
            $query->where('user_id', auth()->id());
        })->orderBy('name')->get();

        return view('vendor.salon-services.edit', compact('salonService', 'categories', 'salonCategories', 'salons', 'stylists'));
    }

    public function update(Request $request, SalonService $salonService)
    {
        if ($salonService->user_id !== $request->user()->id) {
            abort(403);
        }

        $salonIds = Salon::where('user_id', $request->user()->id)->pluck('id');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'salon_id' => [
                'nullable',
                Rule::exists('salons', 'id')->where('user_id', $request->user()->id),
            ],
            'stylist_id' => [
                'nullable',
                Rule::exists('salon_stylists', 'id')->where(function ($query) use ($salonIds) {
                    $query->whereIn('salon_id', $salonIds);
                }),
            ],
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->where('is_active', true),
            ],
            'salon_category_id' => [
                'nullable',
                Rule::exists('salon_service_categories', 'id')->where('is_active', true),
            ],
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

        return redirect()->route('vendor.salon-services.index')
            ->with('success', 'Salon service updated successfully');
    }

    public function destroy(SalonService $salonService)
    {
        if ($salonService->user_id !== auth()->id()) {
            abort(403);
        }

        if ($salonService->bookings()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete a service with existing bookings.');
        }

        if ($salonService->image) {
            app(ImageOptimizationService::class)->deleteImage($salonService->image);
        }

        $salonService->delete();

        return redirect()->route('vendor.salon-services.index')
            ->with('success', 'Salon service deleted successfully');
    }
}
