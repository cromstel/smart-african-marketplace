<?php

namespace App\Console\Commands;

use App\Models\AboutPageContent;
use App\Models\Brand;
use App\Models\HeroSection;
use App\Models\MediaLibrary;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariation;
use App\Models\SalonService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class BackfillMediaLibrary extends Command
{
    protected $signature = 'media:backfill';

    protected $description = 'Backfill media_library entries for existing uploaded files';

    public function handle(): int
    {
        $created = 0;

        ProductImage::query()->select(['id', 'image_path'])->chunkById(200, function ($items) use (&$created) {
            foreach ($items as $item) {
                $created += $this->ensureMedia($item->image_path, 'product_image', $item->id);
            }
        });

        Product::query()->select(['id', 'featured_image'])->whereNotNull('featured_image')->chunkById(200, function ($items) use (&$created) {
            foreach ($items as $item) {
                $created += $this->ensureMedia($item->featured_image, 'product', $item->id);
            }
        });

        ProductVariation::query()->select(['id', 'image'])->whereNotNull('image')->chunkById(200, function ($items) use (&$created) {
            foreach ($items as $item) {
                $created += $this->ensureMedia($item->image, 'product_variation', $item->id);
            }
        });

        Brand::query()->select(['id', 'logo'])->whereNotNull('logo')->chunkById(200, function ($items) use (&$created) {
            foreach ($items as $item) {
                $created += $this->ensureMedia($item->logo, 'brand', $item->id);
            }
        });

        HeroSection::query()->select(['id', 'background_image'])->whereNotNull('background_image')->chunkById(200, function ($items) use (&$created) {
            foreach ($items as $item) {
                $created += $this->ensureMedia($item->background_image, 'hero_section', $item->id);
            }
        });

        AboutPageContent::query()->select(['id', 'image'])->whereNotNull('image')->chunkById(200, function ($items) use (&$created) {
            foreach ($items as $item) {
                $created += $this->ensureMedia($item->image, 'about_page_content', $item->id);
            }
        });

        SalonService::query()->select(['id', 'image'])->whereNotNull('image')->chunkById(200, function ($items) use (&$created) {
            foreach ($items as $item) {
                $created += $this->ensureMedia($item->image, 'salon_service', $item->id);
            }
        });

        $this->info("Media backfill complete. Created {$created} new media records.");

        return self::SUCCESS;
    }

    private function ensureMedia(?string $path, string $context, int $contextId): int
    {
        if (! $path) {
            return 0;
        }

        if (MediaLibrary::where('file_path', $path)->exists()) {
            return 0;
        }

        if (! Storage::disk('public')->exists($path)) {
            return 0;
        }

        $fullPath = Storage::disk('public')->path($path);
        $mimeType = 'application/octet-stream';
        $size = 0;
        try {
            $mimeType = Storage::disk('public')->mimeType($path) ?: 'application/octet-stream';
        } catch (\Throwable $e) {
        }
        try {
            $size = Storage::disk('public')->size($path) ?: 0;
        } catch (\Throwable $e) {
        }
        $dimensions = @getimagesize($fullPath) ?: [null, null];

        MediaLibrary::create([
            'file_name' => basename($path),
            'original_name' => basename($path),
            'file_path' => $path,
            'webp_path' => null,
            'mime_type' => $mimeType,
            'file_size' => $size,
            'width' => $dimensions[0],
            'height' => $dimensions[1],
            'type' => str_starts_with($mimeType, 'image/') ? 'image' : 'other',
            'context' => $context,
            'context_id' => $contextId,
        ]);

        return 1;
    }
}
