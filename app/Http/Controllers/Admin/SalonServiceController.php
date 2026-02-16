<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\SalonService;
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

        return view('admin.salon-services.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:15',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
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

        return view('admin.salon-services.edit', compact('salonService', 'categories'));
    }

    public function update(Request $request, SalonService $salonService)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:15',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
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
