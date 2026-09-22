<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'type',
        'target',
        'vendor_ids',
        'is_active',
        'is_pinned',
        'published_at',
        'expires_at',
    ];

    protected $casts = [
        'vendor_ids' => 'array',
        'is_active' => 'boolean',
        'is_pinned' => 'boolean',
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function reads()
    {
        return $this->hasMany(AnnouncementRead::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    public function scopeForVendor($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('target', 'all_vendors')
                ->orWhere(function ($subQuery) use ($userId) {
                    $subQuery->where('target', 'specific_vendors')
                        ->whereJsonContains('vendor_ids', $userId);
                });
        });
    }

    public function scopeUnreadBy($query, $userId)
    {
        return $query->whereDoesntHave('reads', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }

    public function isReadBy($userId)
    {
        return $this->reads()->where('user_id', $userId)->exists();
    }

    public function markAsReadBy($userId)
    {
        return AnnouncementRead::firstOrCreate([
            'announcement_id' => $this->id,
            'user_id' => $userId,
        ], [
            'read_at' => now(),
        ]);
    }

    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isPublished()
    {
        // A null published_at means "publish immediately" -- this must
        // match scopeActive()'s semantics (whereNull(...)->orWhere(...)),
        // which is what actually gates whether the announcement appears
        // in a vendor's list. Treating null as "not published" here let a
        // vendor see an announcement in their list but get a 404 when
        // they clicked into it, for every announcement created without an
        // explicit future publish date -- the common case.
        return $this->published_at === null || $this->published_at->isPast();
    }
}
