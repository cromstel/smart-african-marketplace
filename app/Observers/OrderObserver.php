<?php

namespace App\Observers;

use App\Models\Order;
use App\Services\VendorLedgerService;

class OrderObserver
{
    public function updated(Order $order): void
    {
        if ($order->is_parent) {
            return;
        }

        if ($order->wasChanged('payment_status') && $order->payment_status === 'paid') {
            app(VendorLedgerService::class)->creditPending($order);
        }

        if ($order->wasChanged('status') && $order->status === 'delivered') {
            app(VendorLedgerService::class)->releaseToAvailable($order);
        }
    }
}
