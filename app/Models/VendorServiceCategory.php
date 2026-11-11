<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VendorServiceCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'category_type',
        'gender_target',
        'icon',
        'parent_id',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(VendorServiceCategory::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(VendorServiceCategory::class, 'parent_id')
            ->orderBy('sort_order')
            ->orderBy('name');
    }

    public function vendorServices(): HasMany
    {
        return $this->hasMany(VendorService::class);
    }

    public function salonServices(): HasMany
    {
        return $this->hasMany(SalonService::class, 'vendor_service_category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategoryType($query, string $type)
    {
        return $query->where('category_type', $type);
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
