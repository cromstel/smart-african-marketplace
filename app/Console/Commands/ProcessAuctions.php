<?php

namespace App\Console\Commands;

use App\Models\Auction;
use App\Services\Auction\AuctionService;
use Illuminate\Console\Command;

class ProcessAuctions extends Command
{
    protected $signature = 'auction:process';

    protected $description = 'Process auction lifecycle - activate, end, and settle auctions';

    public function __construct(
        protected AuctionService $auctionService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->activateScheduledAuctions();
        $this->endActiveAuctions();
        $this->settleEndedAuctions();

        return 0;
    }

    /**
     * Activate scheduled auctions that have reached their start time.
     */
    protected function activateScheduledAuctions(): void
    {
        $auctions = Auction::scheduled()
            ->where('starts_at', '<=', now())
            ->get();

        foreach ($auctions as $auction) {
            try {
                $this->auctionService->activate($auction);
                $this->info("Activated auction: {$auction->id} - {$auction->title}");
            } catch (\Exception $e) {
                $this->error("Failed to activate auction {$auction->id}: {$e->getMessage()}");
            }
        }
    }

    /**
     * End active auctions that have reached their end time.
     */
    protected function endActiveAuctions(): void
    {
        $auctions = Auction::active()
            ->where('ends_at', '<=', now())
            ->get();

        foreach ($auctions as $auction) {
            try {
                $this->auctionService->end($auction);
                $this->info("Ended auction: {$auction->id} - {$auction->title}");
            } catch (\Exception $e) {
                $this->error("Failed to end auction {$auction->id}: {$e->getMessage()}");
            }
        }
    }

    /**
     * Settle ended auctions that haven't been settled yet.
     */
    protected function settleEndedAuctions(): void
    {
        // Regression: this checked `created_at` -- when the auction
        // *listing* was made -- not when it ended. auctions has no
        // dedicated ended_at column, but AuctionService::end() only ever
        // does $auction->update(['status' => 'ended']), which bumps
        // updated_at to exactly that moment; that's the real "how long has
        // this been ended" signal. Checking created_at instead meant the
        // 5-minute grace period was a no-op for any realistic auction
        // (created well over 5 minutes before it ends) -- settlement fired
        // on the very first run after ending, no wait at all -- while a
        // short-duration auction created and ended within the same
        // 5-minute window would have settlement delayed for an unrelated
        // reason (time since creation, not time since ending).
        $auctions = Auction::ended()
            ->where('updated_at', '<', now()->subMinutes(5))
            ->get();

        foreach ($auctions as $auction) {
            try {
                $this->auctionService->settle($auction);
                $this->info("Settled auction: {$auction->id} - {$auction->title}");
            } catch (\Exception $e) {
                $this->error("Failed to settle auction {$auction->id}: {$e->getMessage()}");
            }
        }
    }
}
