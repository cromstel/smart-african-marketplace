<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Phase 4 (roadmap): "Audit N+1 queries across the 49 admin and 32 vendor
 * controllers -- the most likely source of slow pages at this model count."
 *
 * Model::preventLazyLoading() is now enabled outside production
 * (AppServiceProvider::boot()), which turns any lazy-loaded relation into a
 * LazyLoadingViolationException instead of a silent extra query. The
 * existing 300+ test suite already exercises most of the app under that
 * setting and found one real violation (fixed separately). This test hits
 * every admin index page directly to extend that same coverage to
 * controllers with no dedicated feature test -- most of these have never
 * been exercised by any test before this one.
 */
class AdminIndexPagesSmokeTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @dataProvider indexRoutes
     */
    public function test_admin_index_page_loads_without_a_server_error(string $routeName): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route($routeName));

        $response->assertStatus(
            $this->expectedStatusFor($routeName),
        );
    }

    public static function indexRoutes(): array
    {
        return collect([
            'admin.about.index',
            'admin.activity-logs.index',
            'admin.ai-settings.index',
            'admin.announcements.index',
            'admin.auctions.index',
            'admin.blog.categories.index',
            'admin.blog.index',
            'admin.blog.tags.index',
            'admin.booking-types.index',
            'admin.bookings.index',
            'admin.campaigns.index',
            'admin.categories.index',
            'admin.chatbot.sessions.index',
            'admin.chatbot.settings.index',
            'admin.coupons.index',
            'admin.data-exports.index',
            'admin.exports.index',
            'admin.hero-sections.index',
            'admin.imports.index',
            'admin.loyalty.index',
            'admin.maintenance.index',
            'admin.media.index',
            'admin.orders.index',
            'admin.pages.index',
            'admin.payouts.index',
            'admin.products.attributes.index',
            'admin.products.brands.index',
            'admin.products.index',
            'admin.products.tags.index',
            'admin.referrals.index',
            'admin.refunds.index',
            'admin.reviews.index',
            'admin.salon-categories.index',
            'admin.salon-services.index',
            'admin.salon-stylists.index',
            'admin.salon.index',
            'admin.settings.index',
            'admin.shipping-settings.index',
            'admin.subscriptions.plans.index',
            'admin.support-tickets.index',
            'admin.system-health.index',
            'admin.users.index',
            'admin.vendor-verifications.index',
            'admin.vendors.index',
            'admin.widgets.index',
            'admin.withdrawals.index',
        ])->mapWithKeys(fn ($name) => [$name => [$name]])->all();
    }

    private function expectedStatusFor(string $routeName): int
    {
        // These two are deliberate redirects to their real destination
        // (admin.products.export / admin.products.import), not index pages
        // of their own. Everything else must render 200.
        return match ($routeName) {
            'admin.exports.index', 'admin.imports.index' => 302,
            default => 200,
        };
    }
}
