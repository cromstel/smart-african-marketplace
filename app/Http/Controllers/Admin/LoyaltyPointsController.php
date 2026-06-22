<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoyaltyPoint;
use App\Models\Setting;
use App\Models\User;
use App\Services\LoyaltyPointsService;
use Illuminate\Http\Request;

class LoyaltyPointsController extends Controller
{
    protected $loyaltyService;

    public function __construct(LoyaltyPointsService $loyaltyService)
    {
        $this->middleware(['auth', 'admin']);
        $this->loyaltyService = $loyaltyService;
    }

    public function index(Request $request)
    {
        $query = LoyaltyPoint::with('user', 'order');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $points = $query->latest()->paginate(50);
        $users = User::where('loyalty_points_balance', '>', 0)->orderBy('name')->get();

        $stats = [
            'total_earned' => LoyaltyPoint::where('type', 'earned')->sum('points'),
            'total_redeemed' => abs(LoyaltyPoint::where('type', 'redeemed')->sum('points')),
            'total_expired' => abs(LoyaltyPoint::where('type', 'expired')->sum('points')),
            'active_balance' => User::sum('loyalty_points_balance'),
        ];

        return view('admin.loyalty.index', compact('points', 'users', 'stats'));
    }

    public function settings()
    {
        return view('admin.loyalty.settings');
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'loyalty_points_per_dollar' => 'required|integer|min:1|max:100',
            'loyalty_points_value' => 'required|numeric|min:0.001|max:1',
        ]);

        Setting::set('loyalty_points_per_dollar', $validated['loyalty_points_per_dollar']);
        Setting::set('loyalty_points_value', $validated['loyalty_points_value']);

        return redirect()->back()->with('success', 'Loyalty points settings updated successfully');
    }

    public function manualAdjust(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'points' => 'required|integer|not_in:0',
            'description' => 'required|string|max:255',
        ]);

        $user = User::findOrFail($validated['user_id']);

        LoyaltyPoint::create([
            'user_id' => $validated['user_id'],
            'points' => $validated['points'],
            'type' => $validated['points'] > 0 ? 'earned' : 'redeemed',
            'description' => $validated['description'].' (Admin adjustment)',
        ]);

        $user->increment('loyalty_points_balance', $validated['points']);

        return redirect()->back()->with('success', 'Points adjusted successfully');
    }
}
