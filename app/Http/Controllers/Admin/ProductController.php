<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductExportLog;
use App\Models\ProductImage;
use App\Models\ProductImportHistory;
use App\Models\Vendor;
use App\Services\ImageOptimizationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'vendor', 'brand']);

        // Search (name, sku, description)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('sku', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%');
            });
        }

        // Category filter
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Brand filter
        if ($request->filled('brand_id')) {
            $query->where('brand_id', (int) $request->brand_id);
        }

        // Vendor filter
        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }

        // Price range
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Stock status filter (supports both old and new parameter names)
        $stockStatus = $request->filled('stock_status') ? $request->stock_status : $request->stock_filter;
        if ($stockStatus) {
            switch ($stockStatus) {
                case 'in_stock':
                    $query->where('stock_quantity', '>', 0);
                    break;
                case 'out_of_stock':
                    $query->where('stock_quantity', '<=', 0);
                    break;
                case 'low_stock':
                    $query->where('stock_quantity', '>', 0)
                        ->whereColumn('stock_quantity', '<=', 'low_stock_threshold');
                    break;
            }
        }

        // Status filter (is_active)
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Sorting
        $sort = $request->get('sort', 'date_desc');
        switch ($sort) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'date_asc':
                $query->orderBy('created_at', 'asc');
                break;
            case 'date_desc':
                $query->orderBy('created_at', 'desc');
                break;
            case 'popularity':
                $query->orderBy('sales_count', 'desc');
                break;
            case 'stock_asc':
                $query->orderBy('stock_quantity', 'asc');
                break;
            case 'stock_desc':
                $query->orderBy('stock_quantity', 'desc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }

        $products = $query->paginate(20)->appends(request()->query());

        // Load filter options for the view
        $categories = Category::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $brands = Brand::orderBy('name')->get(['id', 'name']);
        $vendors = Vendor::where('status', 'approved')
            ->orderBy('business_name')
            ->get(['id', DB::raw('business_name as name')]);

        return view('admin.products.index', compact('products', 'categories', 'brands', 'vendors'));
    }

    public function stockManagement(Request $request)
    {
        $filter = $request->input('filter', 'all');

        $query = Product::with(['category', 'vendor', 'brand']);

        switch ($filter) {
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

        if ($request->filled('brand_id')) {
            $query->where('brand_id', (int) $request->brand_id);
        }

        $products = $query->latest()->paginate(20)->appends(request()->query());

        $summary = [
            'all' => Product::count(),
            'in_stock' => Product::where('stock_quantity', '>', 0)->count(),
            'out_of_stock' => Product::where('stock_quantity', '<=', 0)->count(),
            'low_stock' => Product::whereColumn('stock_quantity', '<=', 'low_stock_threshold')->count(),
        ];

        return view('admin.products.stock-management', compact('products', 'summary', 'filter'));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->with('parent')->orderBy('name')->get();
        $vendors = Vendor::where('status', 'approved')->get();
        $brands = Brand::with('vendor')->orderBy('name')->get(['id', 'vendor_id', 'name']);

        return view('admin.products.create', compact('categories', 'vendors', 'brands'));
    }

    public function store(Request $request)
    {
        $vendorId = $request->input('vendor_id');
        $brandExistsRule = Rule::exists('brands', 'id')->where(function ($query) use ($vendorId) {
            if (empty($vendorId)) {
                $query->whereNull('vendor_id');

                return;
            }

            $vendorId = (int) $vendorId;
            $query->where(function ($q) use ($vendorId) {
                $q->whereNull('vendor_id')->orWhere('vendor_id', $vendorId);
            });
        });

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'brand_id' => ['nullable', 'integer', $brandExistsRule],
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
        $product->load(['category', 'vendor', 'brand', 'images', 'variants']);

        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = Category::where('is_active', true)->with('parent')->orderBy('name')->get();
        $vendors = Vendor::where('status', 'approved')->get();
        $brands = Brand::with('vendor')->orderBy('name')->get(['id', 'vendor_id', 'name']);

        return view('admin.products.edit', compact('product', 'categories', 'vendors', 'brands'));
    }

    public function update(Request $request, Product $product)
    {
        $vendorId = $request->input('vendor_id');
        $brandExistsRule = Rule::exists('brands', 'id')->where(function ($query) use ($vendorId) {
            if (empty($vendorId)) {
                $query->whereNull('vendor_id');

                return;
            }

            $vendorId = (int) $vendorId;
            $query->where(function ($q) use ($vendorId) {
                $q->whereNull('vendor_id')->orWhere('vendor_id', $vendorId);
            });
        });

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'brand_id' => ['nullable', 'integer', $brandExistsRule],
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
        $imageService = app(ImageOptimizationService::class);
        foreach ($product->images as $image) {
            $imageService->deleteImage($image->image_path);
            $image->delete();
        }

        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully');
    }

    public function deleteImage(ProductImage $image)
    {
        app(ImageOptimizationService::class)->deleteImage($image->image_path);
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

    public function replaceImage(Request $request, ProductImage $image)
    {
        $validated = $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp,avif|max:2048',
        ]);

        $product = $image->product;
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

    /**
     * Show the import form
     */
    public function import()
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $vendors = Vendor::where('status', 'approved')->get();
        $recentImports = ProductImportHistory::with('user')->latest()->limit(10)->get();

        return view('admin.products.import', compact('categories', 'vendors', 'recentImports'));
    }

    /**
     * Process the imported products
     */
    public function processImport(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx|max:10240', // 10MB max
            'update_existing' => 'nullable|boolean',
            'category_id' => 'nullable|exists:categories,id',
            'vendor_id' => 'nullable|exists:vendors,id',
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $path = $file->store('imports/products');

        // Create import history record
        $importHistory = ProductImportHistory::create([
            'file_name' => $originalName,
            'file_path' => $path,
            'status' => 'processing',
            'user_id' => auth()->id(),
        ]);

        try {
            // Read the CSV file
            $fullPath = storage_path('app/'.$path);
            $data = $this->readCsvFile($fullPath);

            if (empty($data)) {
                $importHistory->update([
                    'status' => 'failed',
                    'errors' => json_encode(['The file is empty or could not be read']),
                ]);

                return redirect()->route('admin.products.import')
                    ->with('error', 'The file is empty or could not be read');
            }

            $totalRows = count($data);
            $importHistory->update(['total_rows' => $totalRows]);

            // Validate headers
            $requiredHeaders = ['name', 'price', 'stock_quantity'];
            $headers = array_keys($data[0]);
            $missingHeaders = array_diff($requiredHeaders, $headers);

            if (! empty($missingHeaders)) {
                $importHistory->update([
                    'status' => 'failed',
                    'errors' => json_encode(['Missing required columns: '.implode(', ', $missingHeaders)]),
                ]);

                return redirect()->route('admin.products.import')
                    ->with('error', 'Missing required columns: '.implode(', ', $missingHeaders));
            }

            // Get existing categories and vendors for validation
            $categories = Category::pluck('id', 'name')->toArray();
            $vendors = Vendor::pluck('id', 'business_name')->toArray();

            // Get all existing SKUs for update check
            $existingSkus = Product::pluck('sku')->toArray();

            $successfulRows = 0;
            $failedRows = 0;
            $validationErrors = [];
            $errors = [];

            DB::beginTransaction();

            foreach ($data as $index => $row) {
                try {
                    $rowNumber = $index + 2; // +2 because of 0-index and header row
                    $validationError = $this->validateProductRow($row, $categories, $vendors);

                    if (! empty($validationError)) {
                        $validationErrors[] = [
                            'row' => $rowNumber,
                            'data' => $row,
                            'errors' => $validationError,
                        ];
                        $failedRows++;

                        continue;
                    }

                    $productData = $this->prepareProductData($row, $validated);

                    // Check if product exists by SKU
                    $sku = $row['sku'] ?? null;
                    if ($sku && in_array($sku, $existingSkus) && $request->boolean('update_existing')) {
                        $product = Product::where('sku', $sku)->first();
                        $product->update($productData);
                    } else {
                        $product = Product::create($productData);
                        $existingSkus[] = $product->sku;
                    }

                    $successfulRows++;
                    $importHistory->update(['processed_rows' => $successfulRows + $failedRows]);

                } catch (\Exception $e) {
                    $errors[] = [
                        'row' => $index + 2,
                        'data' => $row,
                        'error' => $e->getMessage(),
                    ];
                    $failedRows++;
                    Log::error('Product import error', [
                        'row' => $index + 2,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            DB::commit();

            $importHistory->update([
                'status' => $failedRows > 0 && $successfulRows === 0 ? 'failed' : 'completed',
                'successful_rows' => $successfulRows,
                'failed_rows' => $failedRows,
                'validation_errors' => json_encode($validationErrors),
                'errors' => json_encode($errors),
            ]);

            $message = "Import completed: {$successfulRows} products imported, {$failedRows} failed.";
            if ($failedRows > 0 && $successfulRows > 0) {
                return redirect()->route('admin.products.import')
                    ->with('warning', $message);
            } elseif ($failedRows > 0) {
                return redirect()->route('admin.products.import')
                    ->with('error', $message);
            }

            return redirect()->route('admin.products.import')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();

            $importHistory->update([
                'status' => 'failed',
                'errors' => json_encode([$e->getMessage()]),
            ]);

            Log::error('Product import failed', ['error' => $e->getMessage()]);

            return redirect()->route('admin.products.import')
                ->with('error', 'Import failed: '.$e->getMessage());
        }
    }

    /**
     * Read CSV file and return array
     */
    private function readCsvFile(string $path): array
    {
        $data = [];
        $handle = fopen($path, 'r');

        if ($handle === false) {
            return $data;
        }

        // Read header row
        $headers = fgetcsv($handle);
        $headers = array_map('trim', $headers);

        // Read data rows
        while (($row = fgetcsv($handle)) !== false) {
            $row = array_map('trim', $row);
            $data[] = array_combine($headers, $row);
        }

        fclose($handle);

        return $data;
    }

    /**
     * Validate a product row
     */
    private function validateProductRow(array $row, array $categories, array $vendors): array
    {
        $errors = [];

        // Required fields
        if (empty($row['name'])) {
            $errors[] = 'Name is required';
        }

        if (empty($row['price']) && ! is_numeric($row['price'])) {
            $errors[] = 'Valid price is required';
        }

        if (! isset($row['stock_quantity']) || ! is_numeric($row['stock_quantity'])) {
            $errors[] = 'Valid stock quantity is required';
        }

        // Validate category if provided
        if (! empty($row['category']) && ! isset($categories[strtolower($row['category'])])) {
            $errors[] = 'Invalid category';
        }

        // Validate vendor if provided
        if (! empty($row['vendor']) && ! isset($vendors[strtolower($row['vendor'])])) {
            $errors[] = 'Invalid vendor';
        }

        // Validate numeric fields
        if (! empty($row['price']) && ! is_numeric($row['price'])) {
            $errors[] = 'Price must be a number';
        }

        if (! empty($row['compare_price']) && ! is_numeric($row['compare_price'])) {
            $errors[] = 'Compare price must be a number';
        }

        if (! empty($row['cost_price']) && ! is_numeric($row['cost_price'])) {
            $errors[] = 'Cost price must be a number';
        }

        if (! empty($row['weight']) && ! is_numeric($row['weight'])) {
            $errors[] = 'Weight must be a number';
        }

        return $errors;
    }

    /**
     * Prepare product data from row
     */
    private function prepareProductData(array $row, array $validated): array
    {
        $categories = Category::pluck('id', 'name')->toArray();
        $vendors = Vendor::pluck('id', 'business_name')->toArray();

        $data = [
            'name' => $row['name'],
            'slug' => Str::slug($row['name']),
            'price' => (float) $row['price'],
            'stock_quantity' => (int) $row['stock_quantity'],
            'description' => $row['description'] ?? '',
            'short_description' => $row['short_description'] ?? null,
            'compare_price' => ! empty($row['compare_price']) ? (float) $row['compare_price'] : null,
            'cost_price' => ! empty($row['cost_price']) ? (float) $row['cost_price'] : null,
            'weight' => ! empty($row['weight']) ? (float) $row['weight'] : null,
            'origin_country' => $row['origin_country'] ?? null,
            'is_featured' => isset($row['is_featured']) && strtolower($row['is_featured']) === 'true',
            'is_active' => ! isset($row['is_active']) || strtolower($row['is_active']) !== 'false',
            'published_at' => now(),
        ];

        // Handle SKU
        if (! empty($row['sku'])) {
            $data['sku'] = $row['sku'];
        } else {
            $data['sku'] = 'PRD-'.strtoupper(Str::random(8));
        }

        // Handle category
        if (! empty($row['category']) && isset($categories[strtolower($row['category'])])) {
            $data['category_id'] = $categories[strtolower($row['category'])];
        } elseif (! empty($validated['category_id'])) {
            $data['category_id'] = $validated['category_id'];
        }

        // Handle vendor
        if (! empty($row['vendor']) && isset($vendors[strtolower($row['vendor'])])) {
            $data['vendor_id'] = $vendors[strtolower($row['vendor'])];
        } elseif (! empty($validated['vendor_id'])) {
            $data['vendor_id'] = $validated['vendor_id'];
        }

        return $data;
    }

    /**
     * Show the export form
     */
    public function export(Request $request)
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $vendors = Vendor::where('status', 'approved')->get();
        $recentExports = ProductExportLog::with('user')->latest()->limit(10)->get();

        return view('admin.products.export', compact('categories', 'vendors', 'recentExports'));
    }

    /**
     * Process the export
     */
    public function processExport(Request $request): StreamedResponse
    {
        $validated = $request->validate([
            'format' => 'required|in:csv',
            'category_id' => 'nullable|exists:categories,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'status' => 'nullable|in:all,in_stock,out_of_stock,low_stock',
            'is_active' => 'nullable|boolean',
            'columns' => 'nullable|array',
        ]);

        // Create export log
        $exportLog = ProductExportLog::create([
            'file_name' => 'products_export_'.now()->format('Y_m_d_His').'.csv',
            'status' => 'processing',
            'format' => $validated['format'],
            'user_id' => auth()->id(),
            'category_id' => $validated['category_id'] ?? null,
            'vendor_id' => $validated['vendor_id'] ?? null,
            'filters' => [
                'status' => $validated['status'] ?? 'all',
                'is_active' => $validated['is_active'] ?? null,
            ],
            'columns' => $validated['columns'] ?? null,
        ]);

        // Build query
        $query = Product::with(['category', 'vendor']);

        if (! empty($validated['category_id'])) {
            $query->where('category_id', $validated['category_id']);
        }

        if (! empty($validated['vendor_id'])) {
            $query->where('vendor_id', $validated['vendor_id']);
        }

        if (isset($validated['is_active'])) {
            $query->where('is_active', $validated['is_active']);
        }

        if (! empty($validated['status'])) {
            switch ($validated['status']) {
                case 'in_stock':
                    $query->where('stock_quantity', '>', 0);
                    break;
                case 'out_of_stock':
                    $query->where('stock_quantity', '<=', 0);
                    break;
                case 'low_stock':
                    $query->whereColumn('stock_quantity', '<=', 'low_stock_threshold');
                    break;
            }
        }

        $products = $query->get();
        $totalRows = $products->count();

        $exportLog->update(['total_rows' => $totalRows]);

        // Define columns to export
        $defaultColumns = [
            'id', 'sku', 'name', 'slug', 'description', 'short_description',
            'price', 'compare_price', 'cost_price', 'stock_quantity', 'low_stock_threshold',
            'stock_status', 'weight', 'origin_country', 'is_featured', 'is_active',
            'views_count', 'sales_count', 'average_rating', 'reviews_count',
            'category', 'vendor', 'created_at', 'updated_at',
        ];

        $columns = $validated['columns'] ?? $defaultColumns;

        // Create CSV response
        $fileName = $exportLog->file_name;

        $response = new StreamedResponse(function () use ($products, $columns) {
            $handle = fopen('php://output', 'w');

            // Add BOM for Excel to properly handle UTF-8
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Header row
            fputcsv($handle, $columns);

            // Data rows
            foreach ($products as $product) {
                $row = [];
                foreach ($columns as $column) {
                    switch ($column) {
                        case 'category':
                            $row[] = $product->category?->name ?? '';
                            break;
                        case 'vendor':
                            $row[] = $product->vendor?->name ?? '';
                            break;
                        case 'created_at':
                        case 'updated_at':
                            $row[] = $product->$column ? $product->$column->format('Y-m-d H:i:s') : '';
                            break;
                        default:
                            $row[] = $product->$column ?? '';
                    }
                }
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);

        // Update export log
        $exportLog->update([
            'status' => 'completed',
            'exported_rows' => $totalRows,
        ]);

        return $response;
    }
}
