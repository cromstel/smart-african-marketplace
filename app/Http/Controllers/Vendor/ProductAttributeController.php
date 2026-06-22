<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\ProductAttribute;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductAttributeController extends Controller
{
    public function index()
    {
        $attributes = ProductAttribute::where('vendor_id', auth()->user()->vendor->id)
            ->orWhereNull('vendor_id')
            ->orderBy('sort_order')
            ->paginate(20);

        return view('vendor.attributes.index', compact('attributes'));
    }

    public function create()
    {
        return view('vendor.attributes.create');
    }

    public function store(Request $request)
    {
        $vendorId = auth()->user()->vendor->id;
        $validated = $this->validateAttribute($request, $vendorId);
        $validated['vendor_id'] = auth()->user()->vendor->id;

        ProductAttribute::create($validated);

        return redirect()->route('vendor.attributes.index')
            ->with('success', 'Attribute created successfully.');
    }

    public function edit(ProductAttribute $attribute)
    {
        if ($attribute->vendor_id !== auth()->user()->vendor->id) {
            abort(403, 'Unauthorized action.');
        }

        return view('vendor.attributes.edit', compact('attribute'));
    }

    public function update(Request $request, ProductAttribute $attribute)
    {
        if ($attribute->vendor_id !== auth()->user()->vendor->id) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $this->validateAttribute($request, (int) $attribute->vendor_id, $attribute->id);

        $attribute->update($validated);

        return redirect()->route('vendor.attributes.index')
            ->with('success', 'Attribute updated successfully.');
    }

    public function destroy(ProductAttribute $attribute)
    {
        if ($attribute->vendor_id !== auth()->user()->vendor->id) {
            abort(403, 'Unauthorized action.');
        }

        $attribute->delete();

        return redirect()->route('vendor.attributes.index')
            ->with('success', 'Attribute deleted successfully.');
    }

    private function validateAttribute(Request $request, int $vendorId, ?int $attributeId = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:28', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'description' => ['nullable', 'string'],
            'type' => ['required', Rule::in(['text', 'select', 'color', 'boolean'])],
            'values' => ['nullable', 'array'],
            'values.*' => ['string'],
            'is_filterable' => ['nullable', 'boolean'],
            'is_visible' => ['nullable', 'boolean'],
            'enable_archives' => ['nullable', 'boolean'],
            'default_sort_order' => ['required', Rule::in(['custom_ordering', 'name', 'name_num', 'term_id'])],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $base = Str::slug($validated['slug'] ?? $validated['name']);
        $candidate = $base !== '' ? Str::limit($base, 28, '') : Str::random(8);

        $counter = 1;
        while (
            ProductAttribute::query()
                ->when($attributeId, fn ($q) => $q->where('id', '!=', $attributeId))
                ->where('vendor_id', $vendorId)
                ->where('slug', $candidate)
                ->exists()
        ) {
            $suffix = '-'.$counter;
            $stem = Str::limit($base, max(1, 28 - strlen($suffix)), '');
            $candidate = $stem.$suffix;
            $counter++;
        }

        $validated['slug'] = $candidate;
        $validated['is_filterable'] = (bool) ($validated['is_filterable'] ?? false);
        $validated['is_visible'] = (bool) ($validated['is_visible'] ?? false);
        $validated['enable_archives'] = (bool) ($validated['enable_archives'] ?? false);

        return $validated;
    }
}
