<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_number',
        'user_id',
        'salon_service_id',
        'booking_date',
        'booking_time',
        'duration_minutes',
        'price',
        'status',
        'customer_notes',
        'staff_notes',
        'cancelled_at',
        'completed_at',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'duration_minutes' => 'integer',
        'price' => 'decimal:2',
        'cancelled_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function salonService(): BelongsTo
    {
        return $this->belongsTo(SalonService::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
