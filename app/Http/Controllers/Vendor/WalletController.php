<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\VendorBalance;
use App\Models\VendorLedger;

class WalletController extends Controller
{
    public function index()
    {
        $vendorId = auth()->user()->vendor?->id;
        $balance = VendorBalance::firstOrCreate(
            ['vendor_id' => $vendorId],
            ['available' => 0, 'pending' => 0, 'lifetime_earnings' => 0]
        );

        $ledger = VendorLedger::where('vendor_id', $vendorId)->latest()->paginate(20);

        return view('vendor.wallet.index', compact('balance', 'ledger'));
    }
}
