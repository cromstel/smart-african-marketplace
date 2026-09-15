<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Resources\ProductResource;
use App\Models\Brand;
use App\Models\Product;
use Illuminate\Http\Request;

class BrandProductController extends Controller
{
    use ApiResponse;

    public function index(Request $request, Brand $brand)
    {
        if (! $brand->is_active) {
            return $this->errorResponse('Brand not found', 404);
        }

        $query = Product::with(['category', 'images'])
            ->active()
            ->published()
            ->where('brand_id', $brand->id);

        // Category filter
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Price range
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Stock status filter
        if ($request->filled('stock_status')) {
            switch ($request->stock_status) {
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
        } elseif ($request->boolean('in_stock')) {
            // Legacy support for in_stock parameter
            $query->inStock();
        }

        // Search (name, sku, description)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('sku', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%');
            });
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
            case 'popular':
                $query->orderBy('sales_count', 'desc')
                    ->orderBy('views_count', 'desc');
                break;
            case 'stock_asc':
                $query->orderBy('stock_quantity', 'asc');
                break;
            case 'stock_desc':
                $query->orderBy('stock_quantity', 'desc');
                break;
            default:
                $query->latest();
        }

        $perPage = (int) $request->input('per_page', 15);
        $perPage = $perPage > 0 ? min($perPage, 100) : 15;

        $products = $query->paginate($perPage);

        return $this->successResponse(ProductResource::collection($products), 'Products retrieved successfully');
    }
}
