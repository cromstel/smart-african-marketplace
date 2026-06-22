<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::withCount('products');

        if ($request->has('parent_id')) {
            $query->where('parent_id', $request->parent_id);
        }

        $categories = $query->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
            'message' => 'Categories retrieved successfully',
        ]);
    }

    public function show(Category $category)
    {
        $category->load(['products' => function ($query) {
            $query->where('is_active', true)->take(10);
        }]);

        return response()->json([
            'success' => true,
            'data' => $category,
            'message' => 'Category retrieved successfully',
        ]);
    }
}
