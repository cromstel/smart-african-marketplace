<?php

namespace App\Services\Chatbot;

use App\Models\ChatbotSession;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ChatbotService
{
    public function ensureSession(?string $sessionKey, ?int $userId, ?string $ip, ?string $ua): ChatbotSession
    {
        if ($sessionKey) {
            $session = ChatbotSession::where('session_key', $sessionKey)->first();
            if ($session) {
                $session->update(['last_active_at' => now()]);

                return $session;
            }
        }

        return ChatbotSession::create([
            'session_key' => Str::uuid()->toString(),
            'user_id' => $userId,
            'ip_address' => $ip,
            'user_agent' => $ua,
            'last_active_at' => now(),
        ]);
    }

    public function findSession(string $sessionKey): ?ChatbotSession
    {
        return ChatbotSession::where('session_key', $sessionKey)->first();
    }

    public function greeting(): string
    {
        return (string) Setting::get('chatbot_greeting', 'Hello! Welcome to African Market. How can I help you today?');
    }

    public function sanitizeMessage(string $message): string
    {
        $message = preg_replace('/\s+/u', ' ', trim($message)) ?? $message;

        return $message;
    }

    public function redactPii(string $message): string
    {
        $redacted = $message;

        $redacted = preg_replace('/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/i', '[redacted email]', $redacted) ?? $redacted;
        $redacted = preg_replace('/\+?\d[\d\s().-]{6,}\d/', '[redacted phone]', $redacted) ?? $redacted;

        return $redacted;
    }

    public function reply(string $message, array $contextChunks, array $historyMessages = [], ?int $sessionId = null): array
    {
        $fallbackEnabled = (bool) Setting::get('chatbot_fallback_enabled', true);
        $contentContext = collect($contextChunks)->map(fn ($chunk) => $chunk->content)->implode("\n---\n");

        if ($contentContext === '') {
            if ($fallbackEnabled) {
                return [
                    'message' => $this->fallbackReply($message),
                    'source' => 'fallback',
                ];
            }

            return [
                'message' => $this->handoffMessage(),
                'source' => 'handoff',
            ];
        }

        if ($this->isTechnicalRequest($message)) {
            return [
                'message' => $this->handoffMessage(),
                'source' => 'handoff',
            ];
        }

        try {
            $apiKey = (string) config('services.openai.api_key');
            if ($apiKey === '') {
                throw new \RuntimeException('Missing API key');
            }

            $payload = [
                'model' => Setting::get('chatbot_openai_model', 'gpt-4o-mini'),
                'messages' => $this->buildPromptMessages($message, $contentContext, $historyMessages),
                'temperature' => 0.3,
                'max_tokens' => 450,
            ];

            $startedAt = microtime(true);
            $response = Http::withToken($apiKey)
                ->timeout(15)
                ->retry(2, 250)
                ->post('https://api.openai.com/v1/chat/completions', $payload);

            if ($response->failed()) {
                $latencyMs = (int) round((microtime(true) - $startedAt) * 1000);
                Log::warning('Chatbot LLM request failed', [
                    'status' => $response->status(),
                    'latency_ms' => $latencyMs,
                    'session_id' => $sessionId,
                ]);
                throw new \RuntimeException('LLM request failed');
            }

            $text = data_get($response->json(), 'choices.0.message.content');
            if (! is_string($text) || trim($text) === '') {
                throw new \RuntimeException('Empty LLM reply');
            }

            $text = trim($text);
            if (str_starts_with($text, 'HANDOFF')) {
                return [
                    'message' => $this->handoffMessage(),
                    'source' => 'handoff',
                ];
            }

            return [
                'message' => $text,
                'source' => 'llm',
            ];
        } catch (\Throwable $e) {
            Log::warning('Chatbot LLM exception', [
                'error' => $e->getMessage(),
                'session_id' => $sessionId,
            ]);
            if ($fallbackEnabled) {
                if (! empty($contextChunks)) {
                    return [
                        'message' => $this->fallbackFromContext($contextChunks),
                        'source' => 'context',
                    ];
                }

                return [
                    'message' => $this->fallbackReply($message),
                    'source' => 'fallback',
                ];
            }

            return [
                'message' => $this->handoffMessage(),
                'source' => 'handoff',
            ];
        }
    }

    private function fallbackReply(string $message): string
    {
        $msg = Str::lower($message);
        if (str_contains($msg, 'shipping')) {
            return 'Hello! Shipping times vary by vendor and destination. You can check shipping details on each product checkout flow.';
        }
        if (str_contains($msg, 'return') || str_contains($msg, 'refund')) {
            return 'Hello! You can request returns from your orders page. Eligibility depends on vendor return policies and order status.';
        }
        if (str_contains($msg, 'payment')) {
            return 'Hello! We support card, Paystack, mobile money, and cash on delivery in supported regions.';
        }

        return 'Hello! I can help with products, services, orders, payments, and account questions. What do you need today?';
    }

    private function fallbackFromContext(array $contextChunks): string
    {
        $chunk = collect($contextChunks)->first();
        if (! $chunk) {
            return $this->handoffMessage();
        }

        $excerpt = trim(mb_substr($chunk->content, 0, 350));
        if ($excerpt === '') {
            return $this->handoffMessage();
        }

        return 'Here is what I found on our site: '.$excerpt;
    }

    private function handoffMessage(): string
    {
        return 'This looks like a question that needs a human agent. Please contact support at support@africanmarket.com and we will assist you.';
    }

    private function isTechnicalRequest(string $message): bool
    {
        $msg = Str::lower($message);
        $keywords = [
            'api', 'sdk', 'integration', 'webhook', 'endpoint', 'stack trace', 'exception',
            'error log', 'sql', 'database', 'migration', 'deployment', 'server', 'debug',
            'oauth', 'token', 'jwt', 'ssl', 'dns', 'cache', 'queue', 'artisan',
        ];

        foreach ($keywords as $keyword) {
            if (str_contains($msg, $keyword)) {
                return true;
            }
        }

        return false;
    }

    private function buildPromptMessages(string $message, string $contentContext, array $historyMessages): array
    {
        $messages = [
            [
                'role' => 'system',
                'content' => "You are a support assistant. Answer ONLY using the context below. If the answer is not explicitly in the context, respond with the single word HANDOFF.\n\nContext:\n".$contentContext,
            ],
        ];

        foreach ($historyMessages as $history) {
            $role = $history['role'] ?? null;
            $content = $history['content'] ?? null;
            if (! in_array($role, ['user', 'assistant'], true) || ! is_string($content) || trim($content) === '') {
                continue;
            }
            $messages[] = ['role' => $role, 'content' => $content];
        }

        $messages[] = ['role' => 'user', 'content' => $message];

        return $messages;
    }
}
