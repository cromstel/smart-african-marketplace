<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductExportLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_name',
        'file_path',
        'status',
        'format',
        'total_rows',
        'exported_rows',
        'user_id',
        'category_id',
        'vendor_id',
        'filters',
        'columns',
    ];

    protected $casts = [
        'total_rows' => 'integer',
        'exported_rows' => 'integer',
        'filters' => 'array',
        'columns' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function getFiltersArray(): array
    {
        return $this->filters ?? [];
    }

    public function getColumnsArray(): array
    {
        return $this->columns ?? [];
    }
}
