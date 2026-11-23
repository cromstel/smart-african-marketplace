<?php

namespace Tests\Unit\Services\Auction;

use App\Models\Auction;
use App\Models\User;
use App\Models\Vendor;
use App\Services\Auction\BiddingService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * BiddingService::placeBid() used to validate a bid against whatever Auction
 * instance the caller (typically route-model binding) had already loaded,
 * and only locked the row for the write itself. Two callers who each loaded
 * the auction before either one bid could therefore both validate against
 * the same stale current_price; whichever transaction committed second would
 * silently overwrite current_price with its own (possibly lower) amount and
 * flip the winning flag onto its own lower bid, erasing a legitimately
 * higher one. test_a_stale_read_bid_can_no_longer_overwrite_a_higher_bid()
 * reproduces that interleaving directly -- the same "prove it, then fix it"
 * approach used elsewhere in this suite -- against the fixed placeBid(),
 * which now locks and re-validates against the current row.
 */
class BiddingServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function test_a_bid_below_the_minimum_is_rejected(): void
    {
        $auction = $this->makeActiveAuction();
        $bidder = User::factory()->create(['role' => 'customer']);

        $this->expectException(\InvalidArgumentException::class);

        app(BiddingService::class)->placeBid($auction, $bidder, 50.00);
    }

    public function test_a_bid_on_an_inactive_auction_is_rejected(): void
    {
        $vendor = $this->makeVendor();
        $auction = Auction::create([
            'vendor_id' => $vendor->id,
            'title' => 'Draft Auction '.uniqid(),
            'starting_price' => 100,
            'current_price' => 100,
            'bid_increment' => 10,
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDays(3),
            'status' => 'draft',
        ]);
        $bidder = User::factory()->create(['role' => 'customer']);

        $this->expectException(\InvalidArgumentException::class);

        app(BiddingService::class)->placeBid($auction, $bidder, 100.00);
    }

    public function test_placing_a_bid_marks_the_previous_winning_bid_as_no_longer_winning(): void
    {
        $auction = $this->makeActiveAuction();
        $first = User::factory()->create(['role' => 'customer']);
        $second = User::factory()->create(['role' => 'customer']);
        $service = app(BiddingService::class);

        $firstBid = $service->placeBid($auction, $first, 110.00);
        $service->placeBid($auction->fresh(), $second, 130.00);

        $this->assertFalse($firstBid->fresh()->is_winning);
        $this->assertSame('130.00', $auction->fresh()->current_price);
        $this->assertSame(2, $auction->fresh()->total_bids);
    }

    public function test_a_bid_placed_near_the_end_extends_the_auction(): void
    {
        $auction = $this->makeActiveAuction(endsAt: now()->addMinutes(5));
        $auction->extension_minutes = 15;
        $auction->save();
        $bidder = User::factory()->create(['role' => 'customer']);
        $originalEndsAt = $auction->ends_at;

        app(BiddingService::class)->placeBid($auction, $bidder, 110.00);

        $this->assertTrue($auction->fresh()->ends_at->isAfter($originalEndsAt));
    }

    public function test_a_stale_read_bid_can_no_longer_overwrite_a_higher_bid(): void
    {
        $auction = $this->makeActiveAuction();

        // Two independently-loaded copies, as two concurrent HTTP requests
        // would each get from their own route-model binding.
        $forFirstRequest = Auction::find($auction->id);
        $forSecondRequest = Auction::find($auction->id);

        $first = User::factory()->create(['role' => 'customer']);
        $second = User::factory()->create(['role' => 'customer']);
        $service = app(BiddingService::class);

        $winningBid = $service->placeBid($forFirstRequest, $first, 150.00);

        // The second request's snapshot still shows current_price=100, so a
        // bid of 110 looks valid against its stale view even though the
        // true current price already moved to 150. It must now be rejected
        // rather than silently becoming the new "winning" bid at a lower
        // price than $150.
        try {
            $service->placeBid($forSecondRequest, $second, 110.00);
            $this->fail('A stale-read bid of 110 was accepted even though the true current price is already 150.');
        } catch (\InvalidArgumentException $e) {
            // Expected.
        }

        $auction->refresh();
        $this->assertSame('150.00', $auction->current_price, 'The true current price must never be overwritten by a stale-read bid.');
        $this->assertTrue($winningBid->fresh()->is_winning, 'The higher bid must remain the winning bid.');
        $this->assertSame(1, $auction->total_bids);
    }

    // --------------------------------------------------------------- Helpers

    private function makeVendor(): Vendor
    {
        $user = User::factory()->vendor()->create();

        return Vendor::create([
            'user_id' => $user->id,
            'business_name' => 'Vendor '.uniqid(),
            'slug' => 'vendor-'.uniqid(),
            'business_email' => 'vendor'.uniqid().'@example.com',
            'business_phone' => '+15550000000',
            'business_address' => '1 Test Street',
            'status' => 'approved',
            'is_active' => true,
        ]);
    }

    private function makeActiveAuction(float $startingPrice = 100, float $increment = 10, $endsAt = null): Auction
    {
        $vendor = $this->makeVendor();

        return Auction::create([
            'vendor_id' => $vendor->id,
            'title' => 'Auction '.uniqid(),
            'starting_price' => $startingPrice,
            'current_price' => $startingPrice,
            'bid_increment' => $increment,
            'starts_at' => now()->subHour(),
            'ends_at' => $endsAt ?? now()->addDays(3),
            'status' => 'active',
            'type' => 'english',
        ]);
    }
}
