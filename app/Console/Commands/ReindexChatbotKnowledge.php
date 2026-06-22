<?php

namespace App\Console\Commands;

use App\Services\Chatbot\KnowledgeBaseService;
use Illuminate\Console\Command;

class ReindexChatbotKnowledge extends Command
{
    protected $signature = 'chatbot:reindex-knowledge';

    protected $description = 'Rebuild chatbot knowledge index from products, services, pages, and blog posts';

    public function handle(KnowledgeBaseService $service): int
    {
        $this->info('Reindexing chatbot knowledge base...');
        $service->reindexAll();
        $this->info('Knowledge base reindex complete.');

        return self::SUCCESS;
    }
}
