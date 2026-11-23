<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Completes the Phase 4 N+1 audit's route-shape coverage (see
 * AdminIndexPagesSmokeTest for index, AdminContentShowEditPagesSmokeTest and
 * siblings for show/edit) by adding the last uncovered shape: create pages.
 * Lower N+1 risk than show/edit -- a create form has no existing record to
 * over-fetch relations on -- but not zero: several of these populate a
 * dropdown from a query (categories, vendors, salons) that could itself
 * regress into a loop, and this is still the same proven pattern that found
 * five real bugs (missing views, a dead route, a bad eager-load) when
 * applied to show/edit. `admin.products.variants.create` is the only route
 * here needing a parent record ({product}).
 *
 * Found one more here: `Route::resource('auctions', ...)` auto-registers
 * create/store, but AuctionController never implemented either -- hitting
 * admin.auctions.create threw a fatal BadMethodCallException instead of a
 * response. Fixed by adding create()/store() that redirect with an
 * explanation, matching admin.orders.create's existing pattern for the same
 * situation (a resource whose creation is deliberately not an admin
 * action -- auctions come from vendors, orders from checkout). Both are
 * asserted as 302 below, not bugs.
 */
class AdminCreatePagesSmokeTest extends TestCase
{
    use DatabaseTransactions;

    #[DataProvider('createRoutes')]
    public function test_admin_create_page_loads_without_a_server_error(string $routeName): void
    {
        $admin = User::factory()->admin()->create();
        $routeParams = $this->routeParamsFor($routeName);

        $response = $this->actingAs($admin)->get(route($routeName, $routeParams));

        $response->assertStatus($this->expectedStatusFor($routeName));
    }

    public static function createRoutes(): array
    {
        return collect([
            'admin.about.create',
            'admin.announcements.create',
            'admin.auctions.create',
            'admin.blog.categories.create',
            'admin.blog.tags.create',
            'admin.blog.create',
            'admin.booking-types.create',
            'admin.campaigns.create',
            'admin.categories.create',
            'admin.coupons.create',
            'admin.hero-sections.create',
            'admin.media.create',
            'admin.orders.create',
            'admin.pages.create',
            'admin.products.attributes.create',
            'admin.products.brands.create',
            'admin.products.create',
            'admin.products.tags.create',
            'admin.products.variations.create',
            'admin.products.variants.create',
            'admin.salon-categories.create',
            'admin.salon-services.create',
            'admin.salon-stylists.create',
            'admin.salon.create',
            'admin.subscriptions.plans.create',
            'admin.users.create',
            'admin.vendors.create',
            'admin.widgets.create',
        ])->mapWithKeys(fn ($name) => [$name => [$name]])->all();
    }

    private function routeParamsFor(string $routeName): array
    {
        return match ($routeName) {
            'admin.products.variants.create' => ['product' => $this->makeProduct()->id],
            default => [],
        };
    }

    private function expectedStatusFor(string $routeName): int
    {
        // Deliberate redirects, not create pages of their own:
        // - admin.orders.create: orders are only created via customer/vendor
        //   checkout, never manually by admin (see OrderController::create()).
        // - admin.auctions.create: auctions are only created by vendors from
        //   their own dashboard (see AuctionController::create()).
        return match ($routeName) {
            'admin.orders.create', 'admin.auctions.create' => 302,
            default => 200,
        };
    }

    private function makeProduct(): Product
    {
        $vendorUser = User::factory()->vendor()->create();
        $vendor = Vendor::create([
            'user_id' => $vendorUser->id,
            'business_name' => 'Vendor '.uniqid(),
            'slug' => 'vendor-'.uniqid(),
            'business_email' => 'vendor'.uniqid().'@example.com',
            'business_phone' => '+15550000000',
            'business_address' => '1 Test Street',
            'status' => 'approved',
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'Category '.uniqid(),
            'slug' => 'category-'.uniqid(),
            'is_active' => true,
        ]);

        return Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'name' => 'Product '.uniqid(),
            'slug' => 'product-'.uniqid(),
            'sku' => 'SKU-'.strtoupper(uniqid()),
            'description' => 'Fixture product.',
            'price' => 20.00,
            'stock_quantity' => 5,
            'stock_status' => 'in_stock',
            'is_active' => true,
        ]);
    }
}
