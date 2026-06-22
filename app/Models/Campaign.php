<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'channel',
        'campaign_type',
        'audience_segment',
        'subject',
        'content',
        'banner_image_url',
        'status',
        'scheduled_at',
        'flash_sale_ends_at',
        'sent_at',
        'created_by',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'flash_sale_ends_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(CampaignRecipient::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(CampaignEvent::class);
    }

    public function attributions(): HasMany
    {
        return $this->hasMany(LeadAttribution::class);
    }
}
