<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalonService extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'salon_id',
        'stylist_id',
        'salon_category_id',
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'duration_minutes',
        'image',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'duration_minutes' => 'integer',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function salon(): BelongsTo
    {
        return $this->belongsTo(Salon::class);
    }

    public function stylist(): BelongsTo
    {
        return $this->belongsTo(SalonStylist::class, 'stylist_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function salonCategory(): BelongsTo
    {
        return $this->belongsTo(SalonServiceCategory::class, 'salon_category_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
