<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\ImageOptimizationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index()
    {
        $vendor = auth()->user()->vendor;

        $products = $vendor->products()
            ->with('category')
            ->latest()
            ->paginate(20);

        return view('vendor.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::whereNull('parent_id')->with('children')->get();

        return view('vendor.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'short_description' => 'nullable|string|max:500',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'compare_price' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'sku' => 'required|string|max:100|unique:products,sku',
            'stock_quantity' => 'required|integer|min:0',
            'weight' => 'nullable|numeric|min:0',
            'origin_country' => 'nullable|string|max:100',
            'ingredients' => 'nullable|string',
            'is_active' => 'boolean',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $vendor = auth()->user()->vendor;

        if (! $vendor->isApproved()) {
            return redirect()->back()->with('error', 'Your vendor account must be approved before adding products');
        }

        $validated['vendor_id'] = $vendor->id;
        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->has('is_active');

        $product = Product::create($validated);

        if ($request->hasFile('images')) {
            $this->uploadProductImages($product, $request->file('images'));
        }

        return redirect()->route('vendor.products.index')
            ->with('success', 'Product created successfully');
    }

    public function edit(Product $product)
    {
        $this->authorize('update', $product);

        $categories = Category::whereNull('parent_id')->with('children')->get();

        return view('vendor.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $this->authorize('update', $product);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'short_description' => 'nullable|string|max:500',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'compare_price' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'sku' => 'required|string|max:100|unique:products,sku,'.$product->id,
            'stock_quantity' => 'required|integer|min:0',
            'weight' => 'nullable|numeric|min:0',
            'origin_country' => 'nullable|string|max:100',
            'ingredients' => 'nullable|string',
            'is_active' => 'boolean',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->has('is_active');

        $product->update($validated);

        if ($request->hasFile('images')) {
            $this->uploadProductImages($product, $request->file('images'));
        }

        return redirect()->route('vendor.products.index')
            ->with('success', 'Product updated successfully');
    }

    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);

        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->image_path);
            $image->delete();
        }

        $product->delete();

        return redirect()->route('vendor.products.index')
            ->with('success', 'Product deleted successfully');
    }

    public function deleteImage(ProductImage $image)
    {
        $product = $image->product;
        $this->authorize('update', $product);

        Storage::disk('public')->delete($image->image_path);
        $image->delete();

        return redirect()->back()->with('success', 'Image deleted successfully');
    }

    public function setPrimaryImage(ProductImage $image)
    {
        $product = $image->product;
        $this->authorize('update', $product);

        $product->images()->update(['is_primary' => false]);
        $image->update(['is_primary' => true]);
        $product->update(['featured_image' => $image->image_path]);

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
