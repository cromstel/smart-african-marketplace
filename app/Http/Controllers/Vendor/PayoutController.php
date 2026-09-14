<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Payout;
use App\Models\VendorBalance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $vendor = auth()->user()->vendor;
        $vendorId = $vendor?->id;
        $verificationStatus = $vendor?->verification?->status;
        if ($verificationStatus !== 'approved') {
            return redirect()->back()->with('error', 'Vendor verification is required before requesting payouts.');
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'method' => 'required|in:stripe,paystack,momo,bank',
        ]);

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

        // The balance check and the reservation of funds must happen as one
        // atomic, locked step. Checking `available` and then creating the
        // Payout as two separate statements left a window where two requests
        // -- concurrent, or simply back-to-back before either had reserved
        // anything -- could each pass the check against the same stale
        // balance and together reserve more than the vendor actually has.
        // lockForUpdate() serializes concurrent requests for this vendor's
        // balance row, and the reservation itself (deducting `available`
        // immediately, not just when the payout later completes) is what
        // makes the second request see the true remaining balance.
        $payout = DB::transaction(function () use ($vendorId, $validated) {
            VendorBalance::firstOrCreate(
                ['vendor_id' => $vendorId],
                ['available' => 0, 'pending' => 0, 'lifetime_earnings' => 0]
            );

            $balance = VendorBalance::where('vendor_id', $vendorId)->lockForUpdate()->first();

            if ($validated['amount'] > $balance->available) {
                return null;
            }

            $balance->available -= $validated['amount'];
            $balance->save();

            return Payout::create([
                'vendor_id' => $vendorId,
                'amount' => $validated['amount'],
                'method' => $validated['method'],
                'status' => 'pending',
                'metadata' => [],
            ]);
        });

        if (! $payout) {
            return redirect()->back()->with('error', 'Insufficient available balance.');
        }

        return redirect()->route('vendor.payouts.index')->with('success', 'Payout request submitted.');
    }
}
