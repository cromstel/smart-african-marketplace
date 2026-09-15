<?php

namespace Tests\Feature\Api;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * /api/v1 controllers paginate their list queries, but several of them
 * discarded the pagination metadata (current_page, total, next page url)
 * because the paginator was nested inside the response's 'data' key
 * instead of returned in a way Laravel's own pagination wrapping
 * recognizes. Separately, an exception thrown before a controller ever
 * runs (failed validation, a 404 route-model-binding, an expired token)
 * skipped the app's {success, ...} envelope entirely. These tests lock in
 * both fixes.
 */
class ApiPaginationAndErrorEnvelopeTest extends TestCase
{
    use DatabaseTransactions;

    private function makeCategoryAndProducts(int $count): Category
    {
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

        for ($i = 0; $i < $count; $i++) {
            Product::create([
                'vendor_id' => $vendor->id,
                'category_id' => $category->id,
                'name' => 'Product '.$i.' '.uniqid(),
                'slug' => 'product-'.$i.'-'.uniqid(),
                'sku' => 'SKU-'.strtoupper(uniqid()),
                'description' => 'A product for pagination tests.',
                'price' => 10.00,
                'stock_quantity' => 5,
                'stock_status' => 'in_stock',
                'is_active' => true,
            ]);
        }

        return $category;
    }

    public function test_product_listing_includes_pagination_meta(): void
    {
        $this->makeCategoryAndProducts(3);

        $response = $this->getJson('/api/v1/products?per_page=2');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success', 'data', 'message',
                'meta' => ['current_page', 'last_page', 'per_page', 'total', 'from', 'to'],
                'links' => ['first', 'last', 'prev', 'next'],
            ])
            ->assertJsonPath('meta.per_page', 2)
            ->assertJsonPath('meta.total', 3)
            ->assertJsonPath('meta.last_page', 2)
            ->assertJsonCount(2, 'data');

        $this->assertNotNull($response->json('links.next'));
    }

    private function makeOrderFor(User $customer): Order
    {
        return Order::create([
            'order_number' => 'ORD-'.uniqid(),
            'user_id' => $customer->id,
            'subtotal' => 40, 'tax' => 0, 'shipping_cost' => 0, 'discount' => 0, 'total' => 40,
            'payment_status' => 'paid',
            'status' => 'delivered',
            'payment_method' => 'card',
        ]);
    }

    public function test_order_listing_includes_pagination_meta(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->makeOrderFor($user);
        $this->makeOrderFor($user);

        $this->getJson('/api/v1/orders')
            ->assertStatus(200)
            ->assertJsonStructure([
                'success', 'data', 'message',
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ])
            ->assertJsonPath('meta.total', 2);
    }

    public function test_brand_listing_includes_pagination_meta(): void
    {
        Brand::create(['name' => 'Brand A '.uniqid(), 'slug' => 'brand-a-'.uniqid(), 'is_active' => true]);
        Brand::create(['name' => 'Brand B '.uniqid(), 'slug' => 'brand-b-'.uniqid(), 'is_active' => true]);

        $this->getJson('/api/v1/brands?per_page=1')
            ->assertStatus(200)
            ->assertJsonStructure(['success', 'data', 'message', 'meta', 'links'])
            ->assertJsonPath('meta.total', 2)
            ->assertJsonCount(1, 'data');
    }

    public function test_salon_listing_uses_the_standard_envelope(): void
    {
        $this->getJson('/api/v1/salon')
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['success', 'data', 'message', 'meta', 'links']);
    }

    public function test_a_404_from_route_model_binding_gets_the_success_flag(): void
    {
        $response = $this->getJson('/api/v1/products/999999999')
            ->assertStatus(404)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Resource not found.');

        // Regardless of APP_DEBUG, an API response must never leak the
        // Eloquent model class/id, the exception class, absolute server
        // file paths, or a stack trace.
        $body = $response->getContent();
        $this->assertStringNotContainsString('App\\Models\\Product', $body);
        $this->assertArrayNotHasKey('exception', $response->json());
        $this->assertArrayNotHasKey('trace', $response->json());
        $this->assertArrayNotHasKey('file', $response->json());
        $this->assertArrayNotHasKey('line', $response->json());
    }

    public function test_an_unauthenticated_api_request_gets_the_success_flag(): void
    {
        $this->getJson('/api/v1/orders')
            ->assertStatus(401)
            ->assertJsonPath('success', false);
    }

    public function test_a_validation_exception_thrown_before_a_controller_runs_gets_the_success_flag(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // AddressController::store() throws ValidationException via
        // $request->validate() -- Laravel's default handler renders that
        // directly, bypassing any envelope the controller itself builds.
        $this->postJson('/api/v1/addresses', ['type' => 'shipping'])
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors(['first_name', 'last_name']);
    }

    public function test_a_manually_returned_error_response_is_not_double_wrapped(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        Sanctum::actingAs($other);

        $order = $this->makeOrderFor($user);

        // OrderController::show() returns its own errorResponse() for this
        // case rather than throwing -- confirms the exception-handler fix
        // doesn't interfere with (or duplicate) responses controllers
        // already shape themselves.
        $response = $this->getJson("/api/v1/orders/{$order->id}")
            ->assertStatus(403)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Unauthorized');

        $this->assertSame(['success', 'message'], array_keys($response->json()));
    }
}
