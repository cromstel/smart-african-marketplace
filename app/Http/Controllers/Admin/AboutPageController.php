<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AboutPageContent;
use App\Models\MediaLibrary;
use App\Services\ImageOptimizationService;
use Illuminate\Http\Request;

class AboutPageController extends Controller
{
    public function index()
    {
        $contents = AboutPageContent::ordered()->get();

        return view('admin.about.index', compact('contents'));
    }

    public function create()
    {
        $mediaImages = MediaLibrary::query()
            ->images()
            ->latest()
            ->limit(100)
            ->get(['id', 'title', 'original_name', 'file_path']);

        return view('admin.about.create', compact('mediaImages'));
    }

    public function store(Request $request, ImageOptimizationService $imageService)
    {
        $validated = $request->validate([
            'section_key' => 'required|string|unique:about_page_contents,section_key',
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'items' => 'nullable|json',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,avif|max:2048',
            'image_media_id' => 'nullable|integer|exists:media_library,id',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            $result = $imageService->uploadAndOptimize(
                $request->file('image'),
                'about',
                1200,
                800,
                'about_page_content'
            );
            $validated['image'] = $result['original'];
        } elseif ($request->filled('image_media_id')) {
            $selectedMedia = MediaLibrary::query()
                ->images()
                ->find($request->integer('image_media_id'));
            if ($selectedMedia) {
                $validated['image'] = $selectedMedia->file_path;
            }
        }

        $validated['items'] = $request->items ? json_decode($request->items, true) : null;
        $validated['is_active'] = $request->has('is_active');

        AboutPageContent::create($validated);

        return redirect()->route('admin.about.index')
            ->with('success', 'About page section created successfully');
    }

    public function edit(AboutPageContent $about)
    {
        $mediaImages = MediaLibrary::query()
            ->images()
            ->latest()
            ->limit(100)
            ->get(['id', 'title', 'original_name', 'file_path']);

        return view('admin.about.edit', compact('about', 'mediaImages'));
    }

    public function update(Request $request, AboutPageContent $about, ImageOptimizationService $imageService)
    {
        $validated = $request->validate([
            'section_key' => 'required|string|unique:about_page_contents,section_key,'.$about->id,
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'items' => 'nullable|json',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,avif|max:2048',
            'image_media_id' => 'nullable|integer|exists:media_library,id',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            if ($about->image) {
                $imageService->deleteImage($about->image);
            }
            $result = $imageService->uploadAndOptimize(
                $request->file('image'),
                'about',
                1200,
                800,
                'about_page_content',
                $about->id
            );
            $validated['image'] = $result['original'];
        } elseif ($request->filled('image_media_id')) {
            $selectedMedia = MediaLibrary::query()
                ->images()
                ->find($request->integer('image_media_id'));
            if ($selectedMedia) {
                $validated['image'] = $selectedMedia->file_path;
            }
        }

        $validated['items'] = $request->items ? json_decode($request->items, true) : null;
        $validated['is_active'] = $request->has('is_active');

        $about->update($validated);

        return redirect()->route('admin.about.index')
            ->with('success', 'About page section updated successfully');
    }

    public function destroy(AboutPageContent $about, ImageOptimizationService $imageService)
    {
        if ($about->image) {
            $imageService->deleteImage($about->image);
        }

        $about->delete();

        return redirect()->route('admin.about.index')
            ->with('success', 'About page section deleted successfully');
    }

    public function toggle(AboutPageContent $about)
    {
        $about->update(['is_active' => ! $about->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $about->is_active,
        ]);
    }
}
