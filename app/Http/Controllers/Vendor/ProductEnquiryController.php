<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Mail\EnquiryResponse;
use App\Models\ProductEnquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ProductEnquiryController extends Controller
{
    public function index(Request $request)
    {
        $vendorId = auth()->user()->vendor?->id;

        $query = ProductEnquiry::whereHas('product', function ($q) use ($vendorId) {
            $q->where('vendor_id', $vendorId);
        })->with('product', 'user');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $enquiries = $query->latest()->paginate(20);

        $statusCounts = ProductEnquiry::whereHas('product', function ($q) use ($vendorId) {
            $q->where('vendor_id', $vendorId);
        })->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return view('vendor.enquiries.index', compact('enquiries', 'statusCounts'));
    }

    public function show($id)
    {
        $vendorId = auth()->user()->vendor?->id;

        $enquiry = ProductEnquiry::whereHas('product', function ($q) use ($vendorId) {
            $q->where('vendor_id', $vendorId);
        })->with('product', 'user', 'respondedBy')->findOrFail($id);

        return view('vendor.enquiries.show', compact('enquiry'));
    }

    public function respond(Request $request, $id)
    {
        $request->validate([
            'response' => 'required|string|max:2000',
        ]);

        $vendorId = auth()->user()->vendor?->id;

        $enquiry = ProductEnquiry::whereHas('product', function ($q) use ($vendorId) {
            $q->where('vendor_id', $vendorId);
        })->findOrFail($id);

        $enquiry->update([
            'response' => $request->response,
            'responded_by' => auth()->id(),
            'responded_at' => now(),
            'status' => 'responded',
        ]);

        if (class_exists(EnquiryResponse::class)) {
            Mail::to($enquiry->email)->send(new EnquiryResponse($enquiry));
        }

        return redirect()->back()->with('success', 'Response sent successfully.');
    }

    public function close($id)
    {
        $vendorId = auth()->user()->vendor?->id;

        $enquiry = ProductEnquiry::whereHas('product', function ($q) use ($vendorId) {
            $q->where('vendor_id', $vendorId);
        })->findOrFail($id);

        $enquiry->update(['status' => 'closed']);

        return redirect()->back()->with('success', 'Enquiry closed successfully.');
    }
}
