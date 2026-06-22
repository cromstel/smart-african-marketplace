<?php

namespace App\Console\Commands;

use App\Models\ChatbotSession;
use App\Models\SupportTicket;
use Illuminate\Console\Command;

class PurgeChatbotData extends Command
{
    protected $signature = 'chatbot:purge-old';

    protected $description = 'Purge chatbot sessions/messages older than 30 days and closed support tickets';

    public function handle(): int
    {
        $cutoff = now()->subDays(30);

        $closedTickets = SupportTicket::query()
            ->where('status', 'closed')
            ->where('closed_at', '<', $cutoff)
            ->delete();

        $sessions = ChatbotSession::query()
            ->where(function ($query) use ($cutoff) {
                $query->where('last_active_at', '<', $cutoff)
                    ->orWhere(function ($sub) use ($cutoff) {
                        $sub->whereNull('last_active_at')
                            ->where('created_at', '<', $cutoff);
                    });
            })
            ->delete();

        $this->info("Purged {$sessions} chatbot sessions and {$closedTickets} closed support tickets.");

        return self::SUCCESS;
    }
}
