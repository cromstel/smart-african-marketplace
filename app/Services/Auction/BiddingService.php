<?php

namespace App\Services\Auction;

use App\Models\Auction;
use App\Models\AuctionAutoBid;
use App\Models\AuctionBid;
use App\Models\AuctionNotification;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class BiddingService
{
    /**
     * Place a bid on an auction.
     */
    public function placeBid(Auction $auction, User $user, float $amount): AuctionBid
    {
        return DB::transaction(function () use ($auction, $user, $amount) {
            // Re-fetch and lock the auction row inside the transaction.
            // $auction, as handed to us, was loaded by the caller (typically
            // route-model binding) before this method ran and before any
            // lock was taken. Validating against that snapshot let two
            // near-simultaneous bids both pass validation against the same
            // stale current_price: whichever request's transaction
            // committed second would then overwrite current_price with its
            // own (possibly lower) amount and flip the winning flag onto
            // its own lower bid -- silently erasing a legitimately higher
            // bid. Locking and re-validating against the current row
            // closes that window; the second bidder now waits for the
            // first bid's transaction to commit and is correctly rejected
            // if their amount no longer clears the new minimum.
            $locked = Auction::whereKey($auction->id)->lockForUpdate()->firstOrFail();

            $this->validateBid($locked, $amount);

            // Get previous winning bid (if any)
            $previousWinningBid = $locked->bids()->winning()->first();

            // Create the new bid
            $bid = AuctionBid::create([
                'auction_id' => $locked->id,
                'user_id' => $user->id,
                'amount' => $amount,
                'is_winning' => true,
                'is_auto_bid' => false,
            ]);

            // Mark previous winning bid as not winning
            if ($previousWinningBid) {
                $previousWinningBid->update(['is_winning' => false]);

                // Notify previous bidder they've been outbid
                $this->notifyOutbid($locked, $previousWinningBid->user, $amount);
            }

            // Update auction current price and bid count
            // Using direct assignment since amount is validated against the
            // locked row above.
            $locked->current_price = max($locked->current_price, $amount);
            $locked->total_bids += 1;
            $locked->save();

            // Check for anti-sniping extension
            $this->checkAndExtend($locked);

            // Process auto-bids (pass the user who just bid to exclude them)
            $this->processAutoBids($locked, $amount, $user);

            // Notify seller of new bid
            $this->notifySellerOfNewBid($locked, $user, $amount);

            // Keep the caller's instance (e.g. the controller's route-bound
            // model) in sync with what was actually persisted.
            $auction->setRawAttributes($locked->getAttributes());

            return $bid;
        });
    }

    /**
     * Validate a bid amount.
     */
    public function validateBid(Auction $auction, float $amount): bool
    {
        if (! $auction->isActive()) {
            throw new \InvalidArgumentException('Auction is not active.');
        }

        if ($auction->hasEnded()) {
            throw new \InvalidArgumentException('Auction has ended.');
        }

        $minimumBid = $auction->getMinimumBid();

        if ($amount < $minimumBid) {
            throw new \InvalidArgumentException(
                "Bid must be at least {$minimumBid}"
            );
        }

        return true;
    }

    /**
     * Get bid history for an auction.
     */
    public function getBidHistory(Auction $auction): Collection
    {
        return $auction->bids()
            ->with('user:id,name,email')
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Get user's bids.
     */
    public function getUserBids(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return AuctionBid::where('user_id', $user->id)
            ->with(['auction.vendor'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * Get user's winning bids.
     */
    public function getUserWinningBids(User $user): Collection
    {
        return AuctionBid::where('user_id', $user->id)
            ->where('is_winning', true)
            ->whereHas('auction', function ($query) {
                $query->active();
            })
            ->with('auction.vendor')
            ->get();
    }

    /**
     * Set up auto-bid for a user on an auction.
     */
    public function setAutoBid(User $user, Auction $auction, float $maxAmount): AuctionAutoBid
    {
        // Check if auto-bid already exists
        $autoBid = AuctionAutoBid::firstOrNew([
            'auction_id' => $auction->id,
            'user_id' => $user->id,
        ]);

        $autoBid->max_amount = $maxAmount;
        $autoBid->is_active = true;
        $autoBid->save();

        return $autoBid;
    }

    /**
     * Cancel auto-bid.
     */
    public function cancelAutoBid(AuctionAutoBid $autoBid): void
    {
        $autoBid->deactivate();
    }

    /**
     * Get user's auto-bids.
     */
    public function getUserAutoBids(User $user): Collection
    {
        return AuctionAutoBid::where('user_id', $user->id)
            ->where('is_active', true)
            ->with('auction')
            ->get();
    }

    /**
     * Process auto-bids for an auction.
     * Handles chained auto-bids to ensure the highest auto-bidder wins.
     */
    public function processAutoBids(Auction $auction, float $currentBidAmount, User $currentBidder): void
    {
        // Maximum number of auto-bid iterations to prevent infinite loops
        $maxIterations = 5;
        $iteration = 0;

        // Calculate minimum bid once before the loop
        $minimumBid = $auction->getMinimumBid();

        while ($iteration < $maxIterations) {
            $iteration++;

            // Get current auto-bids, excluding the current bidder
            $autoBids = AuctionAutoBid::where('auction_id', $auction->id)
                ->where('user_id', '!=', $currentBidder->id)
                ->where('is_active', true)
                ->where('max_amount', '>', $currentBidAmount)
                ->orderByDesc('max_amount')
                ->get();

            if ($autoBids->isEmpty()) {
                return;
            }

            // Pre-fetch users to avoid N+1 queries in loop
            $autoBidUserIds = $autoBids->pluck('user_id')->toArray();
            $autoBidUsers = User::whereIn('id', $autoBidUserIds)->get()->keyBy('id');

            // Get highest auto-bid
            $highestAutoBid = $autoBids->first();

            // Determine bid amount (either max or minimum increment)
            $bidAmount = min($highestAutoBid->max_amount, $minimumBid);

            // Make sure it's higher than current bid
            if ($bidAmount <= $currentBidAmount) {
                $bidAmount = $minimumBid;
            }

            if ($bidAmount > $highestAutoBid->max_amount) {
                // Auto-bid max is lower than minimum bid, can't compete
                return;
            }

            // Place auto-bid in transaction
            $previousBidderId = $currentBidder->id;
            $currentBidderId = $highestAutoBid->user_id;

            DB::transaction(function () use ($auction, $bidAmount, $currentBidderId, $previousBidderId) {
                // Get current winning bid
                $currentWinningBid = $auction->bids()->winning()->first();

                // Create auto-bid
                $bid = AuctionBid::create([
                    'auction_id' => $auction->id,
                    'user_id' => $currentBidderId,
                    'amount' => $bidAmount,
                    'is_winning' => true,
                    'is_auto_bid' => true,
                ]);

                // Update previous winning bid
                if ($currentWinningBid) {
                    $currentWinningBid->update(['is_winning' => false]);
                }

                // Update auction
                $auction->current_price = $bidAmount;
                $auction->total_bids += 1;
                $auction->save();

                // Notify the human bidder they've been outbid by auto-bid
                AuctionNotification::create([
                    'auction_id' => $auction->id,
                    'user_id' => $previousBidderId,
                    'type' => AuctionNotification::TYPE_OUTBID,
                ]);
            });

            // Update for next iteration - use cached user from pre-fetched collection
            $currentBidAmount = $bidAmount;
            $currentBidder = $autoBidUsers->get($currentBidderId);

            if (! $currentBidder) {
                return;
            }
        }
    }

    /**
     * Get current winning bid for an auction.
     */
    public function getCurrentWinningBid(Auction $auction): ?AuctionBid
    {
        return $auction->bids()->winning()->first();
    }

    /**
     * Check and extend auction if needed (anti-sniping).
     */
    protected function checkAndExtend(Auction $auction): void
    {
        $extensionWindow = $auction->extension_minutes ?? config('auction.defaults.extension_minutes', 15);
        $extensionThreshold = now()->addMinutes($extensionWindow);

        if ($auction->ends_at->lessThanOrEqualTo($extensionThreshold)) {
            $auction->ends_at = $extensionThreshold->addMinute();
            $auction->save();
        }
    }

    /**
     * Notify user they've been outbid.
     */
    protected function notifyOutbid(Auction $auction, User $user, float $newBidAmount): void
    {
        AuctionNotification::create([
            'auction_id' => $auction->id,
            'user_id' => $user->id,
            'type' => AuctionNotification::TYPE_OUTBID,
        ]);
    }

    /**
     * Notify seller of new bid.
     */
    protected function notifySellerOfNewBid(Auction $auction, User $bidder, float $amount): void
    {
        AuctionNotification::create([
            'auction_id' => $auction->id,
            'user_id' => $auction->vendor->user_id,
            'type' => AuctionNotification::TYPE_NEW_BID,
        ]);
    }
}
