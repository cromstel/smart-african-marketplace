<?php

namespace App\Jobs;

use App\Services\Chatbot\KnowledgeBaseService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;

/**
 * Roadmap 3.2: previously run inline inside the chatbot:reindex-knowledge
 * command at 02:00 daily, tying up the scheduler process for the full
 * rebuild. WithoutOverlapping mirrors the Cache lock
 * KnowledgeBaseService::ensureIndexed() already uses for its own
 * just-in-time reindex path, applied here so a slow run and the next night's
 * scheduled run can't stack on top of each other.
 */
class ReindexChatbotKnowledgeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public function __construct()
    {
        $this->onQueue('chatbot');
    }

    public function middleware(): array
    {
        return [(new WithoutOverlapping('chatbot-reindex'))->releaseAfter(3600)->expireAfter(3600)];
    }

    public function handle(KnowledgeBaseService $service): void
    {
        $service->reindexAll();
    }
}
