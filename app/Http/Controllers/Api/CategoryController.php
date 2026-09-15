<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = Category::withCount('products');

        if ($request->has('parent_id')) {
            $query->where('parent_id', $request->parent_id);
        }

        // Intentionally not paginated: this is a small navigational taxonomy
        // list (unlike the other /api/v1 list endpoints), and consumers
        // building a category menu/filter expect the full set in one call.
        $categories = $query->orderBy('name')->get();

        return $this->successResponse(CategoryResource::collection($categories), 'Categories retrieved successfully');
    }

    public function show(Category $category)
    {
        return $this->successResponse(new CategoryResource($category), 'Category retrieved successfully');
    }
}
