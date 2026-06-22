<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'tracking_number',
        'carrier',
        'carrier_url',
        'label_url',
        'label_provider',
        'status',
        'current_location',
        'tracking_history',
        'shipped_at',
        'estimated_delivery',
        'delivered_at',
        'delivery_notes',
        'received_by',
    ];

    protected $casts = [
        'tracking_history' => 'array',
        'shipped_at' => 'datetime',
        'estimated_delivery' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function addTrackingUpdate(string $status, string $location, ?string $notes = null): void
    {
        $history = $this->tracking_history ?? [];
        $history[] = [
            'status' => $status,
            'location' => $location,
            'notes' => $notes,
            'timestamp' => now()->toDateTimeString(),
        ];
        $this->tracking_history = $history;
        $this->current_location = $location;
        $this->save();
    }
}
