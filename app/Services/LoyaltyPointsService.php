<?php

namespace App\Services;

use App\Models\LoyaltyPoint;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;

class LoyaltyPointsService
{
    public function awardPointsForPurchase(User $user, float $amount, int $orderId): void
    {
        $pointsPerDollar = (int) Setting::get('loyalty_points_per_dollar', 1);
        $points = (int) floor($amount * $pointsPerDollar);

        if ($points > 0) {
            LoyaltyPoint::create([
                'user_id' => $user->id,
                'points' => $points,
                'type' => 'earned',
                'description' => "Earned {$points} points from purchase",
                'order_id' => $orderId,
                'expires_at' => Carbon::now()->addYear(),
            ]);

            $user->increment('loyalty_points_balance', $points);
        }
    }

    public function redeemPoints(User $user, int $points): float
    {
        if ($points > $user->loyalty_points_balance) {
            throw new \Exception('Insufficient loyalty points');
        }

        $pointsValue = (float) Setting::get('loyalty_points_value', 0.01);
        $discount = $points * $pointsValue;

        LoyaltyPoint::create([
            'user_id' => $user->id,
            'points' => -$points,
            'type' => 'redeemed',
            'description' => "Redeemed {$points} points for \${$discount} discount",
        ]);

        $user->decrement('loyalty_points_balance', $points);

        return $discount;
    }

    public function getPointsBalance(User $user): int
    {
        return $user->loyalty_points_balance;
    }

    public function getPointsHistory(User $user)
    {
        return LoyaltyPoint::where('user_id', $user->id)
            ->latest()
            ->paginate(20);
    }

    public function expireOldPoints(): int
    {
        $expiredPoints = LoyaltyPoint::where('expires_at', '<=', now())
            ->where('type', 'earned')
            ->where('points', '>', 0)
            ->get();

        $totalExpired = 0;

        foreach ($expiredPoints as $point) {
            LoyaltyPoint::create([
                'user_id' => $point->user_id,
                'points' => -$point->points,
                'type' => 'expired',
                'description' => "{$point->points} points expired",
            ]);

            $user = User::find($point->user_id);
            $user->decrement('loyalty_points_balance', $point->points);

            $point->delete();
            $totalExpired += $point->points;
        }

        return $totalExpired;
    }

    public function getTierProgress(int $balance, array $tiers): array
    {
        $currentTier = $tiers[0];
        $nextTier = null;

        foreach ($tiers as $index => $tier) {
            if ($balance >= (int) $tier['threshold']) {
                $currentTier = $tier;
                $nextTier = $tiers[$index + 1] ?? null;
            }
        }

        if (! $nextTier) {
            return [
                'current' => $currentTier,
                'next' => null,
                'progress_percent' => 100,
                'points_to_next' => 0,
            ];
        }

        $range = max(1, (int) $nextTier['threshold'] - (int) $currentTier['threshold']);
        $earnedInTier = max(0, $balance - (int) $currentTier['threshold']);
        $progressPercent = min(100, round(($earnedInTier / $range) * 100, 1));

        return [
            'current' => $currentTier,
            'next' => $nextTier,
            'progress_percent' => $progressPercent,
            'points_to_next' => max(0, (int) $nextTier['threshold'] - $balance),
        ];
    }
}
