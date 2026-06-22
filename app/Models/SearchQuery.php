<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SearchQuery extends Model
{
    use HasFactory;

    protected $fillable = [
        'query',
        'hits',
        'results_count',
        'last_searched_at',
        'last_user_id',
        'last_ip',
        'last_user_agent',
    ];

    protected $casts = [
        'last_searched_at' => 'datetime',
    ];

    public function lastUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_user_id');
    }
}
