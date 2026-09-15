<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\MediaFolder;
use App\Models\MediaLibrary;
use App\Models\Setting;
use App\Models\Vendor;
use App\Services\ImageOptimizationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MediaController extends Controller
{
    protected $imageOptimizationService;

    public function __construct(ImageOptimizationService $imageOptimizationService)
    {
        $this->imageOptimizationService = $imageOptimizationService;
    }

    /**
     * Get the current vendor or redirect.
     */
    protected function getVendor(): ?Vendor
    {
        $user = auth()->user();

        if (! $user) {
            return null;
        }

        $vendor = $user->vendor;

        if (! $vendor) {
            return null;
        }

        if (! $vendor->isApproved()) {
            return null;
        }

        return $vendor;
    }

    /**
     * Display vendor's media library.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $vendor = $this->getVendor();

        if (! $vendor) {
            return redirect()->route('dashboard')
                ->with('error', 'You need to set up a vendor store first.');
        }

        // Get or create vendor's root folder
        $rootFolder = $this->getOrCreateVendorFolder($vendor);

        $query = MediaLibrary::with('uploadedBy')
            ->where('vendor_id', $vendor->id)
            ->latest();

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('alt_text', 'like', "%{$search}%")
                    ->orWhere('original_name', 'like', "%{$search}%");
            });
        }

        // Filter by folder
        if ($request->filled('folder_id')) {
            $query->where('folder_id', $request->folder_id);
        } else {
            // Default to vendor's root folder
            $query->where('folder_id', $rootFolder->id);
        }

        $media = $query->paginate(24);

        // Get vendor's folders
        $folders = MediaFolder::where('vendor_id', $vendor->id)
            ->orderBy('name')
            ->get();

        $stats = [
            'total' => MediaLibrary::where('vendor_id', $vendor->id)->count(),
            'images' => MediaLibrary::where('vendor_id', $vendor->id)->images()->count(),
            'documents' => MediaLibrary::where('vendor_id', $vendor->id)->documents()->count(),
            'videos' => MediaLibrary::where('vendor_id', $vendor->id)->videos()->count(),
            'total_size' => MediaLibrary::where('vendor_id', $vendor->id)->sum('file_size'),
        ];

        return view('vendor.media.index', compact('media', 'stats', 'folders', 'rootFolder', 'vendor'));
    }

    /**
     * Get or create vendor's root folder.
     */
    protected function getOrCreateVendorFolder($vendor): MediaFolder
    {
        $folder = MediaFolder::where('vendor_id', $vendor->id)
            ->whereNull('parent_id')
            ->first();

        if (! $folder) {
            $folder = MediaFolder::create([
                'name' => "Vendor {$vendor->id}",
                'slug' => "vendor-{$vendor->id}",
                'vendor_id' => $vendor->id,
                'owner_type' => 'App\\Models\\Vendor',
                'owner_id' => $vendor->id,
                'created_by_id' => $vendor->user_id,
            ]);
        }

        return $folder;
    }

    /**
     * Show single media item.
     */
    public function show(MediaLibrary $media): View
    {
        $vendor = $this->getVendor();
        if (! $vendor) {
            abort(403, 'Vendor access required.');
        }

        // Ensure vendor can only view their own media
        if ($media->vendor_id !== $vendor->id) {
            abort(403, 'You can only view your own media.');
        }

        $media->load('uploadedBy');

        return view('vendor.media.show', compact('media'));
    }

    /**
     * Edit media metadata.
     */
    public function edit(MediaLibrary $media): View
    {
        $vendor = $this->getVendor();
        if (! $vendor) {
            abort(403, 'Vendor access required.');
        }

        if ($media->vendor_id !== $vendor->id) {
            abort(403, 'You can only edit your own media.');
        }

        return view('vendor.media.edit', compact('media'));
    }

    /**
     * Update media metadata.
     */
    public function update(Request $request, MediaLibrary $media): RedirectResponse
    {
        $vendor = $this->getVendor();
        if (! $vendor) {
            abort(403, 'Vendor access required.');
        }

        if ($media->vendor_id !== $vendor->id) {
            abort(403, 'You can only update your own media.');
        }

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'alt_text' => 'nullable|string|max:255',
            'caption' => 'nullable|string|max:500',
            'description' => 'nullable|string',
        ]);

        $media->update($validated);

        return redirect()->route('vendor.media.show', $media)
            ->with('success', 'Media updated successfully.');
    }

    /**
     * Delete media item.
     */
    public function destroy(MediaLibrary $media): RedirectResponse
    {
        $vendor = $this->getVendor();
        if (! $vendor) {
            abort(403, 'Vendor access required.');
        }

        if ($media->vendor_id !== $vendor->id) {
            abort(403, 'You can only delete your own media.');
        }

        if (Storage::disk('public')->exists($media->file_path)) {
            Storage::disk('public')->delete($media->file_path);
        }

        if ($media->webp_path && Storage::disk('public')->exists($media->webp_path)) {
            Storage::disk('public')->delete($media->webp_path);
        }

        $media->delete();

        return redirect()->route('vendor.media.index')
            ->with('success', 'Media deleted successfully.');
    }

    /**
     * Upload new media files.
     */
    public function upload(Request $request)
    {
        $vendor = $this->getVendor();

        if (! $vendor) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Vendor not found.'], 403);
            }

            return redirect()->route('dashboard')->with('error', 'Vendor not found.');
        }

        $request->validate([
            'files' => 'required|array',
            // svg deliberately excluded: it's served as-is from the public
            // disk at a predictable, directly-browsable URL, and an SVG can
            // carry an onload handler or inline <script> that executes in
            // this site's own origin if that URL is ever opened directly
            // (not just referenced via <img>) -- a stored-XSS vector any
            // vendor could upload through this shared library.
            'files.*' => 'file|mimes:jpeg,jpg,png,webp,pdf,doc,docx,xls,xlsx,mp4,mov,avi|max:10240',
            'folder_id' => 'nullable|exists:media_folders,id',
        ]);

        // Get or create vendor folder
        $rootFolder = $this->getOrCreateVendorFolder($vendor);

        // Use vendor's folder or default to root
        $folderId = $request->input('folder_id') ?? $rootFolder->id;

        // Verify folder belongs to vendor
        $folder = MediaFolder::where('id', $folderId)
            ->where('vendor_id', $vendor->id)
            ->first();

        if (! $folder) {
            $folderId = $rootFolder->id;
        }

        $uploaded = [];

        foreach ($request->file('files') as $file) {
            $type = $this->detectFileType($file->getMimeType());

            // Store in vendor-specific folder
            $path = $file->store("media/vendors/{$vendor->id}", 'public');

            if ($type === 'image') {
                $autoOptimize = Setting::get('media_auto_optimize', true);

                if ($autoOptimize) {
                    $maxWidth = Setting::get('media_max_width', 1200);
                    $maxHeight = Setting::get('media_max_height', 1200);

                    // trackInLibrary: false -- this method already builds its own
                    // MediaLibrary row below (it needs to set vendor_id, which
                    // uploadAndOptimize()'s own internal tracking row never sets).
                    // Leaving this true made every optimized vendor upload create
                    // two MediaLibrary rows for the same physical file: one from
                    // here (with vendor_id) and an untracked duplicate from the
                    // service (without vendor_id).
                    $result = $this->imageOptimizationService->uploadAndOptimize(
                        $file,
                        "media/vendors/{$vendor->id}",
                        $maxWidth,
                        $maxHeight,
                        null,
                        null,
                        false,
                        $folderId
                    );

                    $media = MediaLibrary::create([
                        'file_name' => $result['file_name'] ?? basename($result['original']),
                        'original_name' => $file->getClientOriginalName(),
                        'file_path' => $result['original'],
                        'webp_path' => null,
                        'mime_type' => $result['mime_type'],
                        'file_size' => $result['file_size'] ?? Storage::disk('public')->size($result['original']),
                        'width' => $result['width'] ?? null,
                        'height' => $result['height'] ?? null,
                        'type' => $type,
                        'uploaded_by_type' => 'App\\Models\\User',
                        'uploaded_by_id' => auth()->id(),
                        'vendor_id' => $vendor->id,
                        'folder_id' => $folderId,
                        'is_optimized' => true,
                    ]);
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
                        'vendor_id' => $vendor->id,
                        'folder_id' => $folderId,
                        'is_optimized' => false,
                    ]);
                }
            } else {
                $media = MediaLibrary::create([
                    'file_name' => basename($path),
                    'original_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'mime_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                    'type' => $type,
                    'uploaded_by_type' => 'App\\Models\\User',
                    'uploaded_by_id' => auth()->id(),
                    'vendor_id' => $vendor->id,
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

        return redirect()->route('vendor.media.index')
            ->with('success', count($uploaded).' file(s) uploaded successfully.');
    }

    /**
     * Create new folder.
     */
    public function storeFolder(Request $request): RedirectResponse
    {
        $vendor = $this->getVendor();

        if (! $vendor) {
            return redirect()->route('dashboard')->with('error', 'Vendor not found.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:media_folders,id',
        ]);

        // Verify parent folder belongs to vendor
        if ($validated['parent_id']) {
            $parentFolder = MediaFolder::where('id', $validated['parent_id'])
                ->where('vendor_id', $vendor->id)
                ->first();

            if (! $parentFolder) {
                $validated['parent_id'] = null;
            }
        }

        $baseSlug = Str::slug($validated['name']);
        $slug = $baseSlug;
        $counter = 1;

        while (MediaFolder::where('slug', $slug)->where('vendor_id', $vendor->id)->exists()) {
            $counter++;
            $slug = $baseSlug.'-'.$counter;
        }

        MediaFolder::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'parent_id' => $validated['parent_id'] ?? null,
            'vendor_id' => $vendor->id,
            'owner_type' => 'App\\Models\\Vendor',
            'owner_id' => $vendor->id,
            'created_by_id' => auth()->id(),
        ]);

        return redirect()->route('vendor.media.index')
            ->with('success', 'Folder created successfully.');
    }

    /**
     * Bulk delete media.
     */
    public function bulkDelete(Request $request): RedirectResponse
    {
        $vendor = $this->getVendor();
        if (! $vendor) {
            abort(403, 'Vendor access required.');
        }

        $validated = $request->validate([
            'media_ids' => 'required|array',
            'media_ids.*' => 'exists:media_library,id',
        ]);

        // Only delete vendor's own media
        $media = MediaLibrary::whereIn('id', $validated['media_ids'])
            ->where('vendor_id', $vendor->id)
            ->get();

        foreach ($media as $item) {
            if (Storage::disk('public')->exists($item->file_path)) {
                Storage::disk('public')->delete($item->file_path);
            }

            if ($item->webp_path && Storage::disk('public')->exists($item->webp_path)) {
                Storage::disk('public')->delete($item->webp_path);
            }

            $item->delete();
        }

        return redirect()->route('vendor.media.index')
            ->with('success', count($media).' media items deleted successfully.');
    }

    /**
     * Replace media file.
     */
    public function replace(Request $request, MediaLibrary $media): RedirectResponse
    {
        $vendor = $this->getVendor();
        if (! $vendor) {
            abort(403, 'Vendor access required.');
        }

        if ($media->vendor_id !== $vendor->id) {
            abort(403, 'You can only replace your own media.');
        }

        $request->validate([
            'file' => 'required|file|mimes:jpeg,jpg,png,webp|max:10240',
        ]);

        $file = $request->file('file');

        // Delete old files
        if (Storage::disk('public')->exists($media->file_path)) {
            Storage::disk('public')->delete($media->file_path);
        }
        if ($media->webp_path && Storage::disk('public')->exists($media->webp_path)) {
            Storage::disk('public')->delete($media->webp_path);
        }

        // Store new file (converted to AVIF / kept as WebP — single optimized file, no derivatives)
        $maxWidth = Setting::get('media_max_width', 1200);
        $maxHeight = Setting::get('media_max_height', 1200);

        $result = $this->imageOptimizationService->uploadAndOptimize(
            $file,
            "media/vendors/{$vendor->id}",
            $maxWidth,
            $maxHeight,
            null,
            null,
            false,
            $media->folder_id
        );

        $media->file_path = $result['original'];
        $media->file_name = $result['file_name'];
        $media->original_name = $file->getClientOriginalName();
        $media->file_size = $result['file_size'];
        $media->mime_type = $result['mime_type'];
        $media->webp_path = null;

        if ($media->type === 'image') {
            $media->width = $result['width'] ?? null;
            $media->height = $result['height'] ?? null;
            $media->is_optimized = true;
        }

        $media->save();

        return redirect()->route('vendor.media.show', $media)
            ->with('success', 'File replaced successfully.');
    }

    /**
     * Detect file type.
     */
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
}
