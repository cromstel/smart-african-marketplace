<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductAttribute;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductAttributeController extends Controller
{
    public function index(Request $request)
    {
        $query = ProductAttribute::query()->with(['vendor.user']);

        $scope = (string) $request->string('scope', 'all');
        if ($scope === 'global') {
            $query->whereNull('vendor_id');
        } elseif ($scope === 'vendor') {
            $query->whereNotNull('vendor_id');
        }

        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', (int) $request->integer('vendor_id'));
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->string('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $attributes = $query->orderBy('name')->paginate(20)->withQueryString();
        $vendors = Vendor::query()->with('user')->orderBy('business_name')->get(['id', 'user_id', 'business_name']);

        return view('admin.products.attributes.index', [
            'attributes' => $attributes,
            'vendors' => $vendors,
            'filters' => $request->only(['scope', 'vendor_id', 'search']),
        ]);
    }

    public function create()
    {
        return view('admin.products.attributes.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateAttribute($request);
        $validated['vendor_id'] = null;

        ProductAttribute::create($validated);

        return redirect()->route('admin.products.attributes.index')->with('success', 'Attribute created successfully.');
    }

    public function edit(ProductAttribute $attribute)
    {
        return view('admin.products.attributes.edit', compact('attribute'));
    }

    public function update(Request $request, ProductAttribute $attribute)
    {
        $validated = $this->validateAttribute($request, $attribute);

        $attribute->update($validated);

        return redirect()->route('admin.products.attributes.index')->with('success', 'Attribute updated successfully.');
    }

    public function destroy(ProductAttribute $attribute)
    {
        $attribute->delete();

        return redirect()->route('admin.products.attributes.index')->with('success', 'Attribute deleted successfully.');
    }

    private function validateAttribute(Request $request, ?ProductAttribute $attribute = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:28', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'description' => ['nullable', 'string'],
            'type' => ['required', Rule::in(['text', 'select', 'color', 'boolean', 'checkbox'])],
            'values' => ['nullable', 'array'],
            'values.*' => ['string'],
            'is_filterable' => ['nullable', 'boolean'],
            'is_visible' => ['nullable', 'boolean'],
            'enable_archives' => ['nullable', 'boolean'],
            'default_sort_order' => ['required', Rule::in(['custom_ordering', 'name', 'name_num', 'term_id'])],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $validated['slug'] = $this->uniqueSlug(
            $validated['slug'] ?? $validated['name'],
            $attribute?->vendor_id,
            $attribute?->id
        );

        $validated['is_filterable'] = (bool) ($validated['is_filterable'] ?? false);
        $validated['is_visible'] = (bool) ($validated['is_visible'] ?? false);
        $validated['enable_archives'] = (bool) ($validated['enable_archives'] ?? false);

        return $validated;
    }

    private function uniqueSlug(string $value, ?int $vendorId, ?int $ignoreId = null): string
    {
        $base = Str::slug($value);
        $candidate = $base !== '' ? Str::limit($base, 28, '') : Str::random(8);

        $counter = 1;
        while (
            ProductAttribute::query()
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->where('vendor_id', $vendorId)
                ->where('slug', $candidate)
                ->exists()
        ) {
            $suffix = '-'.$counter;
            $stem = Str::limit($base, max(1, 28 - strlen($suffix)), '');
            $candidate = $stem.$suffix;
            $counter++;
        }

        return $candidate;
    }
}
