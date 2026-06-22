<?php

namespace App\Services\Auction;

use App\Models\Auction;
use App\Models\AuctionNotification as AuctionNotificationModel;
use App\Models\AuctionWinner;
use App\Models\User;
use App\Models\Vendor;
use App\Notifications\AuctionNotification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class AuctionService
{
    /**
     * Create a new auction.
     */
    public function create(array $data, Vendor $vendor): Auction
    {
        $auction = new Auction([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'starting_price' => $data['starting_price'],
            'reserve_price' => $data['reserve_price'] ?? null,
            'buy_now_price' => $data['buy_now_price'] ?? null,
            'bid_increment' => $data['bid_increment'] ?? config('auction.defaults.bid_increment', 1.00),
            'starts_at' => $data['starts_at'],
            'ends_at' => $data['ends_at'],
            'original_ends_at' => $data['ends_at'],
            'extension_minutes' => $data['extension_minutes'] ?? config('auction.defaults.extension_minutes', 15),
            'type' => $data['type'] ?? 'english',
            'status' => 'draft',
            'current_price' => $data['starting_price'],
            'product_id' => $data['product_id'] ?? null,
        ]);

        $auction->vendor()->associate($vendor);
        $auction->save();

        return $auction;
    }

    /**
     * Update an auction.
     */
    public function update(Auction $auction, array $data): Auction
    {
        $auction->fill(array_filter([
            'title' => $data['title'] ?? null,
            'description' => $data['description'] ?? null,
            'starting_price' => $data['starting_price'] ?? null,
            'reserve_price' => $data['reserve_price'] ?? null,
            'buy_now_price' => $data['buy_now_price'] ?? null,
            'bid_increment' => $data['bid_increment'] ?? null,
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
            'extension_minutes' => $data['extension_minutes'] ?? null,
            'type' => $data['type'] ?? null,
        ], fn ($value) => $value !== null));

        // If starting price is changed and no bids exist, update current_price
        if (isset($data['starting_price']) && $auction->total_bids === 0) {
            $auction->current_price = $data['starting_price'];
        }

        $auction->save();

        return $auction;
    }

    /**
     * Schedule an auction (change status from draft to scheduled).
     */
    public function schedule(Auction $auction): Auction
    {
        if ($auction->status !== 'draft') {
            throw new \InvalidArgumentException('Only draft auctions can be scheduled.');
        }

        if (! $auction->starts_at || ! $auction->ends_at) {
            throw new \InvalidArgumentException('Auction must have start and end times.');
        }

        if ($auction->starts_at->isPast()) {
            $auction->update([
                'status' => 'scheduled',
            ]);

            // If starts_at is in the past, activate immediately
            return $this->activate($auction);
        }

        $auction->update(['status' => 'scheduled']);

        return $auction;
    }

    /**
     * Activate a scheduled auction.
     */
    public function activate(Auction $auction): Auction
    {
        if (! in_array($auction->status, ['draft', 'scheduled'])) {
            throw new \InvalidArgumentException('Only draft or scheduled auctions can be activated.');
        }

        if ($auction->starts_at->isFuture()) {
            throw new \InvalidArgumentException('Auction start time has not arrived yet.');
        }

        $auction->update(['status' => 'active']);

        return $auction;
    }

    /**
     * Cancel an auction.
     */
    public function cancel(Auction $auction, string $reason = ''): Auction
    {
        if (in_array($auction->status, ['ended', 'settled'])) {
            throw new \InvalidArgumentException('Ended or settled auctions cannot be cancelled.');
        }

        $auction->update([
            'status' => 'cancelled',
            'cancel_reason' => $reason,
        ]);

        // Notify all bidders
        $this->notifyBiddersOfCancellation($auction);

        return $auction;
    }

    /**
     * End an active auction.
     */
    public function end(Auction $auction): Auction
    {
        if ($auction->status !== 'active') {
            throw new \InvalidArgumentException('Only active auctions can be ended.');
        }

        $auction->update(['status' => 'ended']);

        return $auction;
    }

    /**
     * Settle an ended auction (determine winner).
     */
    public function settle(Auction $auction): Auction
    {
        if ($auction->status !== 'ended') {
            throw new \InvalidArgumentException('Only ended auctions can be settled.');
        }

        $winningBid = $auction->bids()->winning()->first();

        if (! $winningBid) {
            $auction->update(['status' => 'settled']);

            return $auction;
        }

        // Check if reserve price is met
        if ($auction->reserve_price && $winningBid->amount < $auction->reserve_price) {
            $auction->update(['status' => 'settled']);

            return $auction;
        }

        // Create winner record
        $winner = AuctionWinner::create([
            'auction_id' => $auction->id,
            'user_id' => $winningBid->user_id,
            'bid_id' => $winningBid->id,
            'final_price' => $winningBid->amount,
            'status' => 'pending',
        ]);

        $auction->update([
            'status' => 'settled',
            'winner_id' => $winningBid->user_id,
            'winning_bid_id' => $winningBid->id,
        ]);

        // Notify winner
        $this->notifyWinner($auction, $winner);

        // Notify other bidders they lost
        $this->notifyLosers($auction, $winningBid->user_id);

        return $auction;
    }

    /**
     * Extend an auction's end time.
     */
    public function extend(Auction $auction, int $minutes): Auction
    {
        if ($auction->status !== 'active') {
            throw new \InvalidArgumentException('Only active auctions can be extended.');
        }

        $newEndTime = $auction->ends_at->addMinutes($minutes);

        // If original_ends_at is not set, save current as original
        if (! $auction->original_ends_at) {
            $auction->original_ends_at = $auction->ends_at;
        }

        $auction->update(['ends_at' => $newEndTime]);

        return $auction;
    }

    /**
     * Get active auctions with optional filters.
     */
    public function getActiveAuctions(array $filters = []): LengthAwarePaginator
    {
        $query = Auction::active()
            ->with(['vendor', 'product']);

        if (isset($filters['vendor_id'])) {
            $query->where('vendor_id', $filters['vendor_id']);
        }

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', "%{$filters['search']}%")
                    ->orWhere('description', 'like', "%{$filters['search']}%");
            });
        }

        return $query->orderByDesc('created_at')->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Get ended auctions.
     */
    public function getEndedAuctions(array $filters = []): LengthAwarePaginator
    {
        $query = Auction::ended()
            ->with(['vendor', 'winner']);

        if (isset($filters['vendor_id'])) {
            $query->where('vendor_id', $filters['vendor_id']);
        }

        return $query->orderByDesc('ends_at')->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Get featured auctions.
     */
    public function getFeaturedAuctions(int $limit = 10): Collection
    {
        return Auction::featured()
            ->with(['vendor'])
            ->limit($limit)
            ->get();
    }

    /**
     * Get auctions ending soon.
     */
    public function getEndingSoon(int $hours = 24, int $limit = 10): Collection
    {
        return Auction::endingSoon($hours)
            ->with(['vendor'])
            ->orderBy('ends_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Toggle featured status.
     */
    public function toggleFeatured(Auction $auction): Auction
    {
        $auction->update(['is_featured' => ! $auction->is_featured]);

        return $auction;
    }

    /**
     * Notify all bidders of cancellation.
     */
    protected function notifyBiddersOfCancellation(Auction $auction): void
    {
        $bidderIds = $auction->bids()->distinct()->pluck('user_id');

        foreach ($bidderIds as $userId) {
            AuctionNotificationModel::create([
                'auction_id' => $auction->id,
                'user_id' => $userId,
                'type' => AuctionNotificationModel::TYPE_CANCELLED,
            ]);
        }
    }

    /**
     * Notify winner of auction settlement.
     */
    protected function notifyWinner(Auction $auction, AuctionWinner $winner): void
    {
        AuctionNotificationModel::create([
            'auction_id' => $auction->id,
            'user_id' => $winner->user_id,
            'type' => AuctionNotificationModel::TYPE_WON,
        ]);

        // Also send email notification
        $user = User::find($winner->user_id);
        if ($user) {
            $user->notify(new AuctionNotification($auction, AuctionNotification::TYPE_WON));
        }
    }

    /**
     * Notify losers that they lost.
     */
    protected function notifyLosers(Auction $auction, int $winnerId): void
    {
        $loserIds = $auction->bids()
            ->where('user_id', '!=', $winnerId)
            ->distinct()
            ->pluck('user_id');

        foreach ($loserIds as $userId) {
            AuctionNotificationModel::create([
                'auction_id' => $auction->id,
                'user_id' => $userId,
                'type' => AuctionNotificationModel::TYPE_LOST,
            ]);
        }
    }
}
