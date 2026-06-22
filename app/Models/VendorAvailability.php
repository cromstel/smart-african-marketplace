<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorAvailability extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'day_of_week',
        'start_time',
        'end_time',
        'slot_interval_minutes',
        'min_lead_time_minutes',
        'buffer_minutes',
        'is_active',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
        'slot_interval_minutes' => 'integer',
        'min_lead_time_minutes' => 'integer',
        'buffer_minutes' => 'integer',
        'is_active' => 'boolean',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
