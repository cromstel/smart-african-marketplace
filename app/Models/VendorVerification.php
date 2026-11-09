<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'status',
        'documents',
        'notes',
        'verified_at',
        'submitted_ip',
        'submitted_user_agent',
        'reviewed_by',
        'reviewed_ip',
        'reviewed_at',
    ];

    protected $casts = [
        'documents' => 'array',
        'verified_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function scopeForVendor($query, int $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
