<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'available',
        'pending',
        'lifetime_earnings',
    ];

    protected $casts = [
        'available' => 'decimal:2',
        'pending' => 'decimal:2',
        'lifetime_earnings' => 'decimal:2',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function scopeForVendor($query, int $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }
}
