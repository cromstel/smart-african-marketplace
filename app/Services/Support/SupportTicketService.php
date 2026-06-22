<?php

namespace App\Services\Support;

use App\Mail\SupportTicketCreated;
use App\Models\ChatbotSession;
use App\Models\Setting;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use Illuminate\Support\Facades\Mail;

class SupportTicketService
{
    public function createFromChatbotSession(ChatbotSession $session, ?string $message): SupportTicket
    {
        $ticket = SupportTicket::query()
            ->where('chatbot_session_id', $session->id)
            ->where('status', '!=', 'closed')
            ->latest('id')
            ->first();

        if (! $ticket) {
            $ticket = SupportTicket::create([
                'chatbot_session_id' => $session->id,
                'user_id' => $session->user_id,
                'subject' => 'Chatbot Handoff',
                'status' => 'open',
                'last_message_at' => now(),
            ]);

            $this->notifySupportInbox($ticket);
        }

        if ($message) {
            SupportMessage::create([
                'support_ticket_id' => $ticket->id,
                'author_role' => 'user',
                'author_id' => $session->user_id,
                'content' => $message,
            ]);
            $ticket->update(['last_message_at' => now()]);
        }

        return $ticket;
    }

    protected function notifySupportInbox(SupportTicket $ticket): void
    {
        $inbox = (string) Setting::get('support_ticket_inbox_email', '');
        if ($inbox === '') {
            return;
        }

        Mail::to($inbox)->send(new SupportTicketCreated($ticket));
    }
}
