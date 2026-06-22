<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuctionNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'auction_id',
        'user_id',
        'type',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function auction(): BelongsTo
    {
        return $this->belongsTo(Auction::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function markAsRead(): void
    {
        $this->update(['is_read' => true]);
    }

    // Notification type constants
    public const TYPE_OUTBID = 'outbid';

    public const TYPE_WINNING = 'winning';

    public const TYPE_WON = 'won';

    public const TYPE_LOST = 'lost';

    public const TYPE_ENDING_SOON = 'ending_soon';

    public const TYPE_SETTLED = 'settled';

    public const TYPE_CANCELLED = 'cancelled';

    public const TYPE_NEW_BID = 'new_bid';
}
