<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class DataExportRequest extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_READY = 'ready';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'vendor_id',
        'requested_by',
        'reason',
        'notes',
        'status',
        'archive_path',
        'token',
        'approved_by',
        'admin_notes',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function markReady(string $path, int $approvedBy): void
    {
        $this->archive_path = $path;
        $this->approved_by = $approvedBy;
        $this->status = self::STATUS_READY;
        $this->token = Str::random(40);
        $this->save();
    }

    public function markRejected(string $notes, int $approvedBy): void
    {
        $this->admin_notes = $notes;
        $this->approved_by = $approvedBy;
        $this->status = self::STATUS_REJECTED;
        $this->save();
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeReady($query)
    {
        return $query->where('status', self::STATUS_READY);
    }
}
