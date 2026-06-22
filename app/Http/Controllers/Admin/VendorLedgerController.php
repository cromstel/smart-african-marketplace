<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Models\VendorBalance;
use App\Models\VendorLedger;

class VendorLedgerController extends Controller
{
    public function show(Vendor $vendor)
    {
        $balance = VendorBalance::firstOrCreate(
            ['vendor_id' => $vendor->id],
            ['available' => 0, 'pending' => 0, 'lifetime_earnings' => 0]
        );
        $ledger = VendorLedger::where('vendor_id', $vendor->id)->latest()->paginate(20);

        return view('admin.vendors.ledger', compact('vendor', 'balance', 'ledger'));
    }
}
