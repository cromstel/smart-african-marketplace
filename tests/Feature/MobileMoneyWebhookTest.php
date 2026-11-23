<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Mobile money (MTN, M-Pesa) callback endpoint.
 *
 * This endpoint marks payments completed and orders paid. It previously
 * accepted *any* request: no signature, no shared secret, no middleware, and
 * CSRF-exempt like the other webhooks. Anyone who could reach it with a known
 * transaction reference could mark an order paid without paying -- while the
 * Stripe, Paystack and Swish handlers all verified their callers.
 *
 * It now authenticates with an HMAC of the raw body, and fails closed when no
 * secret is configured. These tests hold that shut.
 */
class MobileMoneyWebhookTest extends TestCase
{
    use DatabaseTransactions;

    private const SECRET = 'momo_test_secret';

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        config()->set('payment.mobile_money.webhook_secret', self::SECRET);
        config()->set('payment.mobile_money.signature_header', 'X-Momo-Signature');
    }

    // ------------------------------------------------------------- Auth gate

    public function test_it_refuses_callbacks_when_no_secret_is_configured(): void
    {
        config()->set('payment.mobile_money.webhook_secret', null);

        [$order, $payment] = $this->pendingMobileMoneyOrder();
        $body = $this->body($payment->transaction_id, 'success');

        $this->postRaw('/webhooks/momo/mtn', $body, $this->sign($body))
            ->assertStatus(503);

        $this->assertSame('pending', $payment->fresh()->status);
        $this->assertSame('pending', $order->fresh()->payment_status);
    }

    public function test_it_rejects_a_callback_with_no_signature(): void
    {
        [$order, $payment] = $this->pendingMobileMoneyOrder();
        $body = $this->body($payment->transaction_id, 'success');

        $this->postRaw('/webhooks/momo/mtn', $body)
            ->assertStatus(401);

        $this->assertSame('pending', $payment->fresh()->status);
        $this->assertSame('pending', $order->fresh()->payment_status);
    }

    public function test_it_rejects_a_forged_signature(): void
    {
        [$order, $payment] = $this->pendingMobileMoneyOrder();
        $body = $this->body($payment->transaction_id, 'success');

        $this->postRaw('/webhooks/momo/mtn', $body, ['X-Momo-Signature' => hash_hmac('sha256', $body, 'wrong-secret')])
            ->assertStatus(401);

        $this->assertSame(
            'pending',
            $order->fresh()->payment_status,
            'An unauthenticated caller marked an order paid.'
        );
    }

    public function test_it_rejects_a_body_tampered_after_signing(): void
    {
        [$order, $payment] = $this->pendingMobileMoneyOrder();
        $signed = $this->body($payment->transaction_id, 'failed');
        $tampered = $this->body($payment->transaction_id, 'success');

        $this->postRaw('/webhooks/momo/mtn', $tampered, $this->sign($signed))
            ->assertStatus(401);

        $this->assertSame('pending', $order->fresh()->payment_status);
    }

    // ------------------------------------------------------------- Behaviour

    public function test_an_authenticated_mtn_success_marks_the_order_paid(): void
    {
        [$order, $payment] = $this->pendingMobileMoneyOrder();
        $body = $this->body($payment->transaction_id, 'success');

        $this->postRaw('/webhooks/momo/mtn', $body, $this->sign($body))->assertOk();

        $this->assertSame('completed', $payment->fresh()->status);
        $this->assertSame('paid', $order->fresh()->payment_status);
    }

    public function test_a_failed_callback_does_not_mark_the_order_paid(): void
    {
        [$order, $payment] = $this->pendingMobileMoneyOrder();
        $body = $this->body($payment->transaction_id, 'failed');

        $this->postRaw('/webhooks/momo/mtn', $body, $this->sign($body))->assertOk();

        $this->assertSame('failed', $payment->fresh()->status);
        $this->assertNotSame(
            'paid',
            $order->fresh()->payment_status,
            'A failed mobile money callback marked the order paid.'
        );
    }

    public function test_an_mpesa_stk_callback_is_understood(): void
    {
        [$order, $payment] = $this->pendingMobileMoneyOrder();

        $body = json_encode([
            'Body' => ['stkCallback' => [
                'ResultCode' => 0,
                'ResultDesc' => 'The service request is processed successfully.',
                'CheckoutRequestID' => $payment->transaction_id,
            ]],
        ]);

        $this->postRaw('/webhooks/momo/mpesa', $body, $this->sign($body))->assertOk();

        $this->assertSame('paid', $order->fresh()->payment_status);
    }

    public function test_replaying_a_callback_does_not_duplicate_the_payment(): void
    {
        [$order, $payment] = $this->pendingMobileMoneyOrder();
        $body = $this->body($payment->transaction_id, 'success');
        $headers = $this->sign($body);

        $this->postRaw('/webhooks/momo/mtn', $body, $headers)->assertOk();
        $this->postRaw('/webhooks/momo/mtn', $body, $headers)->assertOk();

        $this->assertSame(1, Payment::where('order_id', $order->id)->count());
        $this->assertSame('paid', $order->fresh()->payment_status);
    }

    public function test_an_unsupported_provider_is_refused(): void
    {
        $body = $this->body('anything', 'success');

        $this->postRaw('/webhooks/momo/bitcoin', $body, $this->sign($body))
            ->assertStatus(422);
    }

    public function test_an_unknown_reference_is_acknowledged_without_changing_anything(): void
    {
        // Providers retry on non-2xx, so an unknown reference is acknowledged
        // rather than retried forever -- but it must not create anything.
        $body = $this->body('reference-that-does-not-exist', 'success');

        $this->postRaw('/webhooks/momo/mtn', $body, $this->sign($body))->assertOk();

        $this->assertDatabaseMissing('payments', ['transaction_id' => 'reference-that-does-not-exist']);
    }

    // --------------------------------------------------------------- Helpers

    private function body(string $reference, string $status): string
    {
        return json_encode(['reference_id' => $reference, 'status' => $status, 'message' => 'test callback']);
    }

    /**
     * @return array<string, string>
     */
    private function sign(string $body): array
    {
        return ['X-Momo-Signature' => hash_hmac('sha256', $body, self::SECRET)];
    }

    /**
     * @param  array<string, string>  $headers
     */
    private function postRaw(string $uri, string $body, array $headers = [])
    {
        $server = ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'];

        foreach ($headers as $name => $value) {
            $server['HTTP_'.strtoupper(str_replace('-', '_', $name))] = $value;
        }

        return $this->call('POST', $uri, [], [], [], $server, $body);
    }

    /**
     * @return array{0: Order, 1: Payment}
     */
    private function pendingMobileMoneyOrder(): array
    {
        $user = User::factory()->create();

        $order = Order::create([
            'order_number' => 'MOMO-'.uniqid(),
            'user_id' => $user->id,
            'subtotal' => 0, 'tax' => 0, 'shipping_cost' => 0, 'discount' => 0, 'total' => 0,
            'payment_status' => 'pending',
            'status' => 'pending',
            'payment_method' => 'mobile_money',
        ]);

        $payment = Payment::create([
            'order_id' => $order->id,
            'transaction_id' => 'momo-'.uniqid(),
            'payment_method' => 'mobile_money',
            'amount' => 50.00,
            'currency' => 'GHS',
            'status' => 'pending',
        ]);

        return [$order, $payment];
    }
}
