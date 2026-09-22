<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SeoTest extends TestCase
{
    // See AuthenticationTest for why DatabaseTransactions rather than
    // RefreshDatabase is used project-wide.
    use DatabaseTransactions;

    public function test_robots_txt_advertises_a_real_sitemap_url(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');

        // Regression: this used to be a static public/robots.txt file
        // containing the literal, unprocessed text "{{ url('/sitemap.xml') }}"
        // -- Blade syntax served verbatim to every crawler because static
        // files in public/ never reach Laravel's router.
        $response->assertDontSee('{{');
        $response->assertSee('Sitemap: '.route('sitemap.index'));
        $response->assertSee('Disallow: /admin/');
    }

    public function test_sitemap_index_lists_every_sub_sitemap(): void
    {
        $response = $this->get(route('sitemap.index'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/xml');

        foreach (['static', 'products', 'salon', 'blog', 'auctions', 'vendors'] as $section) {
            $response->assertSee(route("sitemap.{$section}"), false);
        }
    }

    public function test_product_page_emits_valid_structured_data_for_a_multiline_description(): void
    {
        $unique = uniqid('seo_test_');

        $category = Category::create([
            'name' => 'Test Category '.$unique,
            'slug' => 'test-category-'.$unique,
            'type' => 'food',
        ]);

        $vendorUser = User::factory()->create([
            'email' => $unique.'@example.com',
        ]);

        $vendor = Vendor::create([
            'user_id' => $vendorUser->id,
            'business_name' => 'Test Vendor '.$unique,
            'slug' => 'test-vendor-'.$unique,
            'business_email' => $unique.'-vendor@example.com',
            'business_phone' => '+10000000000',
            'business_address' => '1 Test Street',
            'status' => 'approved',
            'is_active' => true,
        ]);

        $product = Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'name' => 'Test Product '.$unique,
            'slug' => 'test-product-'.$unique,
            'sku' => 'SKU-'.$unique,
            // Regression: {{ }}-interpolated JSON-LD does not escape JSON
            // control characters, so a plain newline in a textarea-sourced
            // field previously produced a <script type="application/ld+json">
            // block that failed to parse.
            'description' => "Line one.\nLine two with a \"quote\" and a backslash \\ and </script>.",
            'price' => 19.99,
            'stock_quantity' => 5,
            'stock_status' => 'in_stock',
            'is_active' => true,
        ]);

        $response = $this->get(route('shop.show', $product->slug));
        $response->assertStatus(200);

        // Regression: ShopController::show() sets per-page title/description
        // via SeoService, but layouts.shop's <x-seo> only ever read the
        // (never-passed) $seoTitle/$seoDescription view variables, so every
        // page silently fell back to the generic site-wide defaults.
        $response->assertSee('<title>'.$product->name.' - '.config('app.name').'</title>', false);
        $response->assertSee('name="description" content="Line one.', false);

        preg_match_all(
            '#<script type="application/ld\+json">(.*?)</script>#s',
            $response->getContent(),
            $matches
        );

        $this->assertNotEmpty($matches[1], 'Expected at least one JSON-LD block on the product page.');

        $types = [];
        foreach ($matches[1] as $json) {
            $decoded = json_decode($json, true);
            $this->assertNotNull($decoded, 'JSON-LD block failed to parse: '.json_last_error_msg());
            $types[] = $decoded['@type'] ?? null;
        }

        $this->assertContains('Product', $types);
        $this->assertContains('BreadcrumbList', $types);
    }

    public function test_vendor_storefront_renders_its_own_title_and_organization_schema(): void
    {
        $unique = uniqid('seo_test_');

        $vendorUser = User::factory()->create([
            'email' => $unique.'@example.com',
        ]);

        $vendor = Vendor::create([
            'user_id' => $vendorUser->id,
            'business_name' => 'Storefront Vendor '.$unique,
            'slug' => 'storefront-vendor-'.$unique,
            'business_email' => $unique.'-vendor@example.com',
            'business_phone' => '+10000000000',
            'business_address' => '1 Test Street',
            'status' => 'approved',
            'is_active' => true,
        ]);

        $response = $this->get(route('vendor.store', $vendor->slug));
        $response->assertStatus(200);

        // Regression: <x-seo /> previously rendered with no props at all
        // (a second, empty <title> next to a hardcoded one), so the
        // vendor-specific title/description/Organization schema the
        // controller already built were silently dropped.
        $response->assertSee('<title>'.$vendor->business_name.' - Vendor Store - '.config('app.name').'</title>', false);

        preg_match_all(
            '#<script type="application/ld\+json">(.*?)</script>#s',
            $response->getContent(),
            $matches
        );

        $types = [];
        foreach ($matches[1] as $json) {
            $decoded = json_decode($json, true);
            $this->assertNotNull($decoded, 'JSON-LD block failed to parse: '.json_last_error_msg());
            $types[] = $decoded['@type'] ?? null;
        }

        $this->assertContains('Organization', $types);
    }
}
