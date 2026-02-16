<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class ImageOptimizationService
{
    public function uploadAndOptimize($image, string $path, int $width = 800, int $height = 800): array
    {
        $filename = Str::random(20);
        $extension = $image->getClientOriginalExtension();
        $originalFilename = $filename.'.'.$extension;
        $webpFilename = $filename.'.webp';

        $img = Image::make($image);
        $img->fit($width, $height, function ($constraint) {
            $constraint->upsize();
        });

        Storage::disk('public')->put($path.'/'.$originalFilename, (string) $img->encode($extension, 85));

        if (extension_loaded('gd') && function_exists('imagewebp')) {
            try {
                $webpImage = Image::make($image);
                $webpImage->fit($width, $height, function ($constraint) {
                    $constraint->upsize();
                });
                Storage::disk('public')->put($path.'/'.$webpFilename, (string) $webpImage->encode('webp', 85));

                return [
                    'original' => $path.'/'.$originalFilename,
                    'webp' => $path.'/'.$webpFilename,
                    'has_webp' => true,
                ];
            } catch (\Exception $e) {
                return [
                    'original' => $path.'/'.$originalFilename,
                    'webp' => null,
                    'has_webp' => false,
                ];
            }
        }

        return [
            'original' => $path.'/'.$originalFilename,
            'webp' => null,
            'has_webp' => false,
        ];
    }

    public function deleteImage(string $imagePath): void
    {
        if (Storage::disk('public')->exists($imagePath)) {
            Storage::disk('public')->delete($imagePath);
        }

        $webpPath = $this->getWebpPath($imagePath);
        if (Storage::disk('public')->exists($webpPath)) {
            Storage::disk('public')->delete($webpPath);
        }
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
}
