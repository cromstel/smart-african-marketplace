<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Payout;
use App\Models\VendorBalance;
use Illuminate\Http\Request;

class PayoutController extends Controller
{
    public function index()
    {
        $vendorId = auth()->user()->vendor?->id;
        $balance = VendorBalance::firstOrCreate(
            ['vendor_id' => $vendorId],
            ['available' => 0, 'pending' => 0, 'lifetime_earnings' => 0]
        );
        $payouts = Payout::where('vendor_id', $vendorId)->latest()->paginate(20);

        return view('vendor.payouts.index', compact('payouts', 'balance'));
    }

    public function create()
    {
        return view('vendor.payouts.create');
    }

    public function store(Request $request)
    {
        $vendorId = auth()->user()->vendor?->id;
        $vendor = auth()->user()->vendor;
        $verificationStatus = auth()->user()->vendor?->verification?->status;
        if ($verificationStatus !== 'approved') {
            return redirect()->back()->with('error', 'Vendor verification is required before requesting payouts.');
        }
        $balance = VendorBalance::firstOrCreate(
            ['vendor_id' => $vendorId],
            ['available' => 0, 'pending' => 0, 'lifetime_earnings' => 0]
        );

        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'method' => 'required|in:stripe,paystack,momo,bank',
        ]);

        if ($validated['amount'] > $balance->available) {
            return redirect()->back()->with('error', 'Insufficient available balance.');
        }

        $metadata = [];

        if ($validated['method'] === 'stripe' && ! $vendor?->stripe_account_id) {
            return redirect()->back()->with('error', 'Stripe account ID is required. Update it in Payout Settings.');
        }

        if ($validated['method'] === 'paystack' && (! $vendor?->bank_account_number || ! $vendor?->bank_code)) {
            return redirect()->back()->with('error', 'Paystack bank details are required. Update them in Payout Settings.');
        }

        if ($validated['method'] === 'bank' && (! $vendor?->bank_account_number || ! $vendor?->bank_name)) {
            return redirect()->back()->with('error', 'Bank details are required. Update them in Payout Settings.');
        }

        if ($validated['method'] === 'momo' && ! $vendor?->momo_number) {
            return redirect()->back()->with('error', 'Mobile money number is required. Update it in Payout Settings.');
        }

        Payout::create([
            'vendor_id' => $vendorId,
            'amount' => $validated['amount'],
            'method' => $validated['method'],
            'status' => 'pending',
            'metadata' => $metadata,
        ]);

        return redirect()->route('vendor.payouts.index')->with('success', 'Payout request submitted.');
    }
}
