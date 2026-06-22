<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RmaRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'rma_number',
        'order_id',
        'order_item_id',
        'user_id',
        'type',
        'reason',
        'description',
        'images',
        'status',
        'vendor_response',
        'admin_notes',
        'approved_at',
        'rejected_at',
        'completed_at',
        'refund_issued',
        'refund_amount',
    ];

    protected $casts = [
        'images' => 'array',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'completed_at' => 'datetime',
        'refund_issued' => 'boolean',
        'refund_amount' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($rma) {
            if (empty($rma->rma_number)) {
                $rma->rma_number = 'RMA-'.strtoupper(uniqid());
            }
        });
    }
}
