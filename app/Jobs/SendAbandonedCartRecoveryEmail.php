<?php

namespace App\Jobs;

use App\Models\Cart;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

/**
 * Roadmap 3.2: previously sent inline inside
 * SendAbandonedCartRecoveryCommand's own foreach loop -- one SMTP hiccup on
 * recipient #50 of 500 threw and aborted the whole scheduled run, silently
 * skipping the other 450. Each recipient is now its own retryable unit.
 */
class SendAbandonedCartRecoveryEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [60, 300, 900];

    public function __construct(public Cart $cart)
    {
        $this->onQueue('campaigns');
    }

    /**
     * Re-checks the same eligibility guard the command applied at dispatch
     * time. The job may run minutes after being queued, so the user could
     * have checked out (or the cart could have been deleted/emptied) in the
     * meantime -- re-verifying here, not just at dispatch, is what actually
     * prevents a stale "you left items in your cart" email.
     */
    public function handle(): void
    {
        $cart = $this->cart->fresh(['user:id,email,name', 'items.product:id,name']);

        if (! $cart || ! $cart->user || ! $cart->user->email || $cart->items->isEmpty()) {
            return;
        }

        $recentPaidOrderExists = Order::query()
            ->where('user_id', $cart->user_id)
            ->where('payment_status', 'paid')
            ->where('created_at', '>=', $cart->updated_at)
            ->exists();

        if ($recentPaidOrderExists) {
            return;
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
    }
}
