<?php

namespace Tests\Unit\Services;

use App\Services\Chatbot\ChatbotService;
use PHPUnit\Framework\TestCase;

class ChatbotServiceRedactionTest extends TestCase
{
    public function test_redact_output_removes_email_card_ipv4_and_phone_details(): void
    {
        $service = new ChatbotService;

        $redacted = $service->redactOutput(
            'Email vendor@example.com; card 4111 1111 1111 1111; IP 192.168.1.10; phone +233 20 123 4567.'
        );

        $this->assertStringNotContainsString('vendor@example.com', $redacted);
        $this->assertStringNotContainsString('4111 1111 1111 1111', $redacted);
        $this->assertStringNotContainsString('192.168.1.10', $redacted);
        $this->assertStringNotContainsString('+233 20 123 4567', $redacted);
        $this->assertStringContainsString('[redacted email]', $redacted);
        $this->assertStringContainsString('[redacted card]', $redacted);
        $this->assertStringContainsString('[redacted ip]', $redacted);
        $this->assertStringContainsString('[redacted phone]', $redacted);
    }

    public function test_redact_output_redacts_prefixed_and_grouped_phone_formats(): void
    {
        $service = new ChatbotService;

        $redacted = $service->redactOutput(
            'Phones: 020 1234 5678, 020 123 456, and 00 44 20 7946 0958.'
        );

        $this->assertStringNotContainsString('020 1234 5678', $redacted);
        $this->assertStringNotContainsString('020 123 456', $redacted);
        $this->assertStringNotContainsString('00 44 20 7946 0958', $redacted);
        $this->assertSame(3, substr_count($redacted, '[redacted phone]'));
    }

    public function test_redact_output_preserves_prices_quantities_order_ids_and_iso_dates(): void
    {
        $service = new ChatbotService;
        $text = 'Price 1 500 000.00; compact 1500000; thousands 12,500; quantity 12; order ORD-123456; segmented order ORD-1234-5678-90; date 2026-11-22.';

        $this->assertSame($text, $service->redactOutput($text));
    }

    public function test_existing_inbound_redaction_behavior_is_unchanged(): void
    {
        $service = new ChatbotService;

        $this->assertSame(
            'Email [redacted email]; price [redacted phone].',
            $service->redactPii('Email jane@example.com; price 1 500 000.')
        );
    }
}
