<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        $query = Brand::query()
            ->active()
            ->withCount(['products as products_count' => function ($query) {
                $query->active()->published();
            }])
            ->latest();

        if ($request->filled('search')) {
            $search = (string) $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%');
            });
        }

        $perPage = min(max((int) $request->input('per_page', 15), 1), 100);

        $brands = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $brands,
            'message' => 'Brands retrieved successfully',
        ]);
    }

    public function show(Brand $brand)
    {
        if (! $brand->is_active) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Brand not found',
            ], 404);
        }

        $brand->loadMissing('vendor');
        $brand->setAttribute('products_count', $brand->products()->active()->published()->count());

        return response()->json([
            'success' => true,
            'data' => $brand,
            'message' => 'Brand retrieved successfully',
        ]);
    }
}
