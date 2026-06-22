<?php

namespace App\Http\Controllers;

use App\Events\ChatbotMessageCreated;
use App\Models\ChatbotMessage;
use App\Services\Chatbot\ChatbotService;
use App\Services\Chatbot\KnowledgeBaseService;
use App\Services\Support\SupportTicketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    public function session(Request $request, ChatbotService $chatbot): JsonResponse
    {
        if (! \App\Models\Setting::get('chatbot_enabled', true)) {
            return response()->json(['message' => 'Chatbot disabled'], 403);
        }

        $sessionKey = $request->input('session_key');
        $userId = auth()->id();
        if ($sessionKey) {
            $existing = $chatbot->findSession($sessionKey);
            if ($existing) {
                if (! $this->canAccessSession($existing, $userId)) {
                    return response()->json(['message' => 'Session access denied'], 403);
                }
                $existing->update(['last_active_at' => now()]);

                return response()->json([
                    'session_key' => $existing->session_key,
                    'greeting' => $chatbot->greeting(),
                    'handoff_active' => (bool) $existing->handoff_active,
                ]);
            }
        }

        $session = $chatbot->ensureSession(
            $sessionKey,
            $userId,
            $request->ip(),
            (string) $request->userAgent()
        );

        return response()->json([
            'session_key' => $session->session_key,
            'greeting' => $chatbot->greeting(),
            'handoff_active' => (bool) $session->handoff_active,
        ]);
    }

    public function message(Request $request, ChatbotService $chatbot, KnowledgeBaseService $knowledge, SupportTicketService $tickets): JsonResponse
    {
        if (! \App\Models\Setting::get('chatbot_enabled', true)) {
            return response()->json(['message' => 'Chatbot disabled'], 403);
        }

        $validated = $request->validate([
            'session_key' => 'required|string',
            'message' => 'required|string|max:2000',
        ]);

        $session = $chatbot->findSession($validated['session_key']);
        if (! $session) {
            return response()->json(['message' => 'Session not found'], 404);
        }
        if (! $this->canAccessSession($session, auth()->id())) {
            return response()->json(['message' => 'Session access denied'], 403);
        }
        $session->update(['last_active_at' => now()]);

        $sanitizedMessage = $chatbot->sanitizeMessage($validated['message']);
        $redactedMessage = $chatbot->redactPii($sanitizedMessage);
        if ($redactedMessage === '') {
            return response()->json(['message' => 'Message cannot be empty'], 422);
        }

        ChatbotMessage::create([
            'chatbot_session_id' => $session->id,
            'role' => 'user',
            'content' => $redactedMessage,
        ]);

        if ($session->handoff_active) {
            $systemMessage = ChatbotMessage::create([
                'chatbot_session_id' => $session->id,
                'role' => 'system',
                'content' => 'Thanks! A human agent will respond shortly.',
            ]);
            event(new ChatbotMessageCreated($systemMessage));
            $tickets->createFromChatbotSession($session, $redactedMessage);

            return response()->json([
                'message' => $systemMessage->content,
                'source' => 'handoff',
            ]);
        }

        $knowledge->ensureIndexed();
        $contextChunks = $knowledge->search($redactedMessage, 4);
        $historyLimit = (int) \App\Models\Setting::get('chatbot_context_message_limit', 8);
        $history = $session->messages()
            ->whereIn('role', ['user', 'assistant'])
            ->orderByDesc('id')
            ->limit($historyLimit + 1)
            ->get()
            ->reverse()
            ->map(fn ($message) => ['role' => $message->role, 'content' => $message->content])
            ->values()
            ->all();
        if (! empty($history)) {
            $last = end($history);
            if (($last['role'] ?? null) === 'user' && ($last['content'] ?? '') === $redactedMessage) {
                array_pop($history);
            }
        }
        $reply = $chatbot->reply($redactedMessage, $contextChunks->all(), $history, $session->id);
        $sources = $contextChunks->map(fn ($chunk) => [
            'document_id' => $chunk->knowledge_document_id,
            'excerpt' => mb_substr($chunk->content, 0, 180),
        ])->values()->all();

        ChatbotMessage::create([
            'chatbot_session_id' => $session->id,
            'role' => $reply['source'] === 'handoff' ? 'system' : 'assistant',
            'content' => $reply['message'],
            'sources' => $sources,
        ]);

        if ($reply['source'] === 'handoff') {
            $session->update(['handoff_active' => true]);
            $tickets->createFromChatbotSession($session, $redactedMessage);
        }

        return response()->json([
            'message' => $reply['message'],
            'source' => $reply['source'],
            'sources' => $sources,
        ]);
    }

    public function messages(Request $request, ChatbotService $chatbot): JsonResponse
    {
        $validated = $request->validate([
            'session_key' => 'required|string',
            'after_id' => 'nullable|integer|min:0',
        ]);

        $session = $chatbot->findSession($validated['session_key']);
        if (! $session) {
            return response()->json(['message' => 'Session not found'], 404);
        }
        if (! $this->canAccessSession($session, auth()->id())) {
            return response()->json(['message' => 'Session access denied'], 403);
        }
        $session->update(['last_active_at' => now()]);

        $afterId = (int) ($validated['after_id'] ?? 0);
        $messages = $session->messages()
            ->where('id', '>', $afterId)
            ->orderBy('id')
            ->get(['id', 'role', 'content', 'created_at']);

        return response()->json([
            'messages' => $messages,
        ]);
    }

    public function handoff(Request $request, ChatbotService $chatbot, SupportTicketService $tickets): JsonResponse
    {
        $validated = $request->validate([
            'session_key' => 'required|string',
        ]);

        $session = $chatbot->findSession($validated['session_key']);
        if (! $session) {
            return response()->json(['message' => 'Session not found'], 404);
        }
        if (! $this->canAccessSession($session, auth()->id())) {
            return response()->json(['message' => 'Session access denied'], 403);
        }

        $session->update(['last_active_at' => now()]);
        $session->update(['handoff_active' => true]);

        $message = ChatbotMessage::create([
            'chatbot_session_id' => $session->id,
            'role' => 'system',
            'content' => 'A human agent will join the chat shortly.',
        ]);
        event(new ChatbotMessageCreated($message));

        $lastUserMessage = $session->messages()
            ->where('role', 'user')
            ->latest('id')
            ->value('content');
        $tickets->createFromChatbotSession($session, $lastUserMessage);

        return response()->json([
            'message' => 'A human agent will join the chat shortly.',
            'source' => 'handoff',
        ]);
    }

    private function canAccessSession(\App\Models\ChatbotSession $session, ?int $userId): bool
    {
        if ($session->user_id && $userId !== $session->user_id) {
            return false;
        }

        if (! $session->user_id && $userId !== null) {
            return false;
        }

        if ($session->user_id && $userId === null) {
            return false;
        }

        return true;
    }
}
