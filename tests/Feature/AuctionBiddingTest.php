<?php

namespace Tests\Feature;

use App\Models\Auction;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AuctionBiddingTest extends TestCase
{
    use DatabaseTransactions;

    public function test_a_guest_cannot_place_a_bid(): void
    {
        $auction = $this->makeActiveAuction();

        $this->post(route('auctions.bid', $auction->slug), ['amount' => 110])
            ->assertRedirect(route('login'));

        $this->assertSame(0, $auction->fresh()->total_bids);
    }

    public function test_a_vendor_cannot_bid_on_their_own_auction(): void
    {
        [$user, $vendor] = $this->makeVendor();
        $auction = $this->makeActiveAuction($vendor);

        $this->actingAs($user)
            ->postJson(route('auctions.bid', $auction->slug), ['amount' => 110])
            ->assertForbidden();

        $this->assertSame(0, $auction->fresh()->total_bids);
    }

    public function test_a_customer_can_place_a_valid_bid(): void
    {
        $auction = $this->makeActiveAuction();
        $customer = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($customer)
            ->postJson(route('auctions.bid', $auction->slug), ['amount' => 110]);

        $response->assertCreated();
        $this->assertSame('110.00', $auction->fresh()->current_price);
        $this->assertDatabaseHas('auction_bids', [
            'auction_id' => $auction->id,
            'user_id' => $customer->id,
            'is_winning' => 1,
        ]);
    }

    public function test_a_bid_below_the_minimum_is_rejected_with_a_client_error(): void
    {
        $auction = $this->makeActiveAuction();
        $customer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($customer)
            ->postJson(route('auctions.bid', $auction->slug), ['amount' => 50])
            ->assertStatus(422);

        $this->assertSame(0, $auction->fresh()->total_bids);
    }

    // --------------------------------------------------------------- Helpers

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

    private function makeActiveAuction(?Vendor $vendor = null): Auction
    {
        if (! $vendor) {
            [, $vendor] = $this->makeVendor();
        }

        return Auction::create([
            'vendor_id' => $vendor->id,
            'title' => 'Auction '.uniqid(),
            'starting_price' => 100,
            'current_price' => 100,
            'bid_increment' => 10,
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addDays(3),
            'status' => 'active',
            'type' => 'english',
        ]);
    }
}
