<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class MediaLibrary extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'media_library';

    protected $fillable = [
        'title',
        'alt_text',
        'file_name',
        'original_name',
        'file_path',
        'original_file_path',
        'webp_path',
        'mime_type',
        'file_size',
        'width',
        'height',
        'type',
        'uploaded_by_type',
        'uploaded_by_id',
        'context',
        'context_id',
        'folder_id',
        'vendor_id',
        'metadata',
        'is_optimized',
    ];

    protected $casts = [
        'metadata' => 'array',
        'file_size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'is_optimized' => 'boolean',
    ];

    public function uploadedBy()
    {
        return $this->morphTo();
    }

    public function folder()
    {
        return $this->belongsTo(MediaFolder::class, 'folder_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function getUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }

    public function getWebpUrlAttribute(): ?string
    {
        if ($this->webp_path) {
            return Storage::url($this->webp_path);
        }

        return null;
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $power = $bytes > 0 ? floor(log($bytes, 1024)) : 0;

        return number_format($bytes / pow(1024, $power), 2).' '.$units[$power];
    }

    public function scopeImages($query)
    {
        return $query->where('type', 'image');
    }

    public function scopeDocuments($query)
    {
        return $query->where('type', 'document');
    }

    public function scopeVideos($query)
    {
        return $query->where('type', 'video');
    }

    public function scopeForVendor($query, int $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    public function scopeOwnedBy($query, $type, $id)
    {
        return $query->where('uploaded_by_type', $type)->where('uploaded_by_id', $id);
    }

    public function scopeContext($query, $context, $contextId = null)
    {
        $query->where('context', $context);
        if ($contextId) {
            $query->where('context_id', $contextId);
        }

        return $query;
    }

    public function scopeOptimized($query)
    {
        return $query->where('is_optimized', true);
    }

    public function scopeNotOptimized($query)
    {
        return $query->where('is_optimized', false);
    }

    public function markAsOptimized(?string $originalPath = null): void
    {
        $this->is_optimized = true;
        if ($originalPath) {
            $this->original_file_path = $originalPath;
        }
        $this->save();
    }

    public function canDeleteOriginal(): bool
    {
        return $this->is_optimized && $this->original_file_path !== null;
    }

    public function deleteOriginal(): bool
    {
        if (! $this->canDeleteOriginal()) {
            return false;
        }

        try {
            if (Storage::disk('public')->exists($this->original_file_path)) {
                Storage::disk('public')->delete($this->original_file_path);
                \Log::info('Original file deleted successfully', [
                    'media_id' => $this->id,
                    'original_path' => $this->original_file_path,
                ]);

                return true;
            }
        } catch (\Exception $e) {
            \Log::error('Failed to delete original file', [
                'media_id' => $this->id,
                'original_path' => $this->original_file_path,
                'error' => $e->getMessage(),
            ]);
        }

        return false;
    }

    public function generateAltText(): string
    {
        if ($this->title) {
            return $this->title;
        }

        $filename = pathinfo($this->original_name, PATHINFO_FILENAME);
        $altText = str_replace(['-', '_'], ' ', $filename);
        $altText = ucwords($altText);

        return $altText;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($media) {
            if (! $media->alt_text) {
                $media->alt_text = $media->generateAltText();
            }
        });
    }
}
