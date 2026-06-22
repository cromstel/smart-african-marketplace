<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariation extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'sku',
        'attributes',
        'price',
        'compare_at_price',
        'stock_quantity',
        'low_stock_threshold',
        'image',
        'is_active',
        'weight',
        'dimensions',
    ];

    protected $casts = [
        'attributes' => 'array',
        'price' => 'decimal:2',
        'compare_at_price' => 'decimal:2',
        'dimensions' => 'array',
        'is_active' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock_quantity', '<=', 'low_stock_threshold');
    }

    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->low_stock_threshold;
    }

    public function isInStock(): bool
    {
        return $this->is_active && $this->stock_quantity > 0;
    }

    public function getNameAttribute(): string
    {
        $attributes = $this->getAttribute('attributes') ?? [];
        if (! is_array($attributes) || empty($attributes)) {
            return $this->sku ?? 'Variant';
        }

        return collect($attributes)
            ->map(fn ($value, $key) => ucfirst((string) $key).': '.(string) $value)
            ->implode(' / ');
    }
}
