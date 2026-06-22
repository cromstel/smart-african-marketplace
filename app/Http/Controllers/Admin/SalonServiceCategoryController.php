<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SalonServiceCategory;
use App\Services\ImageOptimizationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SalonServiceCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index()
    {
        $categories = SalonServiceCategory::withCount('services')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.salon-categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.salon-categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:salon_service_categories,name',
            'description' => 'nullable|string|max:1000',
            'icon' => 'nullable|string|max:100',
            'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,avif|max:4096',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->has('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        if ($request->hasFile('banner_image')) {
            $imageService = app(ImageOptimizationService::class);
            $result = $imageService->uploadAndOptimize(
                $request->file('banner_image'),
                'salon-category-banners',
                1600,
                900,
                'salon_category_banner'
            );
            $validated['banner_image'] = $result['original'];
        }

        SalonServiceCategory::create($validated);

        return redirect()->route('admin.salon-categories.index')
            ->with('success', 'Salon service category created successfully');
    }

    public function edit(SalonServiceCategory $salonCategory)
    {
        return view('admin.salon-categories.edit', compact('salonCategory'));
    }

    public function update(Request $request, SalonServiceCategory $salonCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:salon_service_categories,name,'.$salonCategory->id,
            'description' => 'nullable|string|max:1000',
            'icon' => 'nullable|string|max:100',
            'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,avif|max:4096',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->has('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        if ($request->hasFile('banner_image')) {
            $imageService = app(ImageOptimizationService::class);
            if ($salonCategory->banner_image) {
                $imageService->deleteImage($salonCategory->banner_image);
            }
            $result = $imageService->uploadAndOptimize(
                $request->file('banner_image'),
                'salon-category-banners',
                1600,
                900,
                'salon_category_banner',
                $salonCategory->id
            );
            $validated['banner_image'] = $result['original'];
        }

        $salonCategory->update($validated);

        return redirect()->route('admin.salon-categories.index')
            ->with('success', 'Salon service category updated successfully');
    }

    public function destroy(SalonServiceCategory $salonCategory)
    {
        if ($salonCategory->services()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete category with existing services. Please reassign or delete the services first.');
        }

        if ($salonCategory->banner_image) {
            app(ImageOptimizationService::class)->deleteImage($salonCategory->banner_image);
        }

        $salonCategory->delete();

        return redirect()->route('admin.salon-categories.index')
            ->with('success', 'Salon service category deleted successfully');
    }

    public function toggle(SalonServiceCategory $salonCategory)
    {
        $salonCategory->update(['is_active' => ! $salonCategory->is_active]);

        return redirect()->back()->with('success', 'Category status updated successfully');
    }
}
