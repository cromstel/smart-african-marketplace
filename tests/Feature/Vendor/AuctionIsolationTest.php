<?php

namespace Tests\Feature\Vendor;

use App\Models\Auction;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Vendor\Concerns\MakesIsolatedVendors;
use Tests\TestCase;

/**
 * Cross-tenant isolation for vendor auction management. Unlike several other
 * controllers in this family (see OrderIsolationTest, VendorIsolationTest,
 * WithdrawalTest), Vendor\AuctionController and AuctionPolicy already
 * compare auction->vendor_id against $user->vendor->id (the correct id
 * space) rather than auth()->id() -- these tests are a regression guard for
 * that, not a bug report.
 *
 * The whole vendor auctions route group sits behind
 * `module.access:auctions`, so both vendors need it entitled or every
 * request here would redirect to the upgrade page instead of reaching the
 * policy -- MakesIsolatedVendors::makeVendor() sets that up.
 */
class AuctionIsolationTest extends TestCase
{
    use DatabaseTransactions;
    use MakesIsolatedVendors;

    public function test_a_vendor_cannot_view_another_vendors_auction_management_page(): void
    {
        [$mine] = $this->makeVendor(['auctions']);
        [, , $theirAuction] = $this->makeVendorWithAuction();

        $this->actingAs($mine)
            ->get(route('vendor.auctions.show', $theirAuction))
            ->assertForbidden();
    }

    public function test_a_vendor_cannot_update_another_vendors_auction(): void
    {
        [$mine] = $this->makeVendor(['auctions']);
        [, , $theirAuction] = $this->makeVendorWithAuction();
        $originalTitle = $theirAuction->title;

        $this->actingAs($mine)
            ->put(route('vendor.auctions.update', $theirAuction), ['title' => 'Hijacked'])
            ->assertForbidden();

        $this->assertSame($originalTitle, $theirAuction->fresh()->title);
    }

    public function test_a_vendor_cannot_cancel_another_vendors_auction(): void
    {
        [$mine] = $this->makeVendor(['auctions']);
        [, , $theirAuction] = $this->makeVendorWithAuction();

        $this->actingAs($mine)
            ->post(route('vendor.auctions.cancel', $theirAuction))
            ->assertForbidden();

        $this->assertSame('active', $theirAuction->fresh()->status);
    }

    public function test_a_vendor_cannot_view_another_vendors_auction_bids(): void
    {
        [$mine] = $this->makeVendor(['auctions']);
        [, , $theirAuction] = $this->makeVendorWithAuction();

        $this->actingAs($mine)
            ->get(route('vendor.auctions.bids', $theirAuction))
            ->assertForbidden();
    }

    public function test_a_vendor_cannot_delete_another_vendors_auction(): void
    {
        [$mine] = $this->makeVendor(['auctions']);
        [, , $theirAuction] = $this->makeVendorWithAuction(status: 'draft');

        $this->actingAs($mine)
            ->delete(route('vendor.auctions.destroy', $theirAuction))
            ->assertForbidden();

        $this->assertDatabaseHas('auctions', ['id' => $theirAuction->id]);
    }

    public function test_a_vendor_can_still_manage_their_own_auction(): void
    {
        // Guards against "fixing" isolation by refusing everything.
        [$mine, , $myAuction] = $this->makeVendorWithAuction();

        $this->actingAs($mine)
            ->get(route('vendor.auctions.show', $myAuction))
            ->assertOk();
    }

    // --------------------------------------------------------------- Helpers

    /**
     * @return array{0: User, 1: Vendor, 2: Auction}
     */
    private function makeVendorWithAuction(string $status = 'active'): array
    {
        [$user, $vendor] = $this->makeVendor(['auctions']);

        $auction = Auction::create([
            'vendor_id' => $vendor->id,
            'title' => 'Auction '.uniqid(),
            'starting_price' => 100,
            'current_price' => 100,
            'bid_increment' => 10,
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addDays(3),
            'status' => $status,
            'type' => 'english',
        ]);

        return [$user, $vendor, $auction];
    }
}
