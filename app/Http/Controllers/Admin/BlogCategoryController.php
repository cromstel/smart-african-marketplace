<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BlogCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index()
    {
        $categories = BlogCategory::with(['parent'])->withCount('posts')->orderBy('name')->paginate(20);

        return view('admin.blog.categories.index', compact('categories'));
    }

    public function create()
    {
        $categories = BlogCategory::orderBy('name')->get();

        return view('admin.blog.categories.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:blog_categories,name',
            'parent_id' => 'nullable|exists:blog_categories,id',
            'description' => 'nullable|string|max:1000',
        ]);

        $validated['slug'] = $this->uniqueSlug($validated['name']);

        BlogCategory::create($validated);

        return redirect()->route('admin.blog.categories.index')->with('success', 'Category created successfully');
    }

    public function edit(BlogCategory $blogCategory)
    {
        $categories = BlogCategory::where('id', '!=', $blogCategory->id)->orderBy('name')->get();

        return view('admin.blog.categories.edit', compact('blogCategory', 'categories'));
    }

    public function update(Request $request, BlogCategory $blogCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:blog_categories,name,'.$blogCategory->id,
            'parent_id' => 'nullable|exists:blog_categories,id',
            'description' => 'nullable|string|max:1000',
        ]);

        $newParentId = $validated['parent_id'] ?? null;
        if ($newParentId && $newParentId === $blogCategory->id) {
            return back()->withErrors(['parent_id' => 'A category cannot be its own parent.'])->withInput();
        }

        if ($newParentId) {
            $newParent = BlogCategory::find($newParentId);
            if ($newParent && $newParent->isDescendantOf($blogCategory->id)) {
                return back()->withErrors(['parent_id' => 'Invalid parent category selection (cycle detected).'])->withInput();
            }
        }

        $validated['slug'] = $this->uniqueSlug($validated['name'], $blogCategory->id);

        $blogCategory->update($validated);

        return redirect()->route('admin.blog.categories.index')->with('success', 'Category updated successfully');
    }

    public function destroy(BlogCategory $blogCategory)
    {
        $blogCategory->delete();

        return redirect()->route('admin.blog.categories.index')->with('success', 'Category deleted successfully');
    }

    private function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value);
        $slug = $base !== '' ? $base : Str::random(8);
        $counter = 1;

        while (BlogCategory::when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
