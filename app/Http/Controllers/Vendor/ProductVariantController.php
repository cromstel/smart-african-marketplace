<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Services\ImageOptimizationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProductVariantController extends Controller
{
    public function all(Request $request): View
    {
        $vendor = $request->user()->vendor;

        $variants = ProductVariation::query()
            ->whereHas('product', function ($query) use ($vendor) {
                $query->where('vendor_id', $vendor->id);
            })
            ->with('product')
            ->latest()
            ->paginate(20);

        return view('vendor.products.variants.index-all', compact('variants'));
    }

    public function index(Product $product): View
    {
        $this->authorize('update', $product);

        $variants = $product->variations()->paginate(20);

        return view('vendor.products.variants.index', compact('product', 'variants'));
    }

    public function create(Product $product): View
    {
        $this->authorize('update', $product);

        return view('vendor.products.variants.create', compact('product'));
    }

    public function store(Request $request, Product $product, ImageOptimizationService $imageService): RedirectResponse
    {
        $this->authorize('update', $product);

        $request->validate([
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
            $attributes = [];
            foreach ($request->attributes as $attr) {
                if (! empty($attr['name']) && ! empty($attr['value'])) {
                    $attributes[$attr['name']] = $attr['value'];
                }
            }

            $dimensions = null;
            if ($request->filled('dimensions.length') || $request->filled('dimensions.width') || $request->filled('dimensions.height')) {
                $dimensions = [
                    'length' => $request->input('dimensions.length'),
                    'width' => $request->input('dimensions.width'),
                    'height' => $request->input('dimensions.height'),
                    'unit' => $request->input('dimensions.unit', 'cm'),
                ];
            }

            $variant = $product->variations()->create([
                'sku' => $request->sku ?? $this->generateSku($product, $attributes),
                'attributes' => $attributes,
                'price' => $request->price,
                'compare_at_price' => $request->compare_at_price,
                'stock_quantity' => $request->stock_quantity,
                'low_stock_threshold' => $request->low_stock_threshold ?? 5,
                'weight' => $request->weight,
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

            return redirect()->route('vendor.products.variants.index', $product)
                ->with('success', 'Product variant created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'An error occurred while creating the variant.'])->withInput();
        }
    }

    public function edit(Product $product, ProductVariation $variant): View
    {
        $this->authorize('update', $product);

        return view('vendor.products.variants.edit', compact('product', 'variant'));
    }

    public function update(Request $request, Product $product, ProductVariation $variant, ImageOptimizationService $imageService): RedirectResponse
    {
        $this->authorize('update', $product);

        $request->validate([
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
            $attributes = [];
            foreach ($request->attributes as $attr) {
                if (! empty($attr['name']) && ! empty($attr['value'])) {
                    $attributes[$attr['name']] = $attr['value'];
                }
            }

            $dimensions = null;
            if ($request->filled('dimensions.length') || $request->filled('dimensions.width') || $request->filled('dimensions.height')) {
                $dimensions = [
                    'length' => $request->input('dimensions.length'),
                    'width' => $request->input('dimensions.width'),
                    'height' => $request->input('dimensions.height'),
                    'unit' => $request->input('dimensions.unit', 'cm'),
                ];
            }

            $variant->update([
                'sku' => $request->sku ?? $variant->sku,
                'attributes' => $attributes,
                'price' => $request->price,
                'compare_at_price' => $request->compare_at_price,
                'stock_quantity' => $request->stock_quantity,
                'low_stock_threshold' => $request->low_stock_threshold ?? 5,
                'weight' => $request->weight,
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

            return redirect()->route('vendor.products.variants.index', $product)
                ->with('success', 'Product variant updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'An error occurred while updating the variant.'])->withInput();
        }
    }

    public function destroy(Product $product, ProductVariation $variant, ImageOptimizationService $imageService): RedirectResponse
    {
        $this->authorize('update', $product);

        try {
            if ($variant->image) {
                $imageService->deleteImage($variant->image);
            }

            $variant->delete();

            return redirect()->route('vendor.products.variants.index', $product)
                ->with('success', 'Product variant deleted successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'An error occurred while deleting the variant.']);
        }
    }

    private function generateSku(Product $product, array $attributes): string
    {
        $base = strtoupper(substr($product->slug, 0, 3));
        $suffix = strtoupper(implode('-', array_map(fn ($v) => substr($v, 0, 2), array_values($attributes))));

        return $base.'-'.$suffix.'-'.strtoupper(substr(uniqid(), -4));
    }
}
