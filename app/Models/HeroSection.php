<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HeroSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'page_identifier',
        'title',
        'subtitle',
        'description',
        'background_image',
        'background_type',
        'gradient_from',
        'gradient_to',
        'statistics',
        'primary_button_text',
        'primary_button_link',
        'secondary_button_text',
        'secondary_button_link',
        'is_active',
        'order',
    ];

    protected $casts = [
        'statistics' => 'array',
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    public static function getByPage(string $pageIdentifier)
    {
        return static::where('page_identifier', $pageIdentifier)
            ->where('is_active', true)
            ->first();
    }
}
