<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * resources/views/vendor/layout.blade.php had the exact same unclosed-<a>
 * bug that AdminLayoutMarkupTest guards against in the admin layout (see
 * that test and the "Fix admin layout blank-page bug on tablet/mobile
 * viewports" commit): the sidebar's home link wrapped the logo, brand text,
 * AND the #sidebar-collapse-toggle <button> but was never closed with </a>
 * -- it jumped straight from the button to a stray </div>. A <button>
 * nested inside an unclosed <a> is invalid HTML; browsers' parse-error
 * recovery throws off every tag that follows for the rest of the page,
 * which is what caused the admin layout's blank-page bug under the "lg"
 * breakpoint. The vendor layout was never fixed alongside it despite being
 * a near-duplicate of the same sidebar markup.
 *
 * Unlike the admin layout at the time of that fix, the vendor sidebar's
 * off-canvas classes already included `fixed` (via `inset-y-0 left-0`), so
 * only the tag-nesting half of the bug applied here.
 */
class VendorLayoutMarkupTest extends TestCase
{
    use DatabaseTransactions;

    public function test_vendor_sidebar_home_link_does_not_nest_the_collapse_toggle_button(): void
    {
        [$user] = $this->makeVendor();

        $response = $this->actingAs($user)->get(route('vendor.dashboard'));
        $response->assertOk();

        $html = $response->getContent();

        preg_match('/<a\s[^>]*aria-label="[^"]*Go to homepage"[^>]*>(.*?)<\/a>/s', $html, $matches);

        $this->assertNotEmpty($matches, 'Could not locate the sidebar home link in the rendered vendor page.');
        $this->assertStringNotContainsString(
            'sidebar-collapse-toggle',
            $matches[1],
            'The sidebar collapse-toggle button is nested inside the home <a> link -- the </a> is missing again.'
        );
    }

    public function test_vendor_sidebar_is_positioned_fixed_for_off_canvas_mobile_display(): void
    {
        [$user] = $this->makeVendor();

        $response = $this->actingAs($user)->get(route('vendor.dashboard'));
        $response->assertOk();

        $html = $response->getContent();

        preg_match('/<aside id="sidebar" class="([^"]*)"/', $html, $matches);

        $this->assertNotEmpty($matches, 'Could not locate the #sidebar aside element.');
        $this->assertStringContainsString(
            'fixed',
            $matches[1],
            'The sidebar must be position:fixed below the "lg" breakpoint so its off-canvas -translate-x-full state removes it from document flow -- otherwise it still reserves its full height and pushes all page content off-screen on tablet/mobile widths.'
        );
    }

    /**
     * @return array{0: User, 1: Vendor}
     */
    private function makeVendor(): array
    {
        $user = User::factory()->vendor()->create();

        $vendor = Vendor::create([
            'user_id' => $user->id,
            'business_name' => 'Vendor '.uniqid(),
            'slug' => 'vendor-'.uniqid(),
            'business_email' => 'vendor'.uniqid().'@example.com',
            'business_phone' => '+15550000000',
            'business_address' => '1 Test Street',
            'status' => 'approved',
            'is_active' => true,
        ]);

        return [$user->fresh(), $vendor];
    }
}
