<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Auction extends Model
{
    use HasFactory, HasSlug;

    /**
     * Auction status constants.
     */
    public const STATUS_DRAFT = 'draft';

    public const STATUS_SCHEDULED = 'scheduled';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_ENDED = 'ended';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_SETTLED = 'settled';

    /**
     * Auction type constants.
     */
    public const TYPE_ENGLISH = 'english';

    public const TYPE_DUTCH = 'dutch';

    public const TYPE_SEALED = 'sealed';

    public const TYPE_BUY_NOW = 'buy_now';

    protected $fillable = [
        'vendor_id',
        'product_id',
        'title',
        'description',
        'starting_price',
        'reserve_price',
        'buy_now_price',
        'current_price',
        'bid_increment',
        'starts_at',
        'ends_at',
        'original_ends_at',
        'extension_minutes',
        'status',
        'type',
        'winner_id',
        'winning_bid_id',
        'total_bids',
        'view_count',
        'is_featured',
        'cancel_reason',
    ];

    protected $casts = [
        'starting_price' => 'decimal:2',
        'reserve_price' => 'decimal:2',
        'buy_now_price' => 'decimal:2',
        'current_price' => 'decimal:2',
        'bid_increment' => 'decimal:2',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'original_ends_at' => 'datetime',
        'total_bids' => 'integer',
        'view_count' => 'integer',
        'is_featured' => 'boolean',
        'status' => 'string',
    ];

    /**
     * Get all valid status values.
     */
    public static function getValidStatuses(): array
    {
        return [
            self::STATUS_DRAFT,
            self::STATUS_SCHEDULED,
            self::STATUS_ACTIVE,
            self::STATUS_ENDED,
            self::STATUS_CANCELLED,
            self::STATUS_SETTLED,
        ];
    }

    /**
     * Get all valid type values.
     */
    public static function getValidTypes(): array
    {
        return [
            self::TYPE_ENGLISH,
            self::TYPE_DUTCH,
            self::TYPE_SEALED,
            self::TYPE_BUY_NOW,
        ];
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winner_id');
    }

    public function winningBid(): BelongsTo
    {
        return $this->belongsTo(AuctionBid::class, 'winning_bid_id');
    }

    public function bids(): HasMany
    {
        return $this->hasMany(AuctionBid::class)->orderByDesc('amount');
    }

    public function autoBids(): HasMany
    {
        return $this->hasMany(AuctionAutoBid::class);
    }

    public function winnerRecord(): HasOne
    {
        return $this->hasOne(AuctionWinner::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(AuctionNotification::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeEnded($query)
    {
        return $query->where('status', 'ended');
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true)->where('status', 'active');
    }

    public function scopeEndingSoon($query, int $hours = 24)
    {
        return $query->where('status', 'active')
            ->where('ends_at', '<=', now()->addHours($hours));
    }

    public function scopeForVendor($query, int $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    // Status checks
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isEnded(): bool
    {
        return $this->status === 'ended';
    }

    public function hasEnded(): bool
    {
        // First check if auction is active - only active auctions can "end"
        if ($this->status !== 'active') {
            return false;
        }

        return $this->ends_at->isPast();
    }

    public function isWinning(User $user): bool
    {
        return $this->winner_id === $user->id;
    }

    public function getMinimumBid(): float
    {
        if ($this->total_bids === 0) {
            return (float) $this->starting_price;
        }

        return (float) ($this->current_price + $this->bid_increment);
    }

    public function canBid(User $user): bool
    {
        if (! $this->isActive()) {
            return false;
        }

        if ($this->vendor_id === $user->vendor?->id) {
            return false;
        }

        return true;
    }

    public function incrementViewCount(): void
    {
        $this->increment('view_count');
    }
}
