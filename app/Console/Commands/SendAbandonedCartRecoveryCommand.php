<?php

namespace App\Console\Commands;

use App\Models\Cart;
use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendAbandonedCartRecoveryCommand extends Command
{
    protected $signature = 'campaigns:send-abandoned-cart {--hours=2} {--max=500}';

    protected $description = 'Send abandoned cart recovery emails to eligible users';

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

        $sent = 0;
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

            $itemCount = $cart->items->sum('quantity');
            $cartTotal = number_format((float) $cart->total, 2);

            $html = '<div style="font-family:Arial,sans-serif;color:#1f2937;line-height:1.5;">';
            $html .= '<h2 style="margin:0 0 12px;">Complete your order</h2>';
            $html .= '<p>You still have '.$itemCount.' item(s) in your cart worth $'.$cartTotal.'.</p>';
            $html .= '<p><a href="'.route('cart.index').'" style="display:inline-block;background:#ea580c;color:#fff;padding:10px 16px;border-radius:8px;text-decoration:none;">Return to cart</a></p>';
            $html .= '<p style="color:#6b7280;font-size:12px;margin-top:16px;">If you already completed checkout, please ignore this email.</p>';
            $html .= '</div>';

            Mail::html($html, function ($message) use ($cart) {
                $message->to($cart->user->email)->subject('You left items in your cart');
            });

            $sent++;
        }

        $this->info("Abandoned cart recovery complete. Sent: {$sent}");

        return self::SUCCESS;
    }
}
