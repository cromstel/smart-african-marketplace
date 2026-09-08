<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * End-to-end verification of the payment webhook endpoints.
 *
 * Three properties matter here, in ascending order of how expensive they are
 * to get wrong:
 *
 *   1. A valid signature is accepted.
 *   2. A tampered body or wrong signature is rejected before any state changes.
 *   3. Replaying an identical event does not record the payment twice.
 *
 * (3) is the one that costs money when it regresses: providers retry on
 * timeout, so a duplicate delivery is normal traffic rather than an attack.
 *
 * Signatures are computed over the exact bytes that are sent. Re-encoding the
 * JSON after signing changes the body and invalidates the signature, so the
 * payload string is built once and reused.
 */
class WebhookE2ETest extends TestCase
{
    use DatabaseTransactions;

    private const STRIPE_SECRET = 'whsec_fixture_test';

    private const PAYSTACK_SECRET = 'sk_test_fixture';

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();

        Setting::set('stripe_webhook_secret', self::STRIPE_SECRET);
        Setting::set('paystack_secret_key', self::PAYSTACK_SECRET);
    }

    // ---------------------------------------------------------------- Stripe

    public function test_stripe_accepts_a_valid_signature_and_records_the_payment(): void
    {
        $order = $this->createUnpaidOrder();
        $payload = $this->stripePayload($order->id);

        $this->call('POST', '/webhooks/stripe', [], [], [], $this->serverHeaders($this->stripeHeaders($payload)), $payload)
            ->assertOk();

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'transaction_id' => 'ch_fixture_000000000001',
            'payment_method' => 'stripe',
            'status' => 'completed',
        ]);

        $this->assertSame('paid', $order->fresh()->payment_status);
    }

    public function test_stripe_rejects_an_invalid_signature(): void
    {
        $order = $this->createUnpaidOrder();
        $payload = $this->stripePayload($order->id);

        $headers = ['Stripe-Signature' => 't='.time().',v1='.str_repeat('0', 64)];

        $this->call('POST', '/webhooks/stripe', [], [], [], $this->serverHeaders($headers), $payload)
            ->assertStatus(400);

        $this->assertDatabaseMissing('payments', ['order_id' => $order->id]);
        $this->assertSame('pending', $order->fresh()->payment_status);
    }

    public function test_stripe_rejects_a_body_tampered_after_signing(): void
    {
        $order = $this->createUnpaidOrder();
        $signed = $this->stripePayload($order->id);

        // Signature computed over the original body, a different body sent.
        $tampered = str_replace('"amount": 5000', '"amount": 1', $signed);

        $this->call('POST', '/webhooks/stripe', [], [], [], $this->serverHeaders($this->stripeHeaders($signed)), $tampered)
            ->assertStatus(400);

        $this->assertDatabaseMissing('payments', ['order_id' => $order->id]);
    }

    public function test_replaying_a_stripe_event_does_not_duplicate_the_payment(): void
    {
        $order = $this->createUnpaidOrder();
        $payload = $this->stripePayload($order->id);
        $headers = $this->serverHeaders($this->stripeHeaders($payload));

        $this->call('POST', '/webhooks/stripe', [], [], [], $headers, $payload)->assertOk();
        $this->call('POST', '/webhooks/stripe', [], [], [], $headers, $payload)->assertOk();

        $this->assertSame(
            1,
            Payment::where('order_id', $order->id)->count(),
            'A replayed Stripe event must not create a second payment row.'
        );
    }

    // -------------------------------------------------------------- Paystack

    public function test_paystack_accepts_a_valid_signature_and_records_the_payment(): void
    {
        $order = $this->createUnpaidOrder();
        $payload = $this->paystackPayload($order->id);

        $this->call('POST', '/webhooks/paystack', [], [], [], $this->serverHeaders([
            'X-Paystack-Signature' => hash_hmac('sha512', $payload, self::PAYSTACK_SECRET),
        ]), $payload)->assertOk();

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'transaction_id' => 'psk_fixture_000000000001',
            'payment_method' => 'paystack',
            'status' => 'completed',
        ]);

        $this->assertSame('paid', $order->fresh()->payment_status);
    }

    public function test_paystack_rejects_an_invalid_signature(): void
    {
        $order = $this->createUnpaidOrder();
        $payload = $this->paystackPayload($order->id);

        $this->call('POST', '/webhooks/paystack', [], [], [], $this->serverHeaders([
            'X-Paystack-Signature' => hash_hmac('sha512', $payload, 'the-wrong-secret'),
        ]), $payload)->assertStatus(400);

        $this->assertDatabaseMissing('payments', ['order_id' => $order->id]);
        $this->assertSame('pending', $order->fresh()->payment_status);
    }

    public function test_paystack_rejects_a_missing_signature(): void
    {
        $order = $this->createUnpaidOrder();
        $payload = $this->paystackPayload($order->id);

        $this->call('POST', '/webhooks/paystack', [], [], [], $this->serverHeaders([]), $payload)
            ->assertStatus(400);

        $this->assertDatabaseMissing('payments', ['order_id' => $order->id]);
    }

    public function test_replaying_a_paystack_event_does_not_duplicate_the_payment(): void
    {
        $order = $this->createUnpaidOrder();
        $payload = $this->paystackPayload($order->id);
        $headers = $this->serverHeaders([
            'X-Paystack-Signature' => hash_hmac('sha512', $payload, self::PAYSTACK_SECRET),
        ]);

        $this->call('POST', '/webhooks/paystack', [], [], [], $headers, $payload)->assertOk();
        $this->call('POST', '/webhooks/paystack', [], [], [], $headers, $payload)->assertOk();

        $this->assertSame(
            1,
            Payment::where('order_id', $order->id)->count(),
            'A replayed Paystack event must not create a second payment row.'
        );
    }

    // --------------------------------------------------------------- Helpers

    /**
     * An order with total 0 so the loyalty-points path is skipped: this test is
     * about signature handling and idempotency, not rewards. Mail is faked in
     * setUp for the same reason.
     */
    private function createUnpaidOrder(): Order
    {
        $user = User::factory()->create();

        return Order::create([
            'order_number' => 'TEST-'.uniqid(),
            'user_id' => $user->id,
            'subtotal' => 0,
            'tax' => 0,
            'shipping_cost' => 0,
            'discount' => 0,
            'total' => 0,
            'payment_status' => 'pending',
            'status' => 'pending',
            'payment_method' => 'card',
        ]);
    }

    private function fixture(string $name): string
    {
        $path = base_path("tests/Fixtures/webhooks/{$name}.json");

        $this->assertFileExists($path, "Missing webhook fixture: {$path}");

        return (string) file_get_contents($path);
    }

    private function stripePayload(int $orderId): string
    {
        return $this->withOrderId($this->fixture('stripe-charge-succeeded'), $orderId);
    }

    private function paystackPayload(int $orderId): string
    {
        return $this->withOrderId($this->fixture('paystack-charge-success'), $orderId);
    }

    /**
     * Swaps the fixture's placeholder order id for a real one without
     * re-encoding, so byte-for-byte control of the signed body is retained.
     */
    private function withOrderId(string $payload, int $orderId): string
    {
        return str_replace('"order_id": "1"', '"order_id": "'.$orderId.'"', $payload);
    }

    /**
     * @return array<string, string>
     */
    private function stripeHeaders(string $signedBody): array
    {
        $timestamp = time();
        $signature = hash_hmac('sha256', "{$timestamp}.{$signedBody}", self::STRIPE_SECRET);

        return ['Stripe-Signature' => "t={$timestamp},v1={$signature}"];
    }

    /**
     * Converts header names into the $server format expected by call().
     *
     * @param  array<string, string>  $headers
     * @return array<string, string>
     */
    private function serverHeaders(array $headers): array
    {
        $server = ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'];

        foreach ($headers as $name => $value) {
            $server['HTTP_'.strtoupper(str_replace('-', '_', $name))] = $value;
        }

        return $server;
    }
}
