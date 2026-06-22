<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'vendor_category_id',
        'business_name',
        'slug',
        'business_email',
        'business_phone',
        'business_address',
        'latitude',
        'longitude',
        'city',
        'state',
        'country',
        'postal_code',
        'business_registration_number',
        'tax_id',
        'description',
        'logo',
        'profile_image',
        'stripe_account_id',
        'paystack_recipient_code',
        'bank_account_name',
        'bank_account_number',
        'bank_code',
        'bank_name',
        'momo_provider',
        'momo_number',
        'commission_rate',
        'status',
        'is_active',
        'approved_at',
        'custom_domain',
        'custom_domain_verified_at',
    ];

    protected $casts = [
        'commission_rate' => 'decimal:2',
        'is_active' => 'boolean',
        'approved_at' => 'datetime',
        'custom_domain_verified_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(VendorCategory::class, 'vendor_category_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function subscription()
    {
        return $this->hasOne(VendorSubscription::class);
    }

    public function theme()
    {
        return $this->hasOne(VendorTheme::class);
    }

    public function followers(): HasMany
    {
        return $this->hasMany(VendorFollower::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function balance()
    {
        return $this->hasOne(VendorBalance::class);
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(VendorLedger::class);
    }

    public function verification()
    {
        return $this->hasOne(VendorVerification::class);
    }

    public function payouts(): HasMany
    {
        return $this->hasMany(Payout::class);
    }

    public function availabilities(): HasMany
    {
        return $this->hasMany(VendorAvailability::class);
    }

    public function seoMeta(): MorphOne
    {
        return $this->morphOne(SeoMeta::class, 'model');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function storefrontUrl(array $query = []): string
    {
        $scheme = (string) config('vendor-store.scheme', 'https');
        $baseDomain = trim((string) config('vendor-store.base_domain', ''), '.');

        $url = url('/vendors/'.$this->slug);

        if ($this->hasVerifiedCustomDomainAccess()) {
            $url = $scheme.'://'.$this->custom_domain;
        } elseif ($baseDomain !== '' && $baseDomain !== 'localhost') {
            $url = $scheme.'://'.$this->slug.'.'.$baseDomain;
        }

        if ($query !== []) {
            $url .= '?'.http_build_query($query);
        }

        return $url;
    }

    public function profileImageUrl(): ?string
    {
        if (! $this->profile_image) {
            return null;
        }

        return asset('storage/'.ltrim($this->profile_image, '/'));
    }

    public function hasVerifiedCustomDomainAccess(): bool
    {
        return ! empty($this->custom_domain)
            && $this->custom_domain_verified_at !== null
            && $this->user?->effectiveSubscription()?->plan?->slug === 'enterprise'
            && $this->user?->effectiveSubscription()?->isActive();
    }

    public static function resolveStorefrontHost(?string $host): ?self
    {
        if (! is_string($host) || trim($host) === '') {
            return null;
        }

        $host = strtolower(trim(preg_replace('/:\d+$/', '', $host)));
        $baseDomain = strtolower(trim((string) config('vendor-store.base_domain', ''), '.'));
        $appHost = strtolower((string) parse_url(config('app.url'), PHP_URL_HOST));

        if ($host === '' || $host === $appHost || ($baseDomain !== '' && $host === $baseDomain)) {
            return null;
        }

        $hasCustomDomainColumns = Schema::hasColumn('vendors', 'custom_domain')
            && Schema::hasColumn('vendors', 'custom_domain_verified_at');

        if ($hasCustomDomainColumns) {
            $custom = self::query()
                ->whereRaw('LOWER(custom_domain) = ?', [$host])
                ->whereNotNull('custom_domain_verified_at')
                ->where('is_active', true)
                ->where('status', 'approved')
                ->with('user.subscription.plan')
                ->first();

            if ($custom && $custom->hasVerifiedCustomDomainAccess()) {
                return $custom;
            }
        }

        if ($baseDomain === '' || ! Str::endsWith($host, '.'.$baseDomain)) {
            return null;
        }

        $subdomain = Str::before($host, '.'.$baseDomain);
        if ($subdomain === '' || str_contains($subdomain, '.')) {
            return null;
        }

        if (in_array($subdomain, config('vendor-store.reserved_subdomains', []), true)) {
            return null;
        }

        return self::query()
            ->where('slug', $subdomain)
            ->where('is_active', true)
            ->where('status', 'approved')
            ->first();
    }
}
