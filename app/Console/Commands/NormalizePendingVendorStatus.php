<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NormalizePendingVendorStatus extends Command
{
    protected $signature = 'vendors:normalize-pending-status';

    protected $description = 'Normalize vendor records so pending vendors are inactive';

    public function handle(): int
    {
        $updated = DB::table('vendors')
            ->where('status', 'pending')
            ->where('is_active', true)
            ->update(['is_active' => false]);

        Log::info('Normalized pending vendor statuses', ['updated_rows' => $updated]);

        $this->info("Normalized {$updated} pending vendor record(s).");

        return self::SUCCESS;
    }
}
