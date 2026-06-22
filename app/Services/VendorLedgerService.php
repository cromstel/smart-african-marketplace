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

        DB::transaction(function () use ($order, $itemsByVendor, $total, $amount) {
            foreach ($itemsByVendor as $vendorId => $items) {
                if (! $vendorId) {
                    continue;
                }
                $vendor = Vendor::find($vendorId);
                if (! $vendor) {
                    continue;
                }

                $vendorGross = $items->sum('total');
                $share = $vendorGross / $total;
                $debit = $amount * $share;

                $balance = VendorBalance::firstOrCreate(
                    ['vendor_id' => $vendorId],
                    ['available' => 0, 'pending' => 0, 'lifetime_earnings' => 0]
                );

                $balance->available = max(0, $balance->available - $debit);
                $balance->save();

                VendorLedger::create([
                    'vendor_id' => $vendorId,
                    'order_id' => $order->id,
                    'type' => 'debit_refund',
                    'amount' => $debit,
                    'balance_after' => $balance->available,
                    'metadata' => [
                        'reason' => 'refund',
                    ],
                ]);
            }
        });
    }
}
