<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Referral;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReferralController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index(Request $request)
    {
        $query = Referral::with(['referrer', 'referred']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('referrer_id')) {
            $query->where('referrer_id', $request->referrer_id);
        }

        $referrals = $query->latest()->paginate(50);

        $stats = [
            'total_referrals' => Referral::count(),
            'pending' => Referral::where('status', 'pending')->count(),
            'completed' => Referral::where('status', 'completed')->count(),
            'total_rewards_paid' => Referral::where('reward_paid', true)->sum('reward_amount'),
            'pending_rewards' => Referral::where('reward_paid', false)->sum('reward_amount'),
        ];

        $topReferrers = User::where('referral_earnings', '>', 0)
            ->orderByDesc('referral_earnings')
            ->limit(10)
            ->get();

        $thirtyDaysAgo = now()->subDays(30)->startOfDay();
        $recentReferrals = Referral::where('created_at', '>=', $thirtyDaysAgo)->count();
        $recentCompleted = Referral::where('created_at', '>=', $thirtyDaysAgo)
            ->where('status', 'completed')
            ->count();

        $analytics = [
            'conversion_rate_30d' => $recentReferrals > 0 ? round(($recentCompleted / $recentReferrals) * 100, 1) : 0,
            'avg_reward_completed' => (float) Referral::where('status', 'completed')->avg('reward_amount'),
            'new_referrals_30d' => $recentReferrals,
            'completed_30d' => $recentCompleted,
            'trend_14d' => Referral::query()
                ->where('created_at', '>=', now()->subDays(13)->startOfDay())
                ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
                ->groupBy('day')
                ->orderBy('day')
                ->get()
                ->map(function ($row) {
                    return [
                        'label' => Carbon::parse($row->day)->format('M d'),
                        'total' => (int) $row->total,
                    ];
                }),
        ];

        return view('admin.referrals.index', compact('referrals', 'stats', 'topReferrers', 'analytics'));
    }

    public function settings()
    {
        return view('admin.referrals.settings');
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'referral_reward_amount' => 'required|numeric|min:0',
            'referral_min_purchase' => 'required|numeric|min:0',
            'referral_enabled' => 'required|boolean',
        ]);

        Setting::set('referral_reward_amount', $validated['referral_reward_amount']);
        Setting::set('referral_min_purchase', $validated['referral_min_purchase']);
        Setting::set('referral_enabled', $validated['referral_enabled']);

        return redirect()->back()->with('success', 'Referral settings updated successfully');
    }

    public function approve(Referral $referral)
    {
        if ($referral->status !== 'pending') {
            return redirect()->back()->with('error', 'Only pending referrals can be approved');
        }

        $referral->update([
            'status' => 'completed',
            'reward_paid' => true,
        ]);

        $referral->referrer->increment('referral_earnings', $referral->reward_amount);

        return redirect()->back()->with('success', 'Referral approved and reward paid');
    }

    public function reject(Referral $referral)
    {
        if ($referral->status !== 'pending') {
            return redirect()->back()->with('error', 'Only pending referrals can be rejected');
        }

        $referral->update(['status' => 'rejected']);

        return redirect()->back()->with('success', 'Referral rejected');
    }
}
