<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductTag;
use App\Models\ProductVariation;
use App\Models\Vendor;
use App\Services\ImageOptimizationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductCatalogController extends Controller
{
    public function variations()
    {
        $variations = ProductVariation::with('product')
            ->latest()
            ->paginate(25);

        return view('admin.products.variations', compact('variations'));
    }

    public function createVariation()
    {
        $products = Product::orderBy('name')->get(['id', 'name']);

        return view('admin.products.variation-create', compact('products'));
    }

    public function storeVariation(Request $request, ImageOptimizationService $imageService)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'attributes' => ['required', 'array'],
            'attributes.*.name' => ['required', 'string', 'max:255'],
            'attributes.*.value' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'compare_at_price' => ['nullable', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'low_stock_threshold' => ['nullable', 'integer', 'min:0'],
            'sku' => ['nullable', 'string', 'max:255', 'unique:product_variations,sku'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'dimensions' => ['nullable', 'array'],
            'dimensions.length' => ['nullable', 'numeric', 'min:0'],
            'dimensions.width' => ['nullable', 'numeric', 'min:0'],
            'dimensions.height' => ['nullable', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'max:2048'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $product = Product::findOrFail((int) $validated['product_id']);
        $attributes = $this->mapVariationAttributes($validated['attributes']);

        $variation = ProductVariation::create([
            'product_id' => $product->id,
            'sku' => $validated['sku'] ?? $this->generateVariationSku($product, $attributes),
            'attributes' => $attributes,
            'price' => $validated['price'],
            'compare_at_price' => $validated['compare_at_price'] ?? null,
            'stock_quantity' => $validated['stock_quantity'],
            'low_stock_threshold' => $validated['low_stock_threshold'] ?? 5,
            'weight' => $validated['weight'] ?? null,
            'dimensions' => $this->mapVariationDimensions($request),
            'is_active' => $request->boolean('is_active', true),
        ]);

        if ($request->hasFile('image')) {
            $result = $imageService->uploadAndOptimize(
                $request->file('image'),
                'products/variations',
                800,
                800,
                'product_variation',
                $variation->id
            );

            $variation->update(['image' => $result['original']]);
        }

        return redirect()
            ->route('admin.products.variants.index', $product)
            ->with('success', 'Variation created successfully.');
    }

    public function brands(Request $request)
    {
        $brandsQuery = Brand::with(['vendor'])
            ->withCount('products')
            ->latest();

        if ($request->filled('vendor_scope')) {
            if ($request->string('vendor_scope') === 'global') {
                $brandsQuery->whereNull('vendor_id');
            } elseif ($request->string('vendor_scope') === 'vendor') {
                $brandsQuery->whereNotNull('vendor_id');
            }
        }

        if ($request->filled('vendor_id')) {
            $brandsQuery->where('vendor_id', (int) $request->input('vendor_id'));
        }

        $brands = $brandsQuery->paginate(25)->withQueryString();
        $vendors = Vendor::orderBy('business_name')->get(['id', 'business_name']);

        return view('admin.products.brands', compact('brands', 'vendors'));
    }

    public function createBrand()
    {
        return view('admin.products.brands-create');
    }

    public function storeBrand(Request $request, ImageOptimizationService $imageService)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:brands,slug'],
            'description' => ['nullable', 'string'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'website' => ['nullable', 'url', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['slug'] = $this->resolveBrandSlug($validated['slug'] ?? null, $validated['name']);
        $validated['is_active'] = $request->boolean('is_active');

        $logoFile = $request->file('logo');
        unset($validated['logo']);

        $brand = Brand::create($validated);

        if ($logoFile && $logoFile->isValid()) {
            $result = $imageService->uploadAndOptimize(
                $logoFile,
                'brands',
                600,
                600,
                'brand',
                $brand->id
            );
            $brand->update(['logo' => $result['original']]);
        }

        return redirect()->route('admin.products.brands.index')->with('success', 'Brand created successfully.');
    }

    public function editBrand(Brand $brand)
    {
        return view('admin.products.brands-edit', compact('brand'));
    }

    public function updateBrand(Request $request, Brand $brand, ImageOptimizationService $imageService)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('brands', 'slug')->ignore($brand->id)],
            'description' => ['nullable', 'string'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'website' => ['nullable', 'url', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['slug'] = $this->resolveBrandSlug($validated['slug'] ?? null, $validated['name'], $brand->id);
        $validated['is_active'] = $request->boolean('is_active');

        $logoFile = $request->file('logo');
        unset($validated['logo']);

        $brand->update($validated);

        if ($logoFile && $logoFile->isValid()) {
            if ($brand->logo) {
                $imageService->deleteImage($brand->logo);
            }

            $result = $imageService->uploadAndOptimize(
                $logoFile,
                'brands',
                600,
                600,
                'brand',
                $brand->id
            );

            $brand->update(['logo' => $result['original']]);
        }

        return redirect()->route('admin.products.brands.index')->with('success', 'Brand updated successfully.');
    }

    public function destroyBrand(Brand $brand, ImageOptimizationService $imageService)
    {
        Product::where('brand_id', $brand->id)->update(['brand_id' => null]);

        if ($brand->logo) {
            $imageService->deleteImage($brand->logo);
        }
        $brand->delete();

        return redirect()->route('admin.products.brands.index')->with('success', 'Brand deleted successfully.');
    }

    public function tags()
    {
        $tags = ProductTag::withCount('products')
            ->latest()
            ->paginate(25);

        return view('admin.products.tags', compact('tags'));
    }

    public function createTag()
    {
        return view('admin.products.tags-create');
    }

    public function storeTag(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:product_tags,slug'],
            'color' => ['nullable', 'string', 'max:7'],
        ]);

        $validated['slug'] = $this->resolveTagSlug($validated['slug'] ?? null, $validated['name']);
        $validated['color'] = $validated['color'] ?: '#3b82f6';

        ProductTag::create($validated);

        return redirect()->route('admin.products.tags.index')->with('success', 'Tag created successfully.');
    }

    public function editTag(ProductTag $tag)
    {
        return view('admin.products.tags-edit', compact('tag'));
    }

    public function updateTag(Request $request, ProductTag $tag)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('product_tags', 'slug')->ignore($tag->id)],
            'color' => ['nullable', 'string', 'max:7'],
        ]);

        $validated['slug'] = $this->resolveTagSlug($validated['slug'] ?? null, $validated['name'], $tag->id);
        $validated['color'] = $validated['color'] ?: '#3b82f6';

        $tag->update($validated);

        return redirect()->route('admin.products.tags.index')->with('success', 'Tag updated successfully.');
    }

    public function destroyTag(ProductTag $tag)
    {
        $tag->delete();

        return redirect()->route('admin.products.tags.index')->with('success', 'Tag deleted successfully.');
    }

    private function resolveBrandSlug(?string $slug, string $name, ?int $ignoreId = null): string
    {
        return $this->resolveUniqueSlug(Brand::query(), $slug ?: $name, $ignoreId);
    }

    private function resolveTagSlug(?string $slug, string $name, ?int $ignoreId = null): string
    {
        return $this->resolveUniqueSlug(ProductTag::query(), $slug ?: $name, $ignoreId);
    }

    private function resolveUniqueSlug(Builder $query, string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value);
        $candidate = $base;
        $counter = 1;

        while ($query
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->where('slug', $candidate)
            ->exists()) {
            $candidate = $base.'-'.$counter;
            $counter++;
        }

        return $candidate;
    }

    private function mapVariationAttributes(array $rawAttributes): array
    {
        $attributes = [];
        foreach ($rawAttributes as $attribute) {
            $name = trim((string) ($attribute['name'] ?? ''));
            $value = trim((string) ($attribute['value'] ?? ''));

            if ($name !== '' && $value !== '') {
                $attributes[$name] = $value;
            }
        }

        return $attributes;
    }

    private function mapVariationDimensions(Request $request): ?array
    {
        if (! $request->filled('dimensions.length') && ! $request->filled('dimensions.width') && ! $request->filled('dimensions.height')) {
            return null;
        }

        return [
            'length' => $request->input('dimensions.length'),
            'width' => $request->input('dimensions.width'),
            'height' => $request->input('dimensions.height'),
            'unit' => $request->input('dimensions.unit', 'cm'),
        ];
    }

    private function generateVariationSku(Product $product, array $attributes): string
    {
        $base = strtoupper(Str::substr($product->slug, 0, 3));
        $suffix = strtoupper(implode('-', array_map(fn ($value) => Str::substr((string) $value, 0, 2), array_values($attributes))));

        return trim($base.'-'.$suffix.'-'.strtoupper(Str::substr(uniqid(), -4)), '-');
    }
}
