<?php

namespace App\Console\Commands;

use App\Models\MediaLibrary;
use App\Services\ImageOptimizationService;
use Illuminate\Console\Command;

class OptimizePendingMediaCommand extends Command
{
    protected $signature = 'media:optimize-pending {--limit=100 : Maximum number of images to optimize}';

    protected $description = 'Optimize pending media images and generate webp variants';

    public function handle(ImageOptimizationService $imageOptimizationService): int
    {
        $limit = max(1, (int) $this->option('limit'));
        $optimized = 0;
        $failed = 0;

        $items = MediaLibrary::query()
            ->where('type', 'image')
            ->where(function ($query) {
                $query->where('is_optimized', false)
                    ->orWhereNull('webp_path');
            })
            ->orderBy('id')
            ->limit($limit)
            ->get();

        if ($items->isEmpty()) {
            $this->info('No pending media files to optimize.');

            return self::SUCCESS;
        }

        foreach ($items as $item) {
            $result = $imageOptimizationService->optimizeMediaLibraryItem($item);
            if ($result) {
                $optimized++;
            } else {
                $failed++;
            }
        }

        $this->info("Optimization complete. Optimized: {$optimized}, Failed: {$failed}.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
