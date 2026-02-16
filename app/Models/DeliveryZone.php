<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryZone extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'cities',
        'postal_codes',
        'base_fee',
        'per_km_rate',
        'max_distance_km',
        'is_active',
    ];

    protected $casts = [
        'cities' => 'array',
        'postal_codes' => 'array',
        'base_fee' => 'decimal:2',
        'per_km_rate' => 'decimal:2',
        'max_distance_km' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function calculateDeliveryFee(float $distanceKm): float
    {
        return $this->base_fee + ($distanceKm * $this->per_km_rate);
    }
}
