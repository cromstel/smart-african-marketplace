<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Referral;
use Illuminate\Support\Str;

class ReferralController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if (! $user->referral_code) {
            $user->update(['referral_code' => strtoupper(Str::random(8))]);
        }

        $referrals = Referral::where('referrer_id', $user->id)
            ->with('referred')
            ->latest()
            ->paginate(15);

        $earnings = $user->referral_earnings ?? 0;
        $referralShareUrl = route('register', ['ref' => $user->referral_code]);

        return view('pages.referrals.index', compact('referrals', 'earnings', 'referralShareUrl'));
    }
}
