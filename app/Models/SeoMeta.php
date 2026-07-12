<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SeoMeta extends Model
{
    use HasFactory;

    protected $table = 'seo_meta';

    protected $fillable = [
        'model_type',
        'model_id',
        'title',
        'description',
        'keywords',
        'og_image',
    ];

    public function model(): MorphTo
    {
        return $this->morphTo();
    }
}
