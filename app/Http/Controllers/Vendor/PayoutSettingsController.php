<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PayoutSettingsController extends Controller
{
    public function edit()
    {
        $vendor = auth()->user()->vendor;

        return view('vendor.settings.payouts', compact('vendor'));
    }

    public function update(Request $request)
    {
        $vendor = auth()->user()->vendor;

        $validated = $request->validate([
            'stripe_account_id' => 'nullable|string|max:255',
            'paystack_recipient_code' => 'nullable|string|max:255',
            'bank_account_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_code' => 'nullable|string|max:20',
            'bank_name' => 'nullable|string|max:255',
            'momo_provider' => 'nullable|string|max:100',
            'momo_number' => 'nullable|string|max:50',
        ]);

        $vendor->update($validated);

        return redirect()->route('vendor.settings.payouts.edit')->with('success', 'Payout settings updated.');
    }
}
