<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

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
}
