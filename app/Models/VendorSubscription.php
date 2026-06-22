<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'plan_name',
        'plan_price',
        'status',
        'trial_ends_at',
        'current_period_start',
        'current_period_end',
        'cancel_at_period_end',
        'canceled_at',
        'notification_sent',
        'notification_sent_at',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'current_period_start' => 'datetime',
        'current_period_end' => 'datetime',
        'canceled_at' => 'datetime',
        'notification_sent_at' => 'datetime',
        'cancel_at_period_end' => 'boolean',
        'notification_sent' => 'boolean',
        'plan_price' => 'decimal:2',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function isTrialing(): bool
    {
        return $this->status === 'trialing' &&
               $this->trial_ends_at &&
               $this->trial_ends_at->isFuture();
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['trialing', 'active']) &&
               (! $this->current_period_end || $this->current_period_end->isFuture());
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired' ||
               ($this->current_period_end && $this->current_period_end->isPast());
    }

    public function daysUntilExpiry(): int
    {
        if ($this->isTrialing() && $this->trial_ends_at) {
            return max(0, now()->diffInDays($this->trial_ends_at, false));
        }

        if ($this->current_period_end) {
            return max(0, now()->diffInDays($this->current_period_end, false));
        }

        return 0;
    }

    public function shouldNotifyExpiring(): bool
    {
        $daysLeft = $this->daysUntilExpiry();

        return ! $this->notification_sent &&
               $daysLeft <= 7 &&
               $daysLeft >= 0 &&
               $this->isActive();
    }

    public function markNotificationSent(): void
    {
        $this->update([
            'notification_sent' => true,
            'notification_sent_at' => now(),
        ]);
    }
}
