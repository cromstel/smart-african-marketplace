<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookingResource extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'resource_type',
        'name',
        'description',
        'sku',
        'quantity_total',
        'quantity_available',
        'is_active',
        'minimum_rental_hours',
        'pricing_type',
        'hourly_rate',
        'daily_rate',
        'fixed_rate',
        'image',
        'specifications',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'quantity_total' => 'integer',
        'quantity_available' => 'integer',
        'minimum_rental_hours' => 'integer',
        'hourly_rate' => 'decimal:2',
        'daily_rate' => 'decimal:2',
        'fixed_rate' => 'decimal:2',
        'specifications' => 'array',
    ];

    /**
     * Get the vendor that owns this resource.
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get bookings for this resource.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'resource_name', 'name');
    }

    /**
     * Scope to get only active resources.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by resource type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('resource_type', $type);
    }

    /**
     * Check if resource is available.
     */
    public function isAvailable(int $quantity = 1): bool
    {
        $available = $this->quantity_available ?? $this->quantity_total;

        return $available >= $quantity;
    }

    /**
     * Get the current available quantity.
     */
    public function getAvailableQuantity(): int
    {
        return $this->quantity_available ?? $this->quantity_total;
    }

    /**
     * Calculate price for given duration.
     */
    public function calculatePrice(int $hours = 0, int $days = 0): float
    {
        $price = 0;

        switch ($this->pricing_type) {
            case 'hourly':
                $price = ($this->hourly_rate ?? 0) * max($hours, 1);
                break;
            case 'daily':
                $price = ($this->daily_rate ?? 0) * max($days, 1);
                break;
            case 'fixed':
                $price = $this->fixed_rate ?? 0;
                break;
        }

        return $price;
    }

    /**
     * Get formatted price label.
     */
    public function getPriceLabelAttribute(): string
    {
        $rate = 0;
        $suffix = '/day';

        switch ($this->pricing_type) {
            case 'hourly':
                $rate = $this->hourly_rate ?? 0;
                $suffix = '/hr';
                break;
            case 'daily':
                $rate = $this->daily_rate ?? 0;
                $suffix = '/day';
                break;
            case 'fixed':
                $rate = $this->fixed_rate ?? 0;
                $suffix = '';
                break;
            default:
                $rate = $this->daily_rate ?? 0;
                $suffix = '/day';
        }

        return '$'.number_format($rate, 2).$suffix;
    }
}
