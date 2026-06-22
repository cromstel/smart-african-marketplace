<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'banner_image',
        'parent_id',
        'type',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function salonServices(): HasMany
    {
        return $this->hasMany(SalonService::class);
    }

    public function seoMeta()
    {
        return $this->morphOne(SeoMeta::class, 'model');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFood($query)
    {
        return $query->where('type', 'food');
    }

    public function scopeClothing($query)
    {
        return $query->where('type', 'clothing');
    }

    public function scopeSalon($query)
    {
        return $query->where('type', 'salon');
    }

    public function scopeParents($query)
    {
        return $query->whereNull('parent_id');
    }

    public function fullPath(): string
    {
        $segments = [$this->name];
        $parent = $this->parent;

        while ($parent) {
            array_unshift($segments, $parent->name);
            $parent = $parent->parent;
        }

        return implode(' > ', $segments);
    }

    public function isDescendantOf(int $categoryId): bool
    {
        $parent = $this->parent;
        while ($parent) {
            if ($parent->id === $categoryId) {
                return true;
            }
            $parent = $parent->parent;
        }

        return false;
    }
}
