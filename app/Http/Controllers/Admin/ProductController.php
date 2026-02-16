<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Vendor;
use App\Services\ImageOptimizationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['category', 'vendor'])
            ->latest()
            ->paginate(20);

        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->whereNotNull('parent_id')->get();
        $vendors = Vendor::where('status', 'approved')->get();

        return view('admin.products.create', compact('categories', 'vendors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'description' => 'required|string',
            'short_description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'compare_price' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'weight' => 'nullable|numeric|min:0',
            'origin_country' => 'nullable|string|max:255',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['sku'] = 'PRD-'.strtoupper(Str::random(8));
        $validated['published_at'] = now();

        $product = Product::create($validated);

        if ($request->hasFile('images')) {
            $this->uploadProductImages($product, $request->file('images'));
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully');
    }

    public function show(Product $product)
    {
        $product->load(['category', 'vendor', 'images', 'variants']);

        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = Category::where('is_active', true)->whereNotNull('parent_id')->get();
        $vendors = Vendor::where('status', 'approved')->get();

        return view('admin.products.edit', compact('product', 'categories', 'vendors'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'description' => 'required|string',
            'short_description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'compare_price' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'weight' => 'nullable|numeric|min:0',
            'origin_country' => 'nullable|string|max:255',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $product->update($validated);

        if ($request->hasFile('images')) {
            $this->uploadProductImages($product, $request->file('images'));
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Product updated successfully');
    }

    public function destroy(Product $product)
    {
        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->image_path);
            $image->delete();
        }

        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully');
    }

    public function deleteImage(ProductImage $image)
    {
        Storage::disk('public')->delete($image->image_path);
        $image->delete();

        return redirect()->back()->with('success', 'Image deleted successfully');
    }

    public function setPrimaryImage(ProductImage $image)
    {
        $image->product->images()->update(['is_primary' => false]);
        $image->update(['is_primary' => true]);

        $image->product->update(['featured_image' => $image->image_path]);

        return redirect()->back()->with('success', 'Primary image updated successfully');
    }

    private function uploadProductImages(Product $product, array $images)
    {
        $sortOrder = $product->images()->count();
        $imageService = app(ImageOptimizationService::class);

        foreach ($images as $image) {
            $path = 'products/'.$product->id;
            $result = $imageService->uploadAndOptimize($image, $path, 800, 800);

            $productImage = $product->images()->create([
                'image_path' => $result['original'],
                'alt_text' => $product->name,
                'sort_order' => $sortOrder,
                'is_primary' => $product->images()->count() === 0,
            ]);

            if ($productImage->is_primary) {
                $product->update(['featured_image' => $productImage->image_path]);
            }

            $sortOrder++;
        }
    }
}
