<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\ImageOptimizationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index()
    {
        $status = $this->normalizeStatus((string) request('status', 'all'));
        $search = trim((string) request('q', ''));
        $parentFilter = (string) request('parent', '');
        $dateSort = $this->normalizeDateSort((string) request('date_sort', 'newest'));

        $query = Category::query()
            ->with('parent')
            ->withCount(['products', 'children']);

        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        if ($search !== '') {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($parentFilter === 'top_level') {
            $query->whereNull('parent_id');
        } elseif ($parentFilter === 'subcategories') {
            $query->whereNotNull('parent_id');
        }

        if ($dateSort === 'oldest') {
            $query->orderBy('created_at');
        } else {
            $query->latest();
        }

        $categories = $query->paginate(20)->withQueryString();
        $allCategories = Category::orderBy('name')->get();
        $counts = [
            'all' => Category::count(),
            'active' => Category::where('is_active', true)->count(),
            'inactive' => Category::where('is_active', false)->count(),
        ];

        return view('admin.categories.index', compact(
            'categories',
            'allCategories',
            'counts',
            'status',
            'search',
            'parentFilter',
            'dateSort'
        ));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();

        return view('admin.categories.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'parent_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,webp,avif|max:4096',
        ]);

        $validated['slug'] = $this->uniqueSlug($validated['name']);
        $validated['is_active'] = true;

        if ($request->hasFile('banner_image')) {
            $imageService = app(ImageOptimizationService::class);
            $result = $imageService->uploadAndOptimize(
                $request->file('banner_image'),
                'category-banners',
                1600,
                900,
                'product_category_banner'
            );
            $validated['banner_image'] = $result['original'];
        }

        $category = Category::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'id' => $category->id,
                'name' => $category->name,
                'path' => $category->fullPath(),
            ]);
        }

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category created successfully');
    }

    public function edit(Category $category)
    {
        $category->loadCount(['products', 'children']);
        $categories = Category::where('id', '!=', $category->id)->orderBy('name')->get();

        return view('admin.categories.edit', compact('category', 'categories'));
    }

    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['activate', 'deactivate', 'delete'])],
            'category_ids' => ['required', 'array', 'min:1'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
        ]);

        $categories = Category::whereIn('id', $validated['category_ids'])->get();
        $count = $categories->count();

        if ($validated['action'] === 'activate') {
            Category::whereIn('id', $validated['category_ids'])->update(['is_active' => true]);

            return back()->with('success', "{$count} categor".($count === 1 ? 'y was' : 'ies were').' activated.');
        }

        if ($validated['action'] === 'deactivate') {
            Category::whereIn('id', $validated['category_ids'])->update(['is_active' => false]);

            return back()->with('success', "{$count} categor".($count === 1 ? 'y was' : 'ies were').' deactivated.');
        }

        $deletableIds = [];
        $blockedCount = 0;

        foreach ($categories as $category) {
            $hasProducts = $category->products()->exists();
            $hasChildren = $category->children()->exists();

            if ($hasProducts || $hasChildren) {
                $blockedCount++;

                continue;
            }

            $deletableIds[] = $category->id;
        }

        if (! empty($deletableIds)) {
            Category::whereIn('id', $deletableIds)->delete();
        }

        $deletedCount = count($deletableIds);

        if ($blockedCount > 0) {
            return back()->with('success', "{$deletedCount} deleted, {$blockedCount} skipped (has products or subcategories).");
        }

        return back()->with('success', "{$deletedCount} categor".($deletedCount === 1 ? 'y was' : 'ies were').' deleted.');
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,'.$category->id,
            'parent_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,webp,avif|max:4096',
        ]);

        $newParentId = $validated['parent_id'] ?? null;
        if ($newParentId && $newParentId === $category->id) {
            return back()->withErrors(['parent_id' => 'A category cannot be its own parent.'])->withInput();
        }

        if ($newParentId) {
            $newParent = Category::find($newParentId);
            if ($newParent && $newParent->isDescendantOf($category->id)) {
                return back()->withErrors(['parent_id' => 'Invalid parent category selection (cycle detected).'])->withInput();
            }
        }

        $validated['slug'] = $this->uniqueSlug($validated['name'], $category->id);

        if ($request->hasFile('banner_image')) {
            $imageService = app(ImageOptimizationService::class);
            if ($category->banner_image) {
                $imageService->deleteImage($category->banner_image);
            }
            $result = $imageService->uploadAndOptimize(
                $request->file('banner_image'),
                'category-banners',
                1600,
                900,
                'product_category_banner',
                $category->id
            );
            $validated['banner_image'] = $result['original'];
        }

        $category->update($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category updated successfully');
    }

    public function destroy(Category $category)
    {
        if ($category->products()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete category with products');
        }

        if ($category->banner_image) {
            app(ImageOptimizationService::class)->deleteImage($category->banner_image);
        }

        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category deleted successfully');
    }

    private function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value);
        $slug = $base !== '' ? $base : Str::random(8);
        $counter = 1;

        while (Category::when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    private function normalizeStatus(string $status): string
    {
        return in_array($status, ['all', 'active', 'inactive'], true) ? $status : 'all';
    }

    private function normalizeDateSort(string $dateSort): string
    {
        return in_array($dateSort, ['newest', 'oldest'], true) ? $dateSort : 'newest';
    }
}
