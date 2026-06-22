<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogTag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BlogTagController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index()
    {
        $tags = BlogTag::withCount('posts')->paginate(20);

        return view('admin.blog.tags.index', compact('tags'));
    }

    public function create()
    {
        return view('admin.blog.tags.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:blog_tags,name',
            'description' => 'nullable|string|max:500',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        if (empty($validated['color'])) {
            $validated['color'] = '#6366f1';
        }

        BlogTag::create($validated);

        return redirect()->route('admin.blog.tags.index')->with('success', 'Tag created successfully');
    }

    public function edit(BlogTag $blogTag)
    {
        return view('admin.blog.tags.edit', compact('blogTag'));
    }

    public function update(Request $request, BlogTag $blogTag)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:blog_tags,name,'.$blogTag->id,
            'description' => 'nullable|string|max:500',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        if (empty($validated['color'])) {
            $validated['color'] = '#6366f1';
        }

        $blogTag->update($validated);

        return redirect()->route('admin.blog.tags.index')->with('success', 'Tag updated successfully');
    }

    public function destroy(BlogTag $blogTag)
    {
        $blogTag->delete();

        return redirect()->route('admin.blog.tags.index')->with('success', 'Tag deleted successfully');
    }
}
