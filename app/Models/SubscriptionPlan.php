<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class SubscriptionPlan extends Model
{
    use HasFactory, HasSlug;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'currency',
        'billing_cycle',
        'trial_days',
        'features',
        'feature_keys',
        'max_products',
        'max_orders',
        'commission_rate',
        'is_popular',
        'is_active',
        'sort_order',
        'paystack_plan_code',
        'stripe_price_id',
    ];

    protected $casts = [
        'features' => 'array',
        'feature_keys' => 'array',
        'price' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'is_popular' => 'boolean',
        'is_active' => 'boolean',
        'trial_days' => 'integer',
        'max_products' => 'integer',
        'max_orders' => 'integer',
        'sort_order' => 'integer',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    public function subscriptions()
    {
        return $this->hasMany(UserSubscription::class);
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePopular($query)
    {
        return $query->where('is_popular', true);
    }

    public function hasUnlimitedProducts(): bool
    {
        return $this->max_products === -1;
    }

    public function hasUnlimitedOrders(): bool
    {
        return $this->max_orders === -1;
    }

    public function getFormattedPriceAttribute(): string
    {
        if ($this->price == 0) {
            return 'Free';
        }

        return $this->currency.' '.number_format($this->price, 2);
    }

    public function getFeatureKeysAttribute($value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    public function hasFeatureKey(string $key): bool
    {
        if ($key === '') {
            return false;
        }

        $aliases = config('modules.route_key_aliases', []);
        $resolvedKey = $aliases[$key] ?? $key;
        $featureKeys = $this->feature_keys ?? [];

        return in_array($resolvedKey, $featureKeys, true) || in_array($key, $featureKeys, true);
    }
}
