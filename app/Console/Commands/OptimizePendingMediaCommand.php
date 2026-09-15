<?php

namespace App\Console\Commands;

use App\Jobs\OptimizeMediaLibraryItem;
use App\Models\MediaLibrary;
use Illuminate\Console\Command;

class OptimizePendingMediaCommand extends Command
{
    protected $signature = 'media:optimize-pending {--limit=100 : Maximum number of images to optimize}';

    protected $description = 'Queue pending media images for optimization and AVIF conversion';

    public function handle(): int
    {
        $limit = max(1, (int) $this->option('limit'));

        $items = MediaLibrary::query()
            ->where('type', 'image')
            ->where('is_optimized', false)
            ->orderBy('id')
            ->limit($limit)
            ->get();

        if ($items->isEmpty()) {
            $this->info('No pending media files to optimize.');

            return self::SUCCESS;
        }

        foreach ($items as $item) {
            OptimizeMediaLibraryItem::dispatch($item);
        }

        $this->info("Optimization complete. Queued: {$items->count()}.");

        return self::SUCCESS;
    }
}
