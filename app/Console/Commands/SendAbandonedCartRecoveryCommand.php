<?php

namespace App\Console\Commands;

use App\Jobs\SendAbandonedCartRecoveryEmail;
use App\Models\Cart;
use App\Models\Order;
use Illuminate\Console\Command;

class SendAbandonedCartRecoveryCommand extends Command
{
    protected $signature = 'campaigns:send-abandoned-cart {--hours=2} {--max=500}';

    protected $description = 'Queue abandoned cart recovery emails for eligible users';

    public function handle(): int
    {
        $hours = max(1, (int) $this->option('hours'));
        $max = max(1, (int) $this->option('max'));

        $carts = Cart::query()
            ->whereNotNull('user_id')
            ->where('updated_at', '<=', now()->subHours($hours))
            ->where('updated_at', '>=', now()->subDays(7))
            ->whereHas('items')
            ->with(['user:id,email,name', 'items.product:id,name'])
            ->orderBy('updated_at')
            ->limit($max)
            ->get();

        $queued = 0;
        foreach ($carts as $cart) {
            if (! $cart->user || ! $cart->user->email) {
                continue;
            }

            $recentPaidOrderExists = Order::query()
                ->where('user_id', $cart->user_id)
                ->where('payment_status', 'paid')
                ->where('created_at', '>=', $cart->updated_at)
                ->exists();

            if ($recentPaidOrderExists) {
                continue;
            }

            SendAbandonedCartRecoveryEmail::dispatch($cart);
            $queued++;
        }

        $this->info("Abandoned cart recovery complete. Queued: {$queued}");

        return self::SUCCESS;
    }
}
