<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalonStylist extends Model
{
    use HasFactory;

    protected $fillable = [
        'salon_id',
        'name',
        'email',
        'phone',
        'bio',
        'years_experience',
        'is_active',
    ];

    protected $casts = [
        'years_experience' => 'integer',
        'is_active' => 'boolean',
    ];

    public function salon(): BelongsTo
    {
        return $this->belongsTo(Salon::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(SalonService::class, 'stylist_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'stylist_id');
    }
}
