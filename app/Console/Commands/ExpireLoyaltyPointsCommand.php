<?php

namespace App\Console\Commands;

use App\Services\LoyaltyPointsService;
use Illuminate\Console\Command;

class ExpireLoyaltyPointsCommand extends Command
{
    protected $signature = 'loyalty:expire-points';

    protected $description = 'Expire old loyalty points that reached their expiry date';

    public function handle(LoyaltyPointsService $loyaltyPointsService): int
    {
        $expired = $loyaltyPointsService->expireOldPoints();
        $this->info("Expired {$expired} loyalty points.");

        return self::SUCCESS;
    }
}
