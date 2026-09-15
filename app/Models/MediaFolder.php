<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MediaFolder extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'parent_id',
        // vendor_id was added by a later migration (add_vendor_id_to_media_folders_table)
        // but never added here, so every MediaFolder::create() call passing it had the
        // value silently dropped by mass assignment -- every vendor's root folder ended
        // up with vendor_id NULL. That broke every `where('vendor_id', ...)` scoping
        // query in Vendor\MediaController, and caused a crash (duplicate slug) the first
        // time a vendor uploaded media: VendorObserver creates the vendor's root folder
        // on Vendor::created() (vendor_id silently NULL), then getOrCreateVendorFolder()'s
        // "does this vendor already have a folder" check never matches it, so it tries to
        // create a second folder with the same slug and hits the unique constraint.
        'vendor_id',
        'owner_type',
        'owner_id',
        'created_by_id',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(MediaFolder::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(MediaFolder::class, 'parent_id');
    }

    public function mediaItems(): HasMany
    {
        return $this->hasMany(MediaLibrary::class, 'folder_id');
    }

    public function owner(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'owner_type', 'owner_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function scopeOwnedBy($query, string $type, int $id)
    {
        return $query->where('owner_type', $type)->where('owner_id', $id);
    }
}
