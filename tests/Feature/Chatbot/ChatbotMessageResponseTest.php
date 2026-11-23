<?php

namespace Tests\Feature\Chatbot;

use App\Events\ChatbotMessageCreated;
use App\Models\ChatbotMessage;
use App\Models\ChatbotSession;
use App\Models\KnowledgeChunk;
use App\Models\KnowledgeDocument;
use App\Models\Setting;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ChatbotMessageResponseTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::set('chatbot_enabled', true);
        Setting::set('chatbot_fallback_enabled', true);
        config(['services.openai.api_key' => 'test-openai-key']);

        Event::fake([ChatbotMessageCreated::class]);
        Mail::fake();
        Http::preventStrayRequests();
    }

    public function test_message_response_returns_the_persisted_assistant_message_id(): void
    {
        $session = $this->createSession();

        Http::fake([
            'api.openai.com/*' => Http::response(['error' => 'unavailable'], 500),
        ]);

        $response = $this->postJson(route('chatbot.message'), [
            'session_key' => $session->session_key,
            'message' => 'Tell me about your marketplace.',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['message', 'message_id']);

        $messageId = $response->json('message_id');
        $persisted = ChatbotMessage::query()->find($messageId);

        $this->assertNotNull($persisted);
        $this->assertSame($session->id, $persisted->chatbot_session_id);
        $this->assertSame('assistant', $persisted->role);
        $this->assertSame($response->json('message'), $persisted->content);
    }

    public function test_llm_assistant_output_redacts_pii_exactly_once(): void
    {
        $session = $this->createSession();
        $content = 'kbpiimarker documented vendor contact policy.';
        $this->createKnowledgeDocument($content, 'Vendor contact policy');

        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [[
                    'message' => [
                        'content' => 'Email vendor@example.com; card 4111 1111 1111 1111; IP 192.168.1.10; phone +233 20 123 4567.',
                    ],
                ]],
            ], 200),
        ]);

        $response = $this->postJson(route('chatbot.message'), [
            'session_key' => $session->session_key,
            'message' => 'kbpiimarker vendor contact policy',
        ]);

        $response->assertOk();

        $returnedContent = (string) $response->json('message');
        $persisted = ChatbotMessage::query()
            ->where('chatbot_session_id', $session->id)
            ->where('role', 'assistant')
            ->latest('id')
            ->first();

        $this->assertNotNull($persisted);
        $this->assertSame($returnedContent, $persisted->content);
        $this->assertSame(1, substr_count($returnedContent, '[redacted email]'));
        $this->assertSame(1, substr_count($returnedContent, '[redacted card]'));
        $this->assertSame(1, substr_count($returnedContent, '[redacted ip]'));
        $this->assertSame(1, substr_count($returnedContent, '[redacted phone]'));
    }

    public function test_handoff_response_returns_the_created_system_message_id(): void
    {
        $session = $this->createSession();

        $response = $this->postJson(route('chatbot.handoff'), [
            'session_key' => $session->session_key,
        ]);

        $response->assertOk()
            ->assertJsonStructure(['message', 'message_id']);

        $messageId = $response->json('message_id');
        $persisted = ChatbotMessage::query()->find($messageId);

        $this->assertNotNull($persisted);
        $this->assertSame($session->id, $persisted->chatbot_session_id);
        $this->assertSame('system', $persisted->role);
        $this->assertSame($response->json('message'), $persisted->content);
    }

    public function test_message_response_returns_the_system_message_id_when_handoff_is_already_active(): void
    {
        $session = $this->createSession(handoffActive: true);

        $response = $this->postJson(route('chatbot.message'), [
            'session_key' => $session->session_key,
            'message' => 'I still need help.',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['message', 'message_id']);

        $messageId = $response->json('message_id');
        $persisted = ChatbotMessage::query()->find($messageId);

        $this->assertNotNull($persisted);
        $this->assertSame($session->id, $persisted->chatbot_session_id);
        $this->assertSame('system', $persisted->role);
        $this->assertSame($response->json('message'), $persisted->content);
    }

    public function test_polled_messages_include_sources_for_assistant_messages(): void
    {
        $session = $this->createSession();
        $assistantMessage = ChatbotMessage::create([
            'chatbot_session_id' => $session->id,
            'role' => 'assistant',
            'content' => 'Here is the documented policy.',
            'sources' => [[
                'document_id' => 42,
                'excerpt' => 'The policy excerpt.',
            ]],
        ]);

        $response = $this->getJson(route('chatbot.messages', [
            'session_key' => $session->session_key,
            'after_id' => 0,
        ]));

        $response->assertOk()
            ->assertJsonPath('messages.0.id', $assistantMessage->id)
            ->assertJsonPath('messages.0.sources.0.document_id', 42)
            ->assertJsonPath('messages.0.sources.0.excerpt', 'The policy excerpt.');
    }

    public function test_fallback_assistant_output_and_sources_redact_pii_from_knowledge_content(): void
    {
        $session = $this->createSession();
        $content = 'kbpiimarker '.str_repeat('safe ', 30).'email vendor@example.com phone +233 20 123 4567.';
        $this->createKnowledgeDocument($content, 'Vendor support details');

        Http::fake([
            'api.openai.com/*' => Http::response(['error' => 'unavailable'], 500),
        ]);

        $response = $this->postJson(route('chatbot.message'), [
            'session_key' => $session->session_key,
            'message' => 'kbpiimarker vendor support details',
        ]);

        $response->assertOk();

        $returnedContent = (string) $response->json('message');
        $returnedExcerpt = (string) $response->json('sources.0.excerpt');
        $persisted = ChatbotMessage::query()
            ->where('chatbot_session_id', $session->id)
            ->where('role', 'assistant')
            ->latest('id')
            ->first();

        $this->assertNotNull($persisted);
        $this->assertStringNotContainsString('vendor@example.com', $returnedContent);
        $this->assertStringNotContainsString('+233 20 123 4567', $returnedContent);
        $this->assertStringNotContainsString('vendor@example.com', $persisted->content);
        $this->assertStringNotContainsString('+233 20 123 4567', $persisted->content);
        $this->assertStringNotContainsString('vendor@example.com', $returnedExcerpt);
        $this->assertStringNotContainsString('vendor@', $returnedExcerpt);
        $this->assertStringNotContainsString('+233 20 123 4567', $returnedExcerpt);
        $this->assertStringNotContainsString('vendor@example.com', $persisted->sources[0]['excerpt']);
        $this->assertStringNotContainsString('vendor@', $persisted->sources[0]['excerpt']);
        $this->assertStringNotContainsString('+233 20 123 4567', $persisted->sources[0]['excerpt']);
    }

    private function createKnowledgeDocument(string $content, string $title): KnowledgeDocument
    {
        $document = KnowledgeDocument::create([
            'source_type' => 'test-'.uniqid('', true),
            'source_id' => 1,
            'title' => $title,
            'content' => $content,
            'meta' => [],
        ]);
        KnowledgeChunk::create([
            'knowledge_document_id' => $document->id,
            'chunk_index' => 0,
            'content' => $content,
            'embedding' => null,
        ]);

        return $document;
    }

    private function createSession(bool $handoffActive = false): ChatbotSession
    {
        return ChatbotSession::create([
            'session_key' => 'chatbot-test-'.uniqid('', true),
            'user_id' => null,
            'last_active_at' => now(),
            'handoff_active' => $handoffActive,
        ]);
    }
}
