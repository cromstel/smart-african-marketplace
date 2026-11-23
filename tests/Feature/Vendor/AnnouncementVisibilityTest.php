<?php

namespace Tests\Feature\Vendor;

use App\Models\Announcement;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Vendor\Concerns\MakesIsolatedVendors;
use Tests\TestCase;

/**
 * Announcement::isPublished() returned false whenever published_at was
 * null, but Announcement::scopeActive() (used by the vendor announcements
 * index) treats a null published_at as "publish immediately" -- the admin
 * create form's own hint text says "Leave empty to publish immediately."
 * Since AnnouncementController::store() never defaults published_at to
 * now() when left empty, the overwhelming majority of announcements (any
 * created without explicitly scheduling a future publish date -- the
 * default path) end up with published_at = null.
 *
 * The result: Vendor\AnnouncementController::index() correctly lists these
 * announcements (via the active() scope), but ::show() gates on
 * isPublished() instead, so a vendor who sees the announcement in their
 * list and clicks into it gets a 404 -- for every immediately-published
 * announcement, which is the common case. Reproduced by creating an
 * announcement exactly as the admin "Create Announcement" form does when
 * "Publish Date" is left blank, then confirming live in the browser.
 */
class AnnouncementVisibilityTest extends TestCase
{
    use DatabaseTransactions;
    use MakesIsolatedVendors;

    public function test_a_vendor_can_open_an_immediately_published_announcement(): void
    {
        [$user] = $this->makeVendor();

        $announcement = Announcement::create([
            'title' => 'New feature rollout',
            'content' => 'Something vendors need to know.',
            'type' => 'info',
            'target' => 'all_vendors',
            'is_active' => true,
            'is_pinned' => false,
            'published_at' => null,
            'expires_at' => null,
        ]);

        // It must show up in the vendor's announcement list...
        $this->actingAs($user)
            ->get(route('vendor.announcements.index'))
            ->assertOk()
            ->assertSee('New feature rollout');

        // ...and clicking into it must not 404.
        $this->actingAs($user)
            ->get(route('vendor.announcements.show', $announcement))
            ->assertOk()
            ->assertSee('New feature rollout');
    }

    public function test_a_vendor_cannot_open_an_announcement_scheduled_for_the_future(): void
    {
        [$user] = $this->makeVendor();

        $announcement = Announcement::create([
            'title' => 'Not yet live',
            'content' => 'Scheduled for later.',
            'type' => 'info',
            'target' => 'all_vendors',
            'is_active' => true,
            'is_pinned' => false,
            'published_at' => now()->addDay(),
            'expires_at' => null,
        ]);

        $this->actingAs($user)
            ->get(route('vendor.announcements.show', $announcement))
            ->assertNotFound();
    }

    public function test_a_vendor_cannot_open_an_expired_announcement(): void
    {
        [$user] = $this->makeVendor();

        $announcement = Announcement::create([
            'title' => 'Old news',
            'content' => 'Already expired.',
            'type' => 'info',
            'target' => 'all_vendors',
            'is_active' => true,
            'is_pinned' => false,
            'published_at' => null,
            'expires_at' => now()->subDay(),
        ]);

        $this->actingAs($user)
            ->get(route('vendor.announcements.show', $announcement))
            ->assertNotFound();
    }
}
