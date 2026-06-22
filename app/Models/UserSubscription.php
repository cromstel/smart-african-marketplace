<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subscription_plan_id',
        'status',
        'trial_ends_at',
        'current_period_start',
        'current_period_end',
        'cancel_at_period_end',
        'canceled_at',
        'payment_provider',
        'payment_provider_subscription_id',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'current_period_start' => 'datetime',
        'current_period_end' => 'datetime',
        'canceled_at' => 'datetime',
        'cancel_at_period_end' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function payments()
    {
        return $this->hasMany(SubscriptionPayment::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active' || $this->status === 'trialing';
    }

    public function isTrialing(): bool
    {
        return $this->status === 'trialing' && $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    public function isPastDue(): bool
    {
        return $this->status === 'past_due';
    }

    public function isCanceled(): bool
    {
        return $this->status === 'canceled';
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired';
    }

    public function onTrial(): bool
    {
        return $this->isTrialing();
    }

    public function onGracePeriod(): bool
    {
        return $this->cancel_at_period_end && $this->current_period_end && $this->current_period_end->isFuture();
    }

    public function cancel($immediately = false)
    {
        if ($immediately) {
            $this->update([
                'status' => 'canceled',
                'canceled_at' => now(),
                'cancel_at_period_end' => false,
            ]);
        } else {
            $this->update([
                'cancel_at_period_end' => true,
            ]);
        }

        return $this;
    }

    public function resume()
    {
        if ($this->onGracePeriod()) {
            $this->update([
                'cancel_at_period_end' => false,
            ]);

            return true;
        }

        return false;
    }

    public function hasReachedProductLimit(int $currentCount): bool
    {
        if ($this->plan->hasUnlimitedProducts()) {
            return false;
        }

        return $currentCount >= $this->plan->max_products;
    }

    public function hasReachedOrderLimit(int $currentMonthOrders): bool
    {
        if ($this->plan->hasUnlimitedOrders()) {
            return false;
        }

        return $currentMonthOrders >= $this->plan->max_orders;
    }

    public function daysUntilTrialEnds(): ?int
    {
        if (! $this->isTrialing()) {
            return null;
        }

        return now()->diffInDays($this->trial_ends_at, false);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['active', 'trialing']);
    }

    public function scopeTrialing($query)
    {
        return $query->where('status', 'trialing');
    }

    public function scopeExpiringSoon($query, int $days = 7)
    {
        return $query->where('status', 'trialing')
            ->where('trial_ends_at', '<=', now()->addDays($days))
            ->where('trial_ends_at', '>', now());
    }
}
