<?php

namespace Tests\Feature;

use App\Jobs\OptimizeMediaLibraryItem;
use App\Models\MediaLibrary;
use App\Services\ImageOptimizationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Covers Roadmap 3.2: ImageOptimizationService::optimizeMediaLibraryItem()
 * catches its own exceptions and returns false rather than throwing (see
 * that method's own doc comment). This job turns a false back into a
 * thrown exception so the queue's retry/backoff actually engages instead of
 * silently accepting a failed optimization as "done".
 */
class OptimizeMediaLibraryItemTest extends TestCase
{
    use DatabaseTransactions;

    public function test_handle_throws_when_the_service_reports_failure_so_the_queue_will_retry(): void
    {
        $media = MediaLibrary::create([
            'file_name' => 'broken.jpg',
            'original_name' => 'broken.jpg',
            'file_path' => 'media/broken.jpg',
            'mime_type' => 'image/jpeg',
            'file_size' => 1000,
            'type' => 'image',
            'is_optimized' => false,
        ]);

        $service = $this->mock(ImageOptimizationService::class, function ($mock) {
            $mock->shouldReceive('optimizeMediaLibraryItem')->once()->andReturn(false);
        });

        $job = new OptimizeMediaLibraryItem($media);

        $this->expectException(\RuntimeException::class);
        $job->handle($service);
    }

    public function test_handle_does_nothing_when_the_item_was_already_optimized_by_the_time_it_runs(): void
    {
        $media = MediaLibrary::create([
            'file_name' => 'already.jpg',
            'original_name' => 'already.jpg',
            'file_path' => 'media/already.jpg',
            'mime_type' => 'image/jpeg',
            'file_size' => 1000,
            'type' => 'image',
            'is_optimized' => true,
        ]);

        $service = $this->mock(ImageOptimizationService::class, function ($mock) {
            $mock->shouldNotReceive('optimizeMediaLibraryItem');
        });

        $job = new OptimizeMediaLibraryItem($media);
        $job->handle($service);

        $this->assertTrue(true);
    }
}
