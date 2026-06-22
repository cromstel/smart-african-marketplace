<?php

namespace App\Services;

use App\Models\MediaFolder;
use App\Models\MediaLibrary;
use App\Models\Setting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class ImageOptimizationService
{
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
            $source = Image::make($absolutePath);

            $encodedOriginal = (string) $source->encode(pathinfo($absolutePath, PATHINFO_EXTENSION) ?: 'jpg', 85);
            Storage::disk('public')->put($media->file_path, $encodedOriginal);

            if (extension_loaded('gd') && function_exists('imagewebp')) {
                $webpPath = $this->getWebpPath($media->file_path);
                $webpData = (string) $source->encode('webp', 85);
                Storage::disk('public')->put($webpPath, $webpData);
                $media->webp_path = $webpPath;
            }

            $media->is_optimized = true;
            $media->file_size = Storage::disk('public')->size($media->file_path);
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

        $filename = Str::random(20);
        $extension = $image->getClientOriginalExtension();
        $originalFilename = $filename.'.'.$extension;
        $webpFilename = $filename.'.webp';

        $img = Image::make($image);
        $img->fit($width, $height, function ($constraint) {
            $constraint->upsize();
        });

        Storage::disk('public')->put($path.'/'.$originalFilename, (string) $img->encode($extension, 85));

        $result = [
            'original' => $path.'/'.$originalFilename,
            'webp' => null,
            'has_webp' => false,
        ];

        if (extension_loaded('gd') && function_exists('imagewebp')) {
            try {
                $webpImage = Image::make($image);
                $webpImage->fit($width, $height, function ($constraint) {
                    $constraint->upsize();
                });
                Storage::disk('public')->put($path.'/'.$webpFilename, (string) $webpImage->encode('webp', 85));

                $result = [
                    'original' => $path.'/'.$originalFilename,
                    'webp' => $path.'/'.$webpFilename,
                    'has_webp' => true,
                ];
            } catch (\Exception $e) {
            }
        }

        $media = null;
        if ($trackInLibrary) {
            $imageSize = getimagesize($image->getRealPath());
            $fileSize = $image->getSize();

            $media = MediaLibrary::create([
                'file_name' => $originalFilename,
                'original_name' => $image->getClientOriginalName(),
                'file_path' => $result['original'],
                'webp_path' => $result['webp'] ?? null,
                'mime_type' => $image->getMimeType(),
                'file_size' => $fileSize,
                'width' => $imageSize[0] ?? null,
                'height' => $imageSize[1] ?? null,
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
