<?php

namespace App\Events;

use App\Models\ChatbotMessage;
use App\Models\ChatbotSession;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatbotMessageCreated implements ShouldBroadcastNow
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public ChatbotMessage $message) {}

    public function broadcastOn(): Channel
    {
        $sessionKey = $this->message->session?->session_key;
        if (! $sessionKey) {
            $sessionKey = ChatbotSession::query()
                ->whereKey($this->message->chatbot_session_id)
                ->value('session_key');
        }

        return new Channel('chatbot.session.'.($sessionKey ?: 'unknown'));
    }

    public function broadcastAs(): string
    {
        return 'ChatbotMessageCreated';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->id,
            'role' => $this->message->role,
            'content' => $this->message->content,
            'created_at' => $this->message->created_at?->toISOString(),
        ];
    }
}
