<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Services\ImageOptimizationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductVariantController extends Controller
{
    public function index(Product $product): View
    {
        $variants = $product->variations()->latest()->get();

        return view('admin.product-variants.index', compact('product', 'variants'));
    }

    public function create(Product $product): View
    {
        return view('admin.product-variants.create', compact('product'));
    }

    public function store(Request $request, Product $product, ImageOptimizationService $imageService)
    {
        $validated = $request->validate([
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
            'is_active' => ['boolean'],
        ]);

        DB::beginTransaction();

        try {
            $attributes = $this->mapAttributes($validated['attributes']);
            $dimensions = $this->mapDimensions($request);

            $variant = $product->variations()->create([
                'sku' => $validated['sku'] ?? $this->generateSku($product, $attributes),
                'attributes' => $attributes,
                'price' => $validated['price'],
                'compare_at_price' => $validated['compare_at_price'] ?? null,
                'stock_quantity' => $validated['stock_quantity'],
                'low_stock_threshold' => $validated['low_stock_threshold'] ?? 5,
                'weight' => $validated['weight'] ?? null,
                'dimensions' => $dimensions,
                'is_active' => $request->boolean('is_active', true),
            ]);

            if ($request->hasFile('image')) {
                $result = $imageService->uploadAndOptimize(
                    $request->file('image'),
                    'products/variations',
                    800,
                    800,
                    'product_variation',
                    $variant->id
                );

                $variant->update(['image' => $result['original']]);
            }

            DB::commit();

            return redirect()->route('admin.products.variants.index', $product)
                ->with('success', 'Variant created successfully');
        } catch (\Throwable $exception) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Failed to create variant.'])->withInput();
        }
    }

    public function edit(Product $product, ProductVariation $variant): View
    {
        return view('admin.product-variants.edit', compact('product', 'variant'));
    }

    public function update(Request $request, Product $product, ProductVariation $variant, ImageOptimizationService $imageService)
    {
        $validated = $request->validate([
            'attributes' => ['required', 'array'],
            'attributes.*.name' => ['required', 'string', 'max:255'],
            'attributes.*.value' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'compare_at_price' => ['nullable', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'low_stock_threshold' => ['nullable', 'integer', 'min:0'],
            'sku' => ['nullable', 'string', 'max:255', 'unique:product_variations,sku,'.$variant->id],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'dimensions' => ['nullable', 'array'],
            'dimensions.length' => ['nullable', 'numeric', 'min:0'],
            'dimensions.width' => ['nullable', 'numeric', 'min:0'],
            'dimensions.height' => ['nullable', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'max:2048'],
            'is_active' => ['boolean'],
        ]);

        DB::beginTransaction();

        try {
            $attributes = $this->mapAttributes($validated['attributes']);
            $dimensions = $this->mapDimensions($request);

            $variant->update([
                'sku' => $validated['sku'] ?? $variant->sku,
                'attributes' => $attributes,
                'price' => $validated['price'],
                'compare_at_price' => $validated['compare_at_price'] ?? null,
                'stock_quantity' => $validated['stock_quantity'],
                'low_stock_threshold' => $validated['low_stock_threshold'] ?? 5,
                'weight' => $validated['weight'] ?? null,
                'dimensions' => $dimensions,
                'is_active' => $request->boolean('is_active', true),
            ]);

            if ($request->hasFile('image')) {
                if ($variant->image) {
                    $imageService->deleteImage($variant->image);
                }

                $result = $imageService->uploadAndOptimize(
                    $request->file('image'),
                    'products/variations',
                    800,
                    800,
                    'product_variation',
                    $variant->id
                );

                $variant->update(['image' => $result['original']]);
            }

            DB::commit();

            return redirect()->route('admin.products.variants.index', $product)
                ->with('success', 'Variant updated successfully');
        } catch (\Throwable $exception) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Failed to update variant.'])->withInput();
        }
    }

    public function destroy(Product $product, ProductVariation $variant, ImageOptimizationService $imageService)
    {
        if ($variant->image) {
            $imageService->deleteImage($variant->image);
        }

        $variant->delete();

        return redirect()->route('admin.products.variants.index', $product)
            ->with('success', 'Variant deleted successfully');
    }

    private function mapAttributes(array $rawAttributes): array
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

    private function mapDimensions(Request $request): ?array
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

    private function generateSku(Product $product, array $attributes): string
    {
        $base = strtoupper(Str::substr($product->slug, 0, 3));
        $suffix = strtoupper(implode('-', array_map(fn ($value) => Str::substr((string) $value, 0, 2), array_values($attributes))));

        return trim($base.'-'.$suffix.'-'.strtoupper(Str::substr(uniqid(), -4)), '-');
    }
}
