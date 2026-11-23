<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductQuestion extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'user_id',
        'asker_name',
        'asker_email',
        'question',
        'status',
        'is_public',
        'answer',
        'answered_by',
        'answered_at',
        'moderated_by',
        'moderated_at',
        'moderation_note',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'answered_at' => 'datetime',
        'moderated_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function asker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function answeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'answered_by');
    }

    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeAnswered($query)
    {
        return $query->where('status', 'answered');
    }

    public function scopePublic($query)
    {
        return $query->where('status', 'answered')
            ->where('is_public', true);
    }

    public function scopeForProduct($query, Product|int $product)
    {
        $productId = $product instanceof Product ? $product->id : $product;

        return $query->where('product_id', $productId);
    }

    public function scopeForVendor($query, Vendor|int $vendor)
    {
        $vendorId = $vendor instanceof Vendor ? $vendor->id : $vendor;

        return $query->whereHas('product', function ($query) use ($vendorId) {
            $query->where('vendor_id', $vendorId);
        });
    }
}
