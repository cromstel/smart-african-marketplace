<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\RefundRequest;
use App\Services\ActivityLogger;
use App\Services\VendorLedgerService;
use Illuminate\Http\Request;

class RefundRequestController extends Controller
{
    public function index()
    {
        $refunds = RefundRequest::with(['order', 'user'])
            ->latest()
            ->paginate(20);

        return view('admin.refunds.index', compact('refunds'));
    }

    public function show(RefundRequest $refund)
    {
        $refund->load(['order.items.product', 'user']);

        return view('admin.refunds.show', compact('refund'));
    }

    public function approve(Request $request, RefundRequest $refund)
    {
        if ($refund->status !== 'pending') {
            return redirect()->back()->with('error', 'Only pending refunds can be approved.');
        }

        $refund->update([
            'status' => 'approved',
            'resolution_notes' => $request->input('resolution_notes'),
            'resolved_at' => now(),
        ]);

        app(ActivityLogger::class)->log('refund.approved', $refund);

        return redirect()->back()->with('success', 'Refund approved. Use "Mark Refunded" after issuing payment.');
    }

    public function markRefunded(Request $request, RefundRequest $refund)
    {
        if (! in_array($refund->status, ['approved', 'pending'], true)) {
            return redirect()->back()->with('error', 'Refund cannot be marked as refunded.');
        }

        $order = Order::findOrFail($refund->order_id);
        $order->update([
            'status' => 'refunded',
            'payment_status' => 'refunded',
        ]);

        $refund->update([
            'status' => 'refunded',
            'resolution_notes' => $request->input('resolution_notes'),
            'resolved_at' => now(),
        ]);

        app(VendorLedgerService::class)->debitRefund($order, (float) $order->total);
        app(ActivityLogger::class)->log('refund.refunded', $refund, ['order_id' => $order->id]);

        return redirect()->back()->with('success', 'Refund marked as completed.');
    }

    public function reject(Request $request, RefundRequest $refund)
    {
        if ($refund->status !== 'pending') {
            return redirect()->back()->with('error', 'Only pending refunds can be rejected.');
        }

        $refund->update([
            'status' => 'rejected',
            'resolution_notes' => $request->input('resolution_notes'),
            'resolved_at' => now(),
        ]);

        app(ActivityLogger::class)->log('refund.rejected', $refund);

        return redirect()->back()->with('success', 'Refund request rejected.');
    }
}
