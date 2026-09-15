<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Vendor;
use App\Models\VendorBalance;
use App\Models\VendorLedger;
use Illuminate\Support\Facades\DB;

class VendorLedgerService
{
    public function creditPending(Order $order): void
    {
        $itemsByVendor = $order->items->groupBy('vendor_id');

        DB::transaction(function () use ($order, $itemsByVendor) {
            foreach ($itemsByVendor as $vendorId => $items) {
                if (! $vendorId) {
                    continue;
                }
                $vendor = Vendor::find($vendorId);
                if (! $vendor) {
                    continue;
                }

                $gross = $items->sum(fn (OrderItem $item) => $item->total);
                $commissionRate = $vendor->commission_rate ?? config('marketplace.default_commission_rate', 10);
                $net = max(0, $gross * (1 - $commissionRate / 100));

                $balance = VendorBalance::firstOrCreate(
                    ['vendor_id' => $vendorId],
                    ['available' => 0, 'pending' => 0, 'lifetime_earnings' => 0]
                );

                $balance->pending += $net;
                $balance->lifetime_earnings += $net;
                $balance->save();

                VendorLedger::create([
                    'vendor_id' => $vendorId,
                    'order_id' => $order->id,
                    'type' => 'credit_pending',
                    'amount' => $net,
                    'balance_after' => $balance->pending,
                    'metadata' => [
                        'gross' => $gross,
                        'commission_rate' => $commissionRate,
                    ],
                ]);
            }
        });
    }

    public function releaseToAvailable(Order $order): void
    {
        $itemsByVendor = $order->items->groupBy('vendor_id');

        DB::transaction(function () use ($order, $itemsByVendor) {
            foreach ($itemsByVendor as $vendorId => $items) {
                if (! $vendorId) {
                    continue;
                }
                $vendor = Vendor::find($vendorId);
                if (! $vendor) {
                    continue;
                }

                $gross = $items->sum(fn (OrderItem $item) => $item->total);
                $commissionRate = $vendor->commission_rate ?? config('marketplace.default_commission_rate', 10);
                $net = max(0, $gross * (1 - $commissionRate / 100));

                $balance = VendorBalance::firstOrCreate(
                    ['vendor_id' => $vendorId],
                    ['available' => 0, 'pending' => 0, 'lifetime_earnings' => 0]
                );

                $balance->pending = max(0, $balance->pending - $net);
                $balance->available += $net;
                $balance->save();

                VendorLedger::create([
                    'vendor_id' => $vendorId,
                    'order_id' => $order->id,
                    'type' => 'credit_available',
                    'amount' => $net,
                    'balance_after' => $balance->available,
                    'metadata' => [
                        'gross' => $gross,
                        'commission_rate' => $commissionRate,
                    ],
                ]);
            }
        });
    }

    /**
     * Reverse (all or part of) a vendor's ledger credit(s) for a refunded
     * order. $amount need not be the full order total -- it is split
     * across vendors on a shared order in proportion to each vendor's
     * share of the order's gross, same as before.
     *
     * Fixes two bugs in the previous implementation: it always debited
     * VendorBalance.available, clamped to zero -- so a refund issued
     * before the order reached "delivered" (while its earnings still sat
     * in .pending, since releaseToAvailable() had not run yet) debited a
     * bucket that never held the money and silently did nothing; and it
     * never touched lifetime_earnings, so that figure stayed permanently
     * overstated after any refund. This now debits whichever bucket the
     * order's earnings currently sit in, and reverses lifetime_earnings
     * by the same amount. A vendor never actually credited for this order
     * (e.g. it was refunded before payment was ever confirmed) is
     * correctly skipped rather than gaining a stray zero-value balance.
     */
    public function debitRefund(Order $order, float $amount): void
    {
        $items = $order->items;
        if ($items->isEmpty() && $order->group) {
            $items = $order->group->orders->where('is_parent', false)->flatMap->items;
        }

        $itemsByVendor = $items->groupBy('vendor_id');
        $total = $itemsByVendor->sum(fn ($items) => $items->sum('total'));
        if ($total <= 0) {
            return;
        }

        $orderIds = $items->pluck('order_id')->filter()->unique();

        DB::transaction(function () use ($itemsByVendor, $total, $amount, $orderIds) {
            foreach ($itemsByVendor as $vendorId => $vendorItems) {
                if (! $vendorId) {
                    continue;
                }
                $vendor = Vendor::find($vendorId);
                if (! $vendor) {
                    continue;
                }

                $wasCredited = VendorLedger::where('vendor_id', $vendorId)
                    ->whereIn('order_id', $orderIds)
                    ->where('type', 'credit_pending')
                    ->exists();
                if (! $wasCredited) {
                    continue;
                }

                $vendorGross = $vendorItems->sum('total');
                $share = $vendorGross / $total;
                $debit = $amount * $share;

                $alreadyReleased = VendorLedger::where('vendor_id', $vendorId)
                    ->whereIn('order_id', $orderIds)
                    ->where('type', 'credit_available')
                    ->exists();

                $balance = VendorBalance::firstOrCreate(
                    ['vendor_id' => $vendorId],
                    ['available' => 0, 'pending' => 0, 'lifetime_earnings' => 0]
                );

                if ($alreadyReleased) {
                    $balance->available = max(0, $balance->available - $debit);
                } else {
                    $balance->pending = max(0, $balance->pending - $debit);
                }
                $balance->lifetime_earnings = max(0, $balance->lifetime_earnings - $debit);
                $balance->save();

                VendorLedger::create([
                    'vendor_id' => $vendorId,
                    'order_id' => $orderIds->first(),
                    'type' => 'debit_refund',
                    'amount' => $debit,
                    'balance_after' => $alreadyReleased ? $balance->available : $balance->pending,
                    'metadata' => [
                        'reason' => 'refund',
                        'released' => $alreadyReleased,
                    ],
                ]);
            }
        });
    }
}
