<?php

namespace App\Console\Commands;

use App\Jobs\ReindexChatbotKnowledgeJob;
use Illuminate\Console\Command;

class ReindexChatbotKnowledge extends Command
{
    protected $signature = 'chatbot:reindex-knowledge';

    protected $description = 'Queue a rebuild of the chatbot knowledge index from products, services, pages, and blog posts';

    public function handle(): int
    {
        ReindexChatbotKnowledgeJob::dispatch();
        $this->info('Knowledge base reindex queued.');

        return self::SUCCESS;
    }
}
