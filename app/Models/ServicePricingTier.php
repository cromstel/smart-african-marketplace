<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServicePricingTier extends Model
{
    use HasFactory;

    protected $fillable = [
        'salon_service_id',
        'tier_name',
        'day_of_week',
        'start_time',
        'end_time',
        'price_modifier',
        'price_modifier_type',
        'minimum_duration',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'day_of_week' => 'integer',
        'price_modifier' => 'decimal:2',
        'minimum_duration' => 'integer',
    ];

    /**
     * Get the service this tier belongs to.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(SalonService::class, 'salon_service_id');
    }

    /**
     * Scope to get only active tiers.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Check if this tier applies to a given date and time.
     */
    public function appliesTo(Carbon $date, ?Carbon $time = null): bool
    {
        // Check day of week
        if ($this->day_of_week !== null && $this->day_of_week != $date->dayOfWeek) {
            return false;
        }

        // Check time range
        if ($this->start_time && $this->end_time && $time) {
            $tierStart = Carbon::parse($this->start_time);
            $tierEnd = Carbon::parse($this->end_time);
            $checkTime = Carbon::parse($time);

            if ($checkTime->lt($tierStart) || $checkTime->gte($tierEnd)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Calculate the modified price.
     */
    public function calculatePrice(float $basePrice): float
    {
        if ($this->price_modifier_type === 'percentage') {
            return $basePrice + ($basePrice * (float) $this->price_modifier / 100);
        }

        return $basePrice + (float) $this->price_modifier;
    }

    /**
     * Get the tier label for display.
     */
    public function getLabelAttribute(): string
    {
        $label = $this->tier_name;

        if ($this->day_of_week !== null) {
            $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            $label .= ' - '.$days[$this->day_of_week];
        }

        if ($this->start_time && $this->end_time) {
            $label .= ' '.Carbon::parse($this->start_time)->format('g:i A').'-'.Carbon::parse($this->end_time)->format('g:i A');
        }

        return $label;
    }

    /**
     * Get modifier display (e.g., "+20%" or "+$10")
     */
    public function getModifierDisplayAttribute(): string
    {
        if ($this->price_modifier_type === 'percentage') {
            return ((float) $this->price_modifier > 0 ? '+' : '').$this->price_modifier.'%';
        }

        return ((float) $this->price_modifier > 0 ? '+$' : '-$').number_format(abs((float) $this->price_modifier), 2);
    }
}
