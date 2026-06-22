<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Services\ImageOptimizationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BrandController extends Controller
{
    private function vendorIdOrAbort(): int
    {
        $vendorId = auth()->user()->vendor?->id;
        if (! $vendorId) {
            abort(403, 'Unauthorized. Vendor profile required.');
        }

        return (int) $vendorId;
    }

    private function resolveUniqueSlug(string $value): string
    {
        $base = Str::slug($value);
        $candidate = $base;
        $counter = 1;

        while (Brand::query()->where('slug', $candidate)->exists()) {
            $candidate = $base.'-'.$counter;
            $counter++;
        }

        return $candidate;
    }

    public function index()
    {
        $vendorId = $this->vendorIdOrAbort();
        $brands = Brand::where('vendor_id', $vendorId)->latest()->paginate(20);

        return view('vendor.brands.index', compact('brands'));
    }

    public function create()
    {
        $this->vendorIdOrAbort();

        return view('vendor.brands.create');
    }

    public function store(Request $request, ImageOptimizationService $imageService)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
            'website' => 'nullable|url|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['slug'] = $this->resolveUniqueSlug($validated['name']);
        $validated['vendor_id'] = $this->vendorIdOrAbort();
        $validated['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('logo')) {
            $result = $imageService->uploadAndOptimize(
                $request->file('logo'),
                'brands',
                600,
                600,
                'brand'
            );
            $validated['logo'] = $result['original'];
        }

        Brand::create($validated);

        return redirect()->route('vendor.brands.index')
            ->with('success', 'Brand created successfully.');
    }

    public function edit(Brand $brand)
    {
        return redirect()
            ->route('vendor.brands.index')
            ->with('error', 'Brands can only be created by vendors. Updates are handled by admins.');
    }

    public function update(Request $request, Brand $brand)
    {
        return redirect()
            ->route('vendor.brands.index')
            ->with('error', 'Brands can only be created by vendors. Updates are handled by admins.');
    }

    public function destroy(Brand $brand)
    {
        return redirect()
            ->route('vendor.brands.index')
            ->with('error', 'Brands can only be created by vendors. Deletions are handled by admins.');
    }
}
