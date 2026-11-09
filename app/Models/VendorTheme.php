<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorTheme extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'primary_color',
        'secondary_color',
        'accent_color',
        'text_color',
        'background_color',
        'font_family',
        'font_heading',
        'banner_image',
        'logo',
        'favicon',
        'custom_css',
        'custom_js',
        'layout_style',
        'show_categories',
        'show_filters',
        'show_search',
        'about_text',
        'contact_info',
        'social_links',
        'is_active',
    ];

    protected $casts = [
        'social_links' => 'array',
        'show_categories' => 'boolean',
        'show_filters' => 'boolean',
        'show_search' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function scopeForVendor($query, int $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    public function getDefaultColors(): array
    {
        return [
            'primary' => $this->primary_color ?? '#ea580c',
            'secondary' => $this->secondary_color ?? '#dc2626',
            'accent' => $this->accent_color ?? '#f97316',
            'text' => $this->text_color ?? '#1f2937',
            'background' => $this->background_color ?? '#ffffff',
        ];
    }

    public function getSocialLinksArray(): array
    {
        return $this->social_links ?? [];
    }
}
