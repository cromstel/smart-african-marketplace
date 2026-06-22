<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\RmaRequest;
use Illuminate\Http\Request;

class RmaRequestController extends Controller
{
    public function index(Request $request)
    {
        $vendorId = auth()->id();

        $query = RmaRequest::whereHas('orderItem.product', function ($q) use ($vendorId) {
            $q->where('vendor_id', $vendorId);
        })->with('order', 'orderItem.product', 'user');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $rmas = $query->latest()->paginate(20);

        $statusCounts = RmaRequest::whereHas('orderItem.product', function ($q) use ($vendorId) {
            $q->where('vendor_id', $vendorId);
        })->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return view('vendor.rma.index', compact('rmas', 'statusCounts'));
    }

    public function show($id)
    {
        $vendorId = auth()->id();

        $rma = RmaRequest::whereHas('orderItem.product', function ($q) use ($vendorId) {
            $q->where('vendor_id', $vendorId);
        })->with('order', 'orderItem.product', 'user')->findOrFail($id);

        return view('vendor.rma.show', compact('rma'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected,processing,completed',
            'response' => 'required|string|max:1000',
        ]);

        $vendorId = auth()->id();

        $rma = RmaRequest::whereHas('orderItem.product', function ($q) use ($vendorId) {
            $q->where('vendor_id', $vendorId);
        })->findOrFail($id);

        $updateData = [
            'status' => $request->status,
            'vendor_response' => $request->response,
        ];

        if ($request->status === 'approved') {
            $updateData['approved_at'] = now();
        } elseif ($request->status === 'rejected') {
            $updateData['rejected_at'] = now();
        } elseif ($request->status === 'completed') {
            $updateData['completed_at'] = now();
        }

        $rma->update($updateData);

        return redirect()->back()->with('success', 'RMA status updated successfully.');
    }
}
