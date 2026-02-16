<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'type',
        'value',
        'min_purchase_amount',
        'max_discount_amount',
        'usage_limit',
        'usage_count',
        'per_user_limit',
        'starts_at',
        'expires_at',
        'is_active',
        'description',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_purchase_amount' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            });
    }

    public function isValid(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->usage_limit && $this->usage_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    public function canBeUsedByUser($userId, $cartTotal): array
    {
        if (! $this->isValid()) {
            return ['valid' => false, 'message' => 'This coupon is no longer valid'];
        }

        if ($this->min_purchase_amount && $cartTotal < $this->min_purchase_amount) {
            return ['valid' => false, 'message' => 'Minimum purchase amount is $'.number_format($this->min_purchase_amount, 2)];
        }

        if ($this->per_user_limit) {
            $userUsageCount = Order::where('user_id', $userId)
                ->where('coupon_code', $this->code)
                ->count();

            if ($userUsageCount >= $this->per_user_limit) {
                return ['valid' => false, 'message' => 'You have reached the usage limit for this coupon'];
            }
        }

        return ['valid' => true, 'message' => 'Coupon is valid'];
    }

    public function calculateDiscount($cartTotal): float
    {
        if ($this->type === 'fixed') {
            return min($this->value, $cartTotal);
        }

        $discount = ($cartTotal * $this->value) / 100;

        if ($this->max_discount_amount) {
            $discount = min($discount, $this->max_discount_amount);
        }

        return round($discount, 2);
    }

    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }
}
