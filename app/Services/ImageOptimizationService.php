<?php

namespace App\Services;

use App\Models\MediaFolder;
use App\Models\MediaLibrary;
use App\Models\Setting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ImageOptimizationService
{
    protected ImageManager $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver);
    }

    public function optimizeMediaLibraryItem(MediaLibrary $media): bool
    {
        if ($media->type !== 'image') {
            return false;
        }

        if (! $media->file_path || ! Storage::disk('public')->exists($media->file_path)) {
            return false;
        }

        try {
            $absolutePath = Storage::disk('public')->path($media->file_path);
            $source = $this->manager->read($absolutePath);

            // WebP stays WebP; every other format is converted to the best
            // format this server can actually encode. A single optimized file
            // is stored (no derivative variations).
            $currentExtension = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION) ?: 'jpg');
            $outputExtension = $this->outputExtension($currentExtension);

            $newPath = dirname($media->file_path).'/'.pathinfo($media->file_path, PATHINFO_FILENAME).'.'.$outputExtension;
            $encoded = $source->encodeByExtension($outputExtension, quality: 82)->toString();
            Storage::disk('public')->put($newPath, $encoded);

            // Remove any previously generated WebP derivative.
            $oldWebpPath = $this->getWebpPath($media->file_path);
            if ($oldWebpPath !== $newPath && Storage::disk('public')->exists($oldWebpPath)) {
                Storage::disk('public')->delete($oldWebpPath);
            }

            // If the extension changed (e.g. jpg -> avif), remove the old original file.
            if ($newPath !== $media->file_path && Storage::disk('public')->exists($media->file_path)) {
                Storage::disk('public')->delete($media->file_path);
            }

            $media->file_path = $newPath;
            $media->file_name = basename($newPath);
            $media->webp_path = null;
            $media->mime_type = $this->mimeTypeFor($outputExtension);
            $media->file_size = Storage::disk('public')->size($newPath);
            $media->width = $source->width();
            $media->height = $source->height();
            $media->is_optimized = true;
            $media->save();

            return true;
        } catch (\Throwable $exception) {
            \Log::warning('Media optimization failed', [
                'media_id' => $media->id,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    public function uploadAndOptimize($image, string $path, int $width = 800, int $height = 800, ?string $context = null, ?int $contextId = null, bool $trackInLibrary = true, ?int $folderId = null): array
    {
        if ($folderId === null) {
            $folderId = $this->resolveDefaultFolderId();
        }

        // WebP uploads are kept as WebP; every other format is converted to AVIF.
        // A single optimized file is stored (no derivative variations) to save
        // disk space and improve page performance.
        $inputExtension = strtolower($image->getClientOriginalExtension() ?: 'jpg');
        $outputExtension = $this->outputExtension($inputExtension);

        $filename = Str::random(20).'.'.$outputExtension;
        $storagePath = $path.'/'.$filename;

        $img = $this->manager->read($image);
        $img->cover($width, $height);
        $encoded = $img->encodeByExtension($outputExtension, quality: 82)->toString();
        Storage::disk('public')->put($storagePath, $encoded);

        $mimeType = $this->mimeTypeFor($outputExtension);
        $widthPx = $img->width();
        $heightPx = $img->height();
        $fileSize = Storage::disk('public')->size($storagePath);

        $result = [
            'original' => $storagePath,
            'webp' => null,
            'has_webp' => false,
            'mime_type' => $mimeType,
            'file_name' => $filename,
            'width' => $widthPx,
            'height' => $heightPx,
            'file_size' => $fileSize,
        ];

        $media = null;
        if ($trackInLibrary) {
            $media = MediaLibrary::create([
                'file_name' => $filename,
                'original_name' => $image->getClientOriginalName(),
                'file_path' => $storagePath,
                'webp_path' => null,
                'mime_type' => $mimeType,
                'file_size' => $fileSize,
                'width' => $widthPx,
                'height' => $heightPx,
                'type' => 'image',
                'uploaded_by_type' => auth()->check() ? get_class(auth()->user()) : null,
                'uploaded_by_id' => auth()->id(),
                'context' => $context,
                'context_id' => $contextId,
                'folder_id' => $folderId,
            ]);
        }

        return array_merge($result, [
            'media_id' => $media?->id,
            'media' => $media,
        ]);
    }

    /**
     * WebP inputs stay WebP. Everything else is converted to AVIF when this
     * server's GD build can actually encode it (checked once per call via
     * `imageavif` existing, rather than assumed), falling back to JPEG
     * otherwise so uploads never fail on a server without AVIF support.
     */
    private function outputExtension(string $inputExtension): string
    {
        if ($inputExtension === 'webp') {
            return 'webp';
        }

        return function_exists('imageavif') ? 'avif' : 'jpg';
    }

    /**
     * MIME type for the extensions outputExtension() can produce, plus the
     * common originals it might be asked about elsewhere in the class.
     */
    private function mimeTypeFor(string $extension): string
    {
        return match (strtolower($extension)) {
            'avif' => 'image/avif',
            'webp' => 'image/webp',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            default => 'image/jpeg',
        };
    }

    private function resolveDefaultFolderId(): ?int
    {
        if (! auth()->check()) {
            return null;
        }

        if (! Schema::hasTable('media_folders')) {
            return null;
        }

        $user = auth()->user();
        $vendor = $user->vendor;

        if (! $vendor) {
            return null;
        }

        $name = 'Vendor - '.$vendor->business_name;
        $slug = Str::slug($name);
        $folder = MediaFolder::firstOrCreate(
            ['slug' => $slug],
            [
                'name' => $name,
                'owner_type' => get_class($vendor),
                'owner_id' => $vendor->id,
                'created_by_id' => $user->id,
            ]
        );

        return $folder->id;
    }

    public function deleteImage(string $imagePath): void
    {
        if ($imagePath === '') {
            return;
        }

        if (Storage::disk('public')->exists($imagePath)) {
            Storage::disk('public')->delete($imagePath);
        }

        $webpPath = $this->getWebpPath($imagePath);
        if (Storage::disk('public')->exists($webpPath)) {
            Storage::disk('public')->delete($webpPath);
        }

        MediaLibrary::where('file_path', $imagePath)
            ->orWhere('webp_path', $imagePath)
            ->orWhere('webp_path', $webpPath)
            ->delete();
    }

    public function getWebpPath(string $originalPath): string
    {
        $pathInfo = pathinfo($originalPath);

        return $pathInfo['dirname'].'/'.$pathInfo['filename'].'.webp';
    }

    public function hasWebpVersion(string $imagePath): bool
    {
        return Storage::disk('public')->exists($this->getWebpPath($imagePath));
    }

    public function deleteOriginalAfterOptimization(MediaLibrary $media): bool
    {
        $autoDeleteOriginals = Setting::get('media_auto_delete_originals', false);

        if (! $autoDeleteOriginals) {
            return false;
        }

        if (! $media->is_optimized || ! $media->original_file_path) {
            return false;
        }

        return $media->deleteOriginal();
    }

    public function processUploadWithAutoDelete($file, string $path, int $width = 800, int $height = 800, ?string $context = null, ?int $contextId = null, bool $trackInLibrary = true, ?int $folderId = null): array
    {
        $autoDeleteOriginals = Setting::get('media_auto_delete_originals', false);
        $autoOptimize = Setting::get('media_auto_optimize', true);

        // Store original file temporarily if auto-delete is enabled
        $tempPath = null;
        if ($autoDeleteOriginals && $autoOptimize) {
            $tempFilename = 'temp_original_'.Str::random(20).'.'.$file->getClientOriginalExtension();
            $tempPath = $file->storeAs('media/temp', $tempFilename, 'public');
        }

        // Perform optimization
        $result = $this->uploadAndOptimize($file, $path, $width, $height, $context, $contextId, $trackInLibrary, $folderId);

        // Mark as optimized and handle original deletion
        if ($trackInLibrary && isset($result['media']) && $result['media'] instanceof MediaLibrary) {
            $media = $result['media'];
            $media->is_optimized = true;

            if ($tempPath) {
                $media->original_file_path = $tempPath;
            }

            $media->save();

            // Try to delete original if setting is enabled
            if ($autoDeleteOriginals && $tempPath) {
                $this->deleteOriginalAfterOptimization($media);
            }

            \Log::info('Image optimized', [
                'media_id' => $media->id,
                'is_optimized' => $media->is_optimized,
                'auto_delete_originals' => $autoDeleteOriginals,
                'original_file_path' => $media->original_file_path,
            ]);
        }

        return $result;
    }
}
