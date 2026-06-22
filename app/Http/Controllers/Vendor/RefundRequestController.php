<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\RefundRequest;

class RefundRequestController extends Controller
{
    public function index()
    {
        $vendorId = auth()->user()->vendor?->id;

        $refunds = RefundRequest::whereHas('order.items.product', function ($query) use ($vendorId) {
            $query->where('vendor_id', $vendorId);
        })->with(['order', 'user'])->latest()->paginate(20);

        return view('vendor.refunds.index', compact('refunds'));
    }

    public function show(RefundRequest $refund)
    {
        $vendorId = auth()->user()->vendor?->id;

        $hasAccess = $refund->order->items->contains(function ($item) use ($vendorId) {
            return $item->product?->vendor_id === $vendorId;
        });

        if (! $hasAccess) {
            abort(403);
        }

        $refund->load(['order.items.product', 'user']);

        return view('vendor.refunds.show', compact('refund'));
    }

    public function approve(RefundRequest $refund)
    {
        $this->authorizeVendorRefund($refund);

        if ($refund->status !== 'pending' || $refund->vendor_status !== 'pending') {
            return redirect()->back()->with('error', 'Refund cannot be updated.');
        }

        $refund->update([
            'vendor_status' => 'approved',
            'vendor_notes' => request('vendor_notes'),
            'vendor_responded_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Refund marked as approved. Admin will finalize.');
    }

    public function reject(RefundRequest $refund)
    {
        $this->authorizeVendorRefund($refund);

        if ($refund->status !== 'pending' || $refund->vendor_status !== 'pending') {
            return redirect()->back()->with('error', 'Refund cannot be updated.');
        }

        $refund->update([
            'vendor_status' => 'rejected',
            'vendor_notes' => request('vendor_notes'),
            'vendor_responded_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Refund marked as rejected. Admin will review.');
    }

    private function authorizeVendorRefund(RefundRequest $refund): void
    {
        $vendorId = auth()->user()->vendor?->id;

        $hasAccess = $refund->order->items->contains(function ($item) use ($vendorId) {
            return $item->product?->vendor_id === $vendorId;
        });

        if (! $hasAccess) {
            abort(403);
        }
    }
}
