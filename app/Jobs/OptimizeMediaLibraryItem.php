<?php

namespace App\Jobs;

use App\Models\MediaLibrary;
use App\Services\ImageOptimizationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Roadmap 3.2: previously optimized inline inside OptimizePendingMediaCommand,
 * up to 200 images serially in one process per scheduled run. Each image is
 * now its own job, so a slow or stuck image no longer blocks the rest of the
 * batch, and a transient failure (e.g. the file briefly locked by another
 * upload) gets a real retry instead of waiting for tomorrow's cron.
 *
 * ImageOptimizationService::optimizeMediaLibraryItem() already catches its
 * own exceptions and returns false rather than throwing (see its own
 * doc comment) -- this job turns that false back into a thrown exception so
 * the queue's retry/backoff actually engages, and a permanent failure still
 * lands in `failed_jobs` for the "rising failed_jobs count" signal the
 * queue-workers runbook already watches for.
 */
class OptimizeMediaLibraryItem implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [30, 120, 300];

    public function __construct(public MediaLibrary $media)
    {
        $this->onQueue('media');
    }

    public function handle(ImageOptimizationService $service): void
    {
        $media = $this->media->fresh();

        if (! $media || $media->is_optimized) {
            return;
        }

        if (! $service->optimizeMediaLibraryItem($media)) {
            throw new \RuntimeException("Media optimization failed for media #{$media->id}");
        }
    }
}
