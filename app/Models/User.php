<?php

namespace App\Models;

use App\Services\Subscription\EffectiveSubscriptionResolver;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmailContract
{
    use HasApiTokens, HasFactory, MustVerifyEmail, Notifiable;

    protected $fillable = [
        'firstname',
        'lastname',
        'name',
        'email',
        'password',
        'role',
        'phone',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'latitude',
        'longitude',
        'is_active',
        'provider',
        'provider_id',
        'avatar',
        'loyalty_points_balance',
        'referral_code',
        'referral_earnings',
        'stripe_customer_id',
        'paystack_customer_code',
        'notification_preferences',
        'fcm_token',
        'two_factor_enabled',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'notification_preferences' => 'array',
        'two_factor_enabled' => 'boolean',
        'two_factor_recovery_codes' => 'array',
    ];

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isVendor(): bool
    {
        return $this->hasAnyRole(['vendor', 'salon_owner']);
    }

    public function isSalonOwner(): bool
    {
        return $this->hasRole('salon_owner');
    }

    public function isCustomer(): bool
    {
        return $this->hasRole('customer');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    public function hasRole(string $role): bool
    {
        // Check role column first (most common case - user has single role)
        if ($this->role === $role) {
            return true;
        }

        // Use cached roles if already loaded to avoid N+1
        if ($this->relationLoaded('roles')) {
            return $this->roles->contains(fn ($item) => $item->slug === $role);
        }

        // Fall back to database query
        return $this->roles()->where('slug', $role)->exists();
    }

    public function hasAnyRole(array $roles): bool
    {
        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }

        return false;
    }

    public function assignRoles(array $roles): void
    {
        $slugs = collect($roles)->filter()->unique()->values();

        if ($slugs->isEmpty()) {
            return;
        }

        $roleIds = Role::whereIn('slug', $slugs)->pluck('id');
        $this->roles()->sync($roleIds);
    }

    public function vendor(): HasOne
    {
        return $this->hasOne(Vendor::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function userNotifications(): HasMany
    {
        return $this->hasMany(UserNotification::class)->orderByDesc('created_at');
    }

    public function unreadUserNotifications(): HasMany
    {
        return $this->userNotifications()->where('is_read', false);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class);
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(UserSubscription::class)->latestOfMany();
    }

    public function hasActiveSubscription(): bool
    {
        return $this->subscription && $this->subscription->isActive();
    }

    public function onTrial(): bool
    {
        return $this->subscription && $this->subscription->onTrial();
    }

    public function subscribedToPlan(int $planId): bool
    {
        return $this->subscription && $this->subscription->subscription_plan_id === $planId && $this->subscription->isActive();
    }

    public function effectiveSubscription(): ?UserSubscription
    {
        return app(EffectiveSubscriptionResolver::class)->resolveFor($this);
    }

    public function hasModuleAccess(string $moduleKey): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $subscription = $this->effectiveSubscription();

        if (! $subscription || ! $subscription->isActive() || ! $subscription->plan) {
            return false;
        }

        return $subscription->plan->hasFeatureKey($moduleKey);
    }

    public function followers(): HasMany
    {
        return $this->hasMany(VendorFollower::class, 'vendor_id');
    }

    public function following(): HasMany
    {
        return $this->hasMany(VendorFollower::class, 'user_id');
    }

    public function isFollowing($vendorId): bool
    {
        return $this->following()->where('vendor_id', $vendorId)->exists();
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class);
    }

    /**
     * Products owned by this vendor, used by CheckSubscriptionLimits to
     * enforce a plan's product cap. Product.vendor_id references vendors.id,
     * not users.id, so this cannot be a plain hasMany(Product::class); it
     * returns a query builder rather than an Eloquent relation.
     */
    public function products()
    {
        return Product::where('vendor_id', $this->vendor?->id ?? 0);
    }

    /**
     * Orders containing at least one line item sold by this vendor, used by
     * CheckSubscriptionLimits to enforce a plan's monthly order cap. Order
     * carries no vendor_id column of its own -- membership on a (possibly
     * multi-vendor) order lives on order_items.vendor_id -- so this cannot
     * be a plain hasMany(Order::class, 'vendor_id'); it returns a query
     * builder rather than an Eloquent relation.
     */
    public function vendorOrders()
    {
        $vendorId = $this->vendor?->id;

        return Order::childOrders()->when(
            $vendorId,
            fn ($query) => $query->whereHas('items', fn ($q) => $q->where('vendor_id', $vendorId)),
            fn ($query) => $query->whereRaw('1 = 0')
        );
    }

    public function notificationPreference(string $key, bool $default = true): bool
    {
        $preferences = $this->notification_preferences ?? [];

        if (! is_array($preferences)) {
            return $default;
        }

        if (! array_key_exists($key, $preferences)) {
            return $default;
        }

        return (bool) $preferences[$key];
    }

    /**
     * Check if two-factor authentication is enabled.
     */
    public function isTwoFactorEnabled(): bool
    {
        return $this->two_factor_enabled === true;
    }

    /**
     * Generate a new two-factor authentication secret.
     */
    public function generateTwoFactorSecret(): string
    {
        $secret = random_bytes(32);
        $this->two_factor_secret = base64_encode($secret);

        return $this->two_factor_secret;
    }

    /**
     * Enable two-factor authentication for the user.
     */
    public function enableTwoFactor(string $secret): bool
    {
        $this->two_factor_enabled = true;
        $this->two_factor_secret = $secret;
        $this->two_factor_recovery_codes = $this->generateRecoveryCodes();

        return true;
    }

    /**
     * Disable two-factor authentication for the user.
     */
    public function disableTwoFactor(): bool
    {
        $this->two_factor_enabled = false;
        $this->two_factor_secret = null;
        $this->two_factor_recovery_codes = null;

        return true;
    }

    /**
     * Verify a two-factor authentication code (TOTP).
     */
    public function verifyTwoFactorCode(string $code): bool
    {
        if (! $this->two_factor_secret) {
            return false;
        }

        $secret = $this->two_factor_secret;
        $code = str_replace(' ', '', $code);

        // Use built-in hash_equals for timing attack prevention
        for ($i = 0; $i < 3; $i++) {
            $offset = $i * 4;
            $testCode = substr($code, $offset, 6);
            $expected = $this->getTotpCode($secret, $i);
            if (hash_equals($expected, $testCode)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate a TOTP code.
     */
    protected function getTotpCode(string $secret, int $offset = 0): string
    {
        $time = floor(time() / 30) + $offset;
        $timeHex = str_pad(dechex($time), 16, '0', STR_PAD_LEFT);
        $secretHex = bin2hex(base64_decode($secret));

        $hash = hash_hmac('sha1', $timeHex, $secretHex);
        $offset = hexdec(substr($hash, -1)) & 0xF;

        $truncatedHash = hexdec(substr($hash, $offset * 2, 8)) & 0x7FFFFFFF;
        $code = str_pad($truncatedHash % 1000000, 6, '0', STR_PAD_LEFT);

        return $code;
    }

    /**
     * Verify a two-factor recovery code.
     */
    public function verifyTwoFactorRecoveryCode(string $code): bool
    {
        if (! $this->two_factor_recovery_codes) {
            return false;
        }

        $codes = $this->two_factor_recovery_codes;
        $code = trim($code);

        // Use constant-time comparison to prevent timing attacks
        $found = false;
        foreach ($codes as $storedCode) {
            if (hash_equals($storedCode, $code)) {
                $found = true;
                break;
            }
        }

        if ($found) {
            // Remove the used recovery code
            $this->two_factor_recovery_codes = array_values(array_filter($codes, fn ($c) => $c !== $code));
            $this->save();

            return true;
        }

        return false;
    }

    /**
     * Get the two-factor authentication QR code URL (for manual entry).
     */
    public function getTwoFactorSecretForDisplay(): ?string
    {
        return $this->two_factor_secret;
    }

    /**
     * Get the QR code URL for 2FA setup.
     */
    public function get2faQrCodeUrl(): ?string
    {
        if (! $this->two_factor_secret) {
            return null;
        }

        // Generate QR code URL for authenticator apps
        $issuer = config('app.name', 'Smart African Market');
        $email = $this->email;

        return 'otpauth://totp/'.urlencode($issuer).':'.urlencode($email).'?secret='.$this->two_factor_secret.'&issuer='.urlencode($issuer);
    }

    /**
     * Generate recovery codes for two-factor authentication.
     */
    protected function generateRecoveryCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = strtoupper(bin2hex(random_bytes(4)));
        }

        return $codes;
    }

    /**
     * Regenerate recovery codes.
     */
    public function regenerateRecoveryCodes(): array
    {
        $this->two_factor_recovery_codes = $this->generateRecoveryCodes();
        $this->save();

        return $this->two_factor_recovery_codes;
    }
}
