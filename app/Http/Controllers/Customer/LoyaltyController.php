<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\LoyaltyPoint;
use App\Models\Setting;
use App\Services\LoyaltyPointsService;
use Illuminate\Http\Request;

class LoyaltyController extends Controller
{
    public function index(LoyaltyPointsService $loyaltyPointsService)
    {
        $user = auth()->user();
        $balance = (int) ($user->loyalty_points_balance ?? 0);
        $pointsValue = (float) Setting::get('loyalty_points_value', 0.01);

        $history = LoyaltyPoint::where('user_id', $user->id)
            ->latest()
            ->paginate(15);

        $runningBalance = $balance;
        foreach ($history as $entry) {
            $entry->balance_after = $runningBalance;
            $runningBalance -= (int) $entry->points;
        }

        $tiers = [
            ['name' => 'Bronze', 'threshold' => 0, 'benefits' => ['Base reward rate']],
            ['name' => 'Silver', 'threshold' => 500, 'benefits' => ['Priority support', '+5% seasonal perks']],
            ['name' => 'Gold', 'threshold' => 1500, 'benefits' => ['Early access deals', '+10% seasonal perks']],
            ['name' => 'Platinum', 'threshold' => 3000, 'benefits' => ['VIP campaign offers', '+15% seasonal perks']],
        ];

        $tierProgress = $loyaltyPointsService->getTierProgress($balance, $tiers);

        $earningOpportunities = [
            ['title' => 'Purchase products', 'points' => (int) Setting::get('loyalty_points_per_dollar', 1), 'unit' => 'point(s) per $1'],
            ['title' => 'Submit approved review', 'points' => 20, 'unit' => 'point(s) each'],
            ['title' => 'Refer a new customer', 'points' => 100, 'unit' => 'point(s) per successful referral'],
            ['title' => 'Join seasonal campaigns', 'points' => 50, 'unit' => 'bonus point(s)'],
        ];

        $expiringSoon = (int) LoyaltyPoint::query()
            ->where('user_id', $user->id)
            ->where('type', 'earned')
            ->where('points', '>', 0)
            ->whereNotNull('expires_at')
            ->whereBetween('expires_at', [now(), now()->addDays(30)])
            ->sum('points');

        $minRedeemPoints = (int) Setting::get('loyalty_min_redeem_points', 100);

        return view('pages.loyalty.index', compact(
            'balance',
            'history',
            'pointsValue',
            'tierProgress',
            'earningOpportunities',
            'expiringSoon',
            'minRedeemPoints'
        ));
    }

    public function redeem(Request $request, LoyaltyPointsService $loyaltyPointsService)
    {
        $validated = $request->validate([
            'points' => 'required|integer|min:1',
        ]);

        $user = auth()->user();
        $cart = Cart::where('user_id', $user->id)->first();
        if (! $cart || $cart->items()->count() === 0) {
            return redirect()->route('cart.index')->with('error', 'Add items to cart before redeeming loyalty points.');
        }

        if ($cart->coupon_code && $cart->coupon_code !== 'LOYALTY-POINTS') {
            return redirect()->route('cart.index')->with('error', 'Remove coupon code before redeeming loyalty points.');
        }

        $minRedeemPoints = (int) Setting::get('loyalty_min_redeem_points', 100);
        $requestedPoints = (int) $validated['points'];
        if ($requestedPoints < $minRedeemPoints) {
            return redirect()->route('loyalty.index')->with('error', "Minimum redemption is {$minRedeemPoints} points.");
        }

        $cart->calculateTotals();
        $pointsValue = (float) Setting::get('loyalty_points_value', 0.01);
        $eligibleAmount = max(0, (float) $cart->subtotal + (float) $cart->tax + (float) $cart->shipping);
        $maxPointsByCart = $pointsValue > 0 ? (int) floor($eligibleAmount / $pointsValue) : 0;
        $availablePoints = (int) $user->loyalty_points_balance;
        $pointsToRedeem = max(0, min($requestedPoints, $availablePoints, $maxPointsByCart));

        if ($pointsToRedeem < $minRedeemPoints) {
            return redirect()->route('cart.index')->with('error', 'Not enough eligible cart value or loyalty balance for redemption.');
        }

        $discount = $loyaltyPointsService->redeemPoints($user, $pointsToRedeem);

        $cart->update([
            'coupon_code' => 'LOYALTY-POINTS',
            'discount' => min($discount, $eligibleAmount),
        ]);
        $cart->calculateTotals();

        return redirect()->route('cart.index')->with('success', "Redeemed {$pointsToRedeem} points for $".number_format($discount, 2).' discount.');
    }
}
