<?php

namespace Tests\Feature;

use App\Models\Auction;
use App\Models\User;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * ProcessAuctions::settleEndedAuctions() checked `created_at` -- when the
 * auction listing was made -- instead of when it actually ended, to
 * implement its stated 5-minute settlement grace period. auctions has no
 * dedicated ended_at column, but AuctionService::end() only ever does
 * $auction->update(['status' => 'ended']), which bumps updated_at to
 * exactly that moment, so that's the real signal. The bug had it backwards
 * in both directions: a normal auction (created well over 5 minutes before
 * it ends) settled immediately with no grace period at all, while a
 * short-duration auction created and ended within the same 5-minute window
 * had settlement delayed for a reason unrelated to when it actually ended.
 */
class ProcessAuctionsSettlementTimingTest extends TestCase
{
    use DatabaseTransactions;

    public function test_an_auction_ended_less_than_five_minutes_ago_is_not_settled_yet(): void
    {
        $auction = $this->makeEndedAuction(
            createdAt: now()->subDays(10), // listed long ago
            endedAt: now()->subMinute(),   // but only just ended
        );

        $this->artisan('auction:process')->assertSuccessful();

        $this->assertSame('ended', $auction->fresh()->status);
    }

    public function test_an_auction_ended_more_than_five_minutes_ago_is_settled(): void
    {
        $auction = $this->makeEndedAuction(
            createdAt: now()->subDays(10),
            endedAt: now()->subMinutes(10),
        );

        $this->artisan('auction:process')->assertSuccessful();

        $this->assertContains($auction->fresh()->status, ['settled']);
    }

    public function test_an_auction_created_and_ended_within_the_same_window_still_waits_the_full_grace_period(): void
    {
        $auction = $this->makeEndedAuction(
            createdAt: now()->subMinute(),
            endedAt: now()->subMinute(),
        );

        $this->artisan('auction:process')->assertSuccessful();

        $this->assertSame('ended', $auction->fresh()->status);
    }

    private function makeEndedAuction(Carbon $createdAt, Carbon $endedAt): Auction
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

        $auction = Auction::create([
            'vendor_id' => $vendor->id,
            'title' => 'Auction '.uniqid(),
            'starting_price' => 100,
            'current_price' => 100,
            'bid_increment' => 10,
            'starts_at' => $createdAt,
            'ends_at' => $endedAt,
            'status' => 'ended',
            'type' => 'english',
        ]);

        // created_at/updated_at are auto-managed by Eloquent on create(), so
        // both land at "now" regardless of the starts_at/ends_at passed
        // above -- set them explicitly via the query builder to simulate
        // "listed long ago, ended at a specific moment" without Eloquent's
        // timestamp auto-touch overwriting updated_at again.
        DB::table('auctions')->where('id', $auction->id)->update([
            'created_at' => $createdAt,
            'updated_at' => $endedAt,
        ]);

        return $auction->fresh();
    }
}
