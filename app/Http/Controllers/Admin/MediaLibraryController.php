<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MediaFolder;
use App\Models\MediaLibrary;
use App\Models\Setting;
use App\Services\ImageOptimizationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaLibraryController extends Controller
{
    protected $imageOptimizationService;

    public function __construct(ImageOptimizationService $imageOptimizationService)
    {
        $this->imageOptimizationService = $imageOptimizationService;
    }

    public function index(Request $request)
    {
        $query = MediaLibrary::with('uploadedBy')->latest();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('alt_text', 'like', "%{$search}%")
                    ->orWhere('original_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('uploaded_by')) {
            $query->where('uploaded_by_id', $request->uploaded_by);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('folder_id')) {
            $query->where('folder_id', $request->folder_id);
        }

        $media = $query->paginate(24);
        $folders = MediaFolder::orderBy('name')->get();

        $stats = [
            'total' => MediaLibrary::count(),
            'images' => MediaLibrary::images()->count(),
            'documents' => MediaLibrary::documents()->count(),
            'videos' => MediaLibrary::videos()->count(),
            'total_size' => MediaLibrary::sum('file_size'),
        ];

        return view('admin.media.index', compact('media', 'stats', 'folders'));
    }

    public function create()
    {
        $folders = MediaFolder::orderBy('name')->get();

        return view('admin.media.create', compact('folders'));
    }

    public function settings()
    {
        return view('admin.media.settings');
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'auto_optimize' => 'boolean',
            'generate_webp' => 'boolean',
            'auto_delete_originals' => 'boolean',
            'max_width' => 'integer|min:100|max:5000',
            'max_height' => 'integer|min:100|max:5000',
            'max_file_size' => 'integer|min:1|max:100',
            'allowed_types' => 'string',
            'auto_alt' => 'boolean',
        ]);

        foreach ($validated as $key => $value) {
            Setting::set('media_'.$key, $value);
        }

        return redirect()->route('admin.media.settings')
            ->with('success', 'Media settings updated successfully.');
    }

    public function show(MediaLibrary $media)
    {
        $media->load('uploadedBy');

        return view('admin.media.show', compact('media'));
    }

    public function edit(MediaLibrary $media)
    {
        return view('admin.media.edit', compact('media'));
    }

    public function update(Request $request, MediaLibrary $media)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'alt_text' => 'nullable|string|max:255',
        ]);

        $media->update($validated);

        return redirect()->route('admin.media.show', $media)
            ->with('success', 'Media updated successfully.');
    }

    public function destroy(MediaLibrary $media)
    {
        if (Storage::disk('public')->exists($media->file_path)) {
            Storage::disk('public')->delete($media->file_path);
        }

        if ($media->webp_path && Storage::disk('public')->exists($media->webp_path)) {
            Storage::disk('public')->delete($media->webp_path);
        }

        $media->delete();

        return redirect()->route('admin.media.index')
            ->with('success', 'Media deleted successfully.');
    }

    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'media_ids' => 'required|array',
            'media_ids.*' => 'exists:media_library,id',
        ]);

        $media = MediaLibrary::whereIn('id', $validated['media_ids'])->get();

        foreach ($media as $item) {
            if (Storage::disk('public')->exists($item->file_path)) {
                Storage::disk('public')->delete($item->file_path);
            }

            if ($item->webp_path && Storage::disk('public')->exists($item->webp_path)) {
                Storage::disk('public')->delete($item->webp_path);
            }

            $item->delete();
        }

        return redirect()->route('admin.media.index')
            ->with('success', count($validated['media_ids']).' media items deleted successfully.');
    }

    public function upload(Request $request)
    {
        // svg deliberately excluded: see the identical note in
        // Vendor\MediaController::upload() -- it's served as-is from the
        // public disk at a predictable URL, and can carry a script/onload
        // handler that executes in this site's own origin if opened
        // directly rather than only referenced via <img>.
        $request->validate([
            'files' => 'required|array',
            'files.*' => 'file|mimes:jpeg,jpg,png,webp,pdf,doc,docx,xls,xlsx,mp4,mov,avi|max:10240',
            'folder_id' => 'nullable|exists:media_folders,id',
        ]);

        $uploaded = [];
        $folderId = $request->input('folder_id');

        foreach ($request->file('files') as $file) {
            $type = $this->detectFileType($file->getMimeType());

            if ($type === 'image') {
                $autoOptimize = Setting::get('media_auto_optimize', true);
                $autoDeleteOriginals = Setting::get('media_auto_delete_originals', false);
                $maxWidth = Setting::get('media_max_width', 1200);
                $maxHeight = Setting::get('media_max_height', 1200);

                if ($autoOptimize) {
                    $result = $this->imageOptimizationService->processUploadWithAutoDelete(
                        $file,
                        'media',
                        $maxWidth,
                        $maxHeight,
                        null,
                        null,
                        true,
                        $folderId
                    );
                    $media = $result['media'] ?? null;
                } else {
                    $imageSize = getimagesize($file->getRealPath());
                    $media = MediaLibrary::create([
                        'file_name' => basename($path),
                        'original_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'webp_path' => null,
                        'mime_type' => $file->getMimeType(),
                        'file_size' => $file->getSize(),
                        'width' => $imageSize[0] ?? null,
                        'height' => $imageSize[1] ?? null,
                        'type' => $type,
                        'uploaded_by_type' => 'App\\Models\\User',
                        'uploaded_by_id' => auth()->id(),
                        'folder_id' => $folderId,
                        'is_optimized' => false,
                    ]);
                }
            } else {
                $path = $file->store('media', 'public');

                $media = MediaLibrary::create([
                    'file_name' => basename($path),
                    'original_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'mime_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                    'type' => $type,
                    'uploaded_by_type' => 'App\\Models\\User',
                    'uploaded_by_id' => auth()->id(),
                    'folder_id' => $folderId,
                ]);
            }

            $uploaded[] = $media;
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'media' => $uploaded,
            ]);
        }

        return redirect()->route('admin.media.index')
            ->with('success', count($uploaded).' file(s) uploaded successfully.');
    }

    protected function detectFileType(string $mimeType): string
    {
        if (str_starts_with($mimeType, 'image/')) {
            return 'image';
        }

        if (str_starts_with($mimeType, 'video/')) {
            return 'video';
        }

        if (in_array($mimeType, [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])) {
            return 'document';
        }

        return 'other';
    }

    public function storeFolder(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:media_folders,id',
        ]);

        $baseSlug = Str::slug($validated['name']);
        $slug = $baseSlug;
        $counter = 1;

        while (MediaFolder::where('slug', $slug)->exists()) {
            $counter++;
            $slug = $baseSlug.'-'.$counter;
        }

        MediaFolder::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'parent_id' => $validated['parent_id'] ?? null,
            'owner_type' => 'App\\Models\\User',
            'owner_id' => auth()->id(),
            'created_by_id' => auth()->id(),
        ]);

        return redirect()->route('admin.media.index')
            ->with('success', 'Folder created successfully.');
    }

    public function regenerateAlt(MediaLibrary $media)
    {
        $media->alt_text = $media->generateAltText();
        $media->save();

        return redirect()->back()->with('success', 'Alt text regenerated successfully.');
    }

    public function replace(Request $request, MediaLibrary $media)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpeg,jpg,png,webp|max:10240',
        ]);

        $file = $request->file('file');

        // Store the original file path before replacement for logging
        $oldFilePath = $media->file_path;
        $oldWebpPath = $media->webp_path;
        $oldOriginalPath = $media->original_file_path;

        // Get the optimization settings
        $autoOptimize = Setting::get('media_auto_optimize', true);
        $generateWebp = Setting::get('media_generate_webp', true);
        $autoDeleteOriginals = Setting::get('media_auto_delete_originals', false);

        // Get max dimensions from settings
        $maxWidth = Setting::get('media_max_width', 1200);
        $maxHeight = Setting::get('media_max_height', 1200);

        $folderId = $media->folder_id;

        if ($autoOptimize && $media->type === 'image') {
            // Run optimization on the new file
            $result = $this->imageOptimizationService->uploadAndOptimize(
                $file,
                'media',
                $maxWidth,
                $maxHeight,
                null,
                null,
                false,
                $folderId
            );

            // Get the optimized paths
            $newFilePath = $result['original'];

            // Update the media record (metadata reflects the converted AVIF/WebP file)
            $media->file_path = $newFilePath;
            $media->file_name = $result['file_name'] ?? basename($newFilePath);
            $media->webp_path = null;
            $media->original_name = $file->getClientOriginalName();
            $media->file_size = $result['file_size'] ?? $file->getSize();
            $media->width = $result['width'] ?? null;
            $media->height = $result['height'] ?? null;
            $media->mime_type = $result['mime_type'];
            $media->is_optimized = true;
            $media->original_file_path = $oldFilePath; // Store old path as reference
            $media->save();

            // Delete old files (but not the original if it was stored separately)
            $this->deleteOldFiles($oldFilePath, $oldWebpPath);

            // Handle auto-delete of originals if enabled
            if ($autoDeleteOriginals && $oldOriginalPath) {
                $media->deleteOriginal();
            }

            \Log::info('Media file replaced with optimization', [
                'media_id' => $media->id,
                'old_path' => $oldFilePath,
                'new_path' => $newFilePath,
                'auto_delete_originals' => $autoDeleteOriginals,
            ]);

            return redirect()->route('admin.media.show', $media)
                ->with('success', 'Image replaced and optimized successfully.');
        } else {
            // No optimization - just replace the file
            $path = $file->store('media', 'public');
            $media->file_path = $path;
            $media->original_name = $file->getClientOriginalName();
            $media->file_size = $file->getSize();
            $media->mime_type = $file->getMimeType();
            $imageSize = getimagesize($file->getRealPath());
            $media->width = $imageSize[0] ?? null;
            $media->height = $imageSize[1] ?? null;
            $media->is_optimized = false;
            $media->original_file_path = $oldFilePath;
            $media->save();

            // Delete old file
            if ($oldFilePath && Storage::disk('public')->exists($oldFilePath)) {
                Storage::disk('public')->delete($oldFilePath);
            }
            if ($oldWebpPath && Storage::disk('public')->exists($oldWebpPath)) {
                Storage::disk('public')->delete($oldWebpPath);
            }

            \Log::info('Media file replaced without optimization', [
                'media_id' => $media->id,
                'old_path' => $oldFilePath,
                'new_path' => $path,
            ]);

            return redirect()->route('admin.media.show', $media)
                ->with('success', 'Image replaced successfully.');
        }
    }

    protected function deleteOldFiles(?string $filePath, ?string $webpPath): void
    {
        try {
            if ($filePath && Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }
            if ($webpPath && Storage::disk('public')->exists($webpPath)) {
                Storage::disk('public')->delete($webpPath);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to delete old media files', [
                'file_path' => $filePath,
                'webp_path' => $webpPath,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function bulkRegenerateAlt(Request $request)
    {
        $validated = $request->validate([
            'media_ids' => 'required|array',
            'media_ids.*' => 'exists:media_library,id',
        ]);

        $media = MediaLibrary::whereIn('id', $validated['media_ids'])->get();

        foreach ($media as $item) {
            $item->alt_text = $item->generateAltText();
            $item->save();
        }

        return redirect()->route('admin.media.index')
            ->with('success', count($validated['media_ids']).' alt texts regenerated successfully.');
    }
}
