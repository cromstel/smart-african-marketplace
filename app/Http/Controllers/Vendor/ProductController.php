<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductTag;
use App\Models\ProductVariation;
use App\Services\AiContentGeneratorService;
use App\Services\ImageOptimizationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $vendor = auth()->user()->vendor;

        $query = $vendor->products()
            ->with('category')
            ->latest();

        if ($request->filled('inventory_filter')) {
            switch ($request->string('inventory_filter')) {
                case 'low_stock':
                    $query->whereColumn('stock_quantity', '<=', 'low_stock_threshold');
                    break;
                case 'out_of_stock':
                    $query->where('stock_quantity', '<=', 0);
                    break;
                case 'in_stock':
                    $query->where('stock_quantity', '>', 0);
                    break;
            }
        }

        if ($request->filled('stock_status')) {
            $query->where('stock_status', $request->string('stock_status'));
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', (int) $request->input('category_id'));
        }

        $products = $query->paginate(20)->withQueryString();

        return view('vendor.products.index', compact('products'));
    }

    public function inventory(Request $request)
    {
        $vendor = auth()->user()->vendor;
        $filter = $request->input('filter', 'all');

        $query = $vendor->products()->with('category')->latest();

        if ($filter === 'low_stock') {
            $query->whereColumn('stock_quantity', '<=', 'low_stock_threshold');
        } elseif ($filter === 'out_of_stock') {
            $query->where('stock_quantity', '<=', 0);
        } elseif ($filter === 'in_stock') {
            $query->where('stock_quantity', '>', 0);
        }

        $products = $query->paginate(20)->withQueryString();

        $summary = [
            'all' => $vendor->products()->count(),
            'in_stock' => $vendor->products()->where('stock_quantity', '>', 0)->count(),
            'out_of_stock' => $vendor->products()->where('stock_quantity', '<=', 0)->count(),
            'low_stock' => $vendor->products()->whereColumn('stock_quantity', '<=', 'low_stock_threshold')->count(),
            'total_variants' => ProductVariation::whereHas('product', function ($query) use ($vendor) {
                $query->where('vendor_id', $vendor->id);
            })->count(),
        ];

        return view('vendor.products.inventory', compact('products', 'summary', 'filter'));
    }

    public function create(Request $request)
    {
        $categories = Category::whereNull('parent_id')
            ->where('is_active', true)
            ->with(['children' => function ($query) {
                $query->where('is_active', true)->orderBy('name');
            }])
            ->orderBy('name')
            ->get();
        $vendorId = auth()->user()->vendor->id;
        $brands = Brand::active()
            ->where(function ($query) use ($vendorId) {
                $query->whereNull('vendor_id')->orWhere('vendor_id', $vendorId);
            })
            ->orderBy('name')
            ->get();
        $tags = ProductTag::whereNull('vendor_id')
            ->orderBy('name')
            ->get();
        $selectedCategoryId = $request->input('category_id');

        return view('vendor.products.create', compact('categories', 'brands', 'tags', 'selectedCategoryId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'short_description' => 'nullable|string|max:500',
            'description' => 'required|string',
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->where('is_active', true),
            ],
            'brand_id' => 'nullable|exists:brands,id',
            'price' => 'required|numeric|min:0',
            'compare_price' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'sku' => 'required|string|max:100|unique:products,sku',
            'stock_quantity' => 'required|integer|min:0',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'stock_status' => 'nullable|in:in_stock,out_of_stock,backorder',
            'weight' => 'nullable|numeric|min:0',
            'origin_country' => 'nullable|string|max:100',
            'ingredients' => 'nullable|string',
            'is_active' => 'boolean',
            'tags' => 'nullable|array',
            'tags.*' => Rule::exists('product_tags', 'id')->whereNull('vendor_id'),
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,avif|max:2048',
        ]);

        $vendor = auth()->user()->vendor;

        if (! $vendor->isApproved()) {
            return redirect()->back()->with('error', 'Your vendor account must be approved before adding products');
        }

        $validated['vendor_id'] = $vendor->id;
        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->has('is_active');
        $validated['low_stock_threshold'] = $request->input('low_stock_threshold', 10);
        $validated['stock_status'] = $request->input(
            'stock_status',
            $validated['stock_quantity'] > 0 ? 'in_stock' : 'out_of_stock'
        );

        $product = Product::create($validated);

        if ($request->has('tags')) {
            $product->tags()->sync($request->tags);
            foreach ($request->tags as $tagId) {
                ProductTag::find($tagId)->incrementUsage();
            }
        }

        if ($request->hasFile('images')) {
            $this->uploadProductImages($product, $request->file('images'));
        }

        return redirect()->route('vendor.products.index')
            ->with('success', 'Product created successfully');
    }

    public function edit(Product $product)
    {
        $this->authorize('update', $product);

        $categories = Category::whereNull('parent_id')
            ->where('is_active', true)
            ->with(['children' => function ($query) {
                $query->where('is_active', true)->orderBy('name');
            }])
            ->orderBy('name')
            ->get();
        $vendorId = auth()->user()->vendor->id;
        $brands = Brand::active()
            ->where(function ($query) use ($vendorId) {
                $query->whereNull('vendor_id')->orWhere('vendor_id', $vendorId);
            })
            ->orderBy('name')
            ->get();
        $tags = ProductTag::whereNull('vendor_id')
            ->orderBy('name')
            ->get();

        return view('vendor.products.edit', compact('product', 'categories', 'brands', 'tags'));
    }

    public function update(Request $request, Product $product)
    {
        $this->authorize('update', $product);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'short_description' => 'nullable|string|max:500',
            'description' => 'required|string',
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->where('is_active', true),
            ],
            'brand_id' => 'nullable|exists:brands,id',
            'price' => 'required|numeric|min:0',
            'compare_price' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'sku' => 'required|string|max:100|unique:products,sku,'.$product->id,
            'stock_quantity' => 'required|integer|min:0',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'stock_status' => 'nullable|in:in_stock,out_of_stock,backorder',
            'weight' => 'nullable|numeric|min:0',
            'origin_country' => 'nullable|string|max:100',
            'ingredients' => 'nullable|string',
            'is_active' => 'boolean',
            'tags' => 'nullable|array',
            'tags.*' => Rule::exists('product_tags', 'id')->whereNull('vendor_id'),
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,avif|max:2048',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->has('is_active');
        $validated['low_stock_threshold'] = $request->input('low_stock_threshold', $product->low_stock_threshold ?? 10);
        $validated['stock_status'] = $request->input(
            'stock_status',
            $validated['stock_quantity'] > 0 ? 'in_stock' : 'out_of_stock'
        );

        $product->update($validated);

        $oldTags = $product->tags->pluck('id')->toArray();
        $newTags = $request->tags ?? [];

        $product->tags()->sync($newTags);

        foreach (array_diff($oldTags, $newTags) as $removedTagId) {
            ProductTag::find($removedTagId)?->decrementUsage();
        }
        foreach (array_diff($newTags, $oldTags) as $addedTagId) {
            ProductTag::find($addedTagId)?->incrementUsage();
        }

        if ($request->hasFile('images')) {
            $this->uploadProductImages($product, $request->file('images'));
        }

        return redirect()->route('vendor.products.index')
            ->with('success', 'Product updated successfully');
    }

    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);

        $imageService = app(ImageOptimizationService::class);
        foreach ($product->images as $image) {
            $imageService->deleteImage($image->image_path);
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

        app(ImageOptimizationService::class)->deleteImage($image->image_path);
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

    public function replaceImage(Request $request, ProductImage $image)
    {
        $validated = $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp,avif|max:2048',
        ]);

        $product = $image->product;
        $this->authorize('update', $product);

        $oldPath = $image->image_path;
        $path = 'products/'.$product->id;
        $imageService = app(ImageOptimizationService::class);
        $result = $imageService->uploadAndOptimize($validated['image'], $path, 800, 800);

        $image->update([
            'image_path' => $result['original'],
        ]);

        if ($image->is_primary) {
            $product->update(['featured_image' => $image->image_path]);
        }

        $imageService->deleteImage($oldPath);

        return redirect()->back()->with('success', 'Image replaced successfully');
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

    public function generateContent(Request $request, AiContentGeneratorService $aiService)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:title,description,short_description,all',
            'category' => 'nullable|string',
            'brand' => 'nullable|string',
            'attributes' => 'nullable|array',
            'price' => 'nullable|numeric',
            'style' => 'nullable|in:premium,standard,sale,detailed,concise,technical,marketing',
        ]);

        $productData = [
            'name' => $request->name,
            'category' => $request->category,
            'brand' => $request->brand,
            'attributes' => $request->attributes ?? [],
            'price' => $request->price,
        ];

        $response = [];

        switch ($request->type) {
            case 'title':
                $style = $request->style ?? 'standard';
                $response['title'] = $aiService->generateTitle($productData, $style);
                break;

            case 'description':
                $style = $request->style ?? 'detailed';
                $response['description'] = $aiService->generateDescription($productData, $style);
                break;

            case 'short_description':
                $response['short_description'] = $aiService->generateShortDescription($productData);
                break;

            case 'all':
                $titleStyle = $request->input('title_style', 'standard');
                $descStyle = $request->input('desc_style', 'detailed');

                $response['title'] = $aiService->generateTitle($productData, $titleStyle);
                $response['description'] = $aiService->generateDescription($productData, $descStyle);
                $response['short_description'] = $aiService->generateShortDescription($productData);
                $response['keywords'] = $aiService->generateSeoKeywords($productData);
                break;
        }

        return response()->json([
            'success' => true,
            'data' => $response,
        ]);
    }
}
