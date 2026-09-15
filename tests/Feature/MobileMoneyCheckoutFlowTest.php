<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Covers Roadmap 1.3: the mobile-money *checkout-initiation* path in
 * Api\CheckoutController -- selecting MTN or M-Pesa at checkout, the real
 * outbound HTTP call each provider requires (MTN's requesttopay, M-Pesa's
 * OAuth token exchange + STK push), and what happens when the gateway is
 * unconfigured or the provider rejects the request.
 *
 * This is a different surface from MobileMoneyWebhookTest, which covers the
 * provider calling *back* once a payment resolves. This file was cited in
 * docs/product/roadmap.md as already existing ("MobileMoneyCheckoutFlowTest")
 * before it had actually been written -- exactly the kind of gap
 * CONTRIBUTING.md's "never cite a file that does not exist" rule exists to
 * catch. It did not exist; this is that file.
 */
class MobileMoneyCheckoutFlowTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::set('momo_api_key', 'test-momo-key');
        Setting::set('momo_api_secret', 'test-momo-secret');
        Setting::set('momo_user_id', 'test-momo-user');
        Setting::set('momo_environment', 'sandbox');

        Setting::set('mpesa_consumer_key', 'test-consumer-key');
        Setting::set('mpesa_consumer_secret', 'test-consumer-secret');
        Setting::set('mpesa_shortcode', '174379');
        Setting::set('mpesa_passkey', 'test-passkey');
        Setting::set('mpesa_environment', 'sandbox');
    }

    public function test_mtn_payment_is_initiated_and_creates_a_pending_payment(): void
    {
        Http::fake([
            'sandbox.momodeveloper.mtn.com/*' => Http::response('', 202),
        ]);

        $response = $this->checkout([
            'payment_method' => 'mobile_money',
            'mobile_money_provider' => 'mtn',
            'phone_number' => '+233241234567',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.payment.method', 'mobile_money')
            ->assertJsonPath('data.payment.provider', 'mtn')
            ->assertJsonPath('data.payment.status', 'pending');

        $payment = Payment::where('payment_method', 'mobile_money')->latest('id')->first();
        $this->assertNotNull($payment);
        $this->assertSame('pending', $payment->status);
        $this->assertSame('EUR', $payment->currency);

        Http::assertSent(fn ($request) => str_contains($request->url(), 'collection/v1_0/requesttopay')
            && $request['payer']['partyId'] === '+233241234567');
    }

    public function test_mpesa_payment_is_initiated_using_the_stk_push_checkout_request_id(): void
    {
        Http::fake([
            'sandbox.safaricom.co.ke/oauth/v1/generate*' => Http::response([
                'access_token' => 'test-access-token',
                'expires_in' => '3599',
            ], 200),
            'sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest' => Http::response([
                'MerchantRequestID' => 'merchant-req-1',
                'CheckoutRequestID' => 'ws_CO_test_123456',
                'ResponseCode' => '0',
                'ResponseDescription' => 'Success. Request accepted for processing',
                'CustomerMessage' => 'Success. Request accepted for processing',
            ], 200),
        ]);

        $response = $this->checkout([
            'payment_method' => 'mobile_money',
            'mobile_money_provider' => 'mpesa',
            'phone_number' => '+254712345678',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.payment.provider', 'mpesa')
            ->assertJsonPath('data.payment.provider_reference', 'ws_CO_test_123456');

        $payment = Payment::where('payment_method', 'mobile_money')->latest('id')->first();
        $this->assertSame('ws_CO_test_123456', $payment->transaction_id);
        $this->assertSame('KES', $payment->currency);

        Http::assertSent(fn ($request) => str_contains($request->url(), 'oauth/v1/generate'));
        Http::assertSent(fn ($request) => str_contains($request->url(), 'mpesa/stkpush/v1/processrequest'));
    }

    public function test_mtn_checkout_fails_gracefully_when_the_gateway_is_not_configured(): void
    {
        Setting::set('momo_api_key', '');

        $response = $this->checkout([
            'payment_method' => 'mobile_money',
            'mobile_money_provider' => 'mtn',
            'phone_number' => '+233241234567',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'MTN Mobile Money gateway not configured');

        $this->assertDatabaseCount('payments', 0);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_mpesa_checkout_fails_gracefully_when_the_gateway_is_not_configured(): void
    {
        Setting::set('mpesa_consumer_key', '');

        $response = $this->checkout([
            'payment_method' => 'mobile_money',
            'mobile_money_provider' => 'mpesa',
            'phone_number' => '+254712345678',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'M-Pesa gateway not configured');

        $this->assertDatabaseCount('payments', 0);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_a_rejected_mtn_request_rolls_back_the_order_instead_of_leaving_it_pending(): void
    {
        Http::fake([
            'sandbox.momodeveloper.mtn.com/*' => Http::response(['message' => 'Invalid subscription key'], 401),
        ]);

        $response = $this->checkout([
            'payment_method' => 'mobile_money',
            'mobile_money_provider' => 'mtn',
            'phone_number' => '+233241234567',
        ]);

        $response->assertStatus(500)
            ->assertJsonPath('success', false);

        $this->assertDatabaseCount('payments', 0);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_a_rejected_mpesa_oauth_request_rolls_back_the_order(): void
    {
        Http::fake([
            'sandbox.safaricom.co.ke/oauth/v1/generate*' => Http::response(['error' => 'invalid_client'], 401),
        ]);

        $response = $this->checkout([
            'payment_method' => 'mobile_money',
            'mobile_money_provider' => 'mpesa',
            'phone_number' => '+254712345678',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Failed to obtain M-Pesa access token');

        $this->assertDatabaseCount('payments', 0);
        $this->assertDatabaseCount('orders', 0);
    }

    // --------------------------------------------------------------- Helpers

    private function checkout(array $paymentFields)
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $vendor = Vendor::create([
            'user_id' => User::factory()->create()->id,
            'business_name' => 'Test Vendor '.uniqid(),
            'slug' => 'test-vendor-'.uniqid(),
            'business_email' => 'vendor'.uniqid().'@example.com',
            'business_phone' => '+15550000000',
            'business_address' => '1 Test Street',
            'status' => 'approved',
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'Test Category '.uniqid(),
            'slug' => 'test-category-'.uniqid(),
        ]);

        $product = Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'name' => 'Test Product '.uniqid(),
            'slug' => 'test-product-'.uniqid(),
            'sku' => 'SKU-'.strtoupper(uniqid()),
            'description' => 'A product created for mobile money checkout tests.',
            'price' => 25.00,
            'stock_quantity' => 100,
            'stock_status' => 'in_stock',
            'is_active' => true,
        ]);

        $cart = Cart::create([
            'user_id' => $user->id,
            'subtotal' => 0,
            'tax' => 0,
            'shipping' => 0,
            'discount' => 0,
            'total' => 0,
        ]);

        $cart->items()->create([
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 25.00,
            'subtotal' => 25.00,
        ]);

        $cart->calculateTotals();

        $billing = Address::create([
            'user_id' => $user->id,
            'type' => 'billing',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'address_line1' => '123 Market St',
            'city' => 'Accra',
            'country' => 'GH',
            'is_default' => true,
        ]);

        $shipping = Address::create([
            'user_id' => $user->id,
            'type' => 'shipping',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'address_line1' => '123 Market St',
            'city' => 'Accra',
            'country' => 'GH',
            'is_default' => true,
        ]);

        return $this->postJson('/api/v1/checkout', array_merge([
            'billing_address_id' => $billing->id,
            'shipping_address_id' => $shipping->id,
        ], $paymentFields));
    }
}
