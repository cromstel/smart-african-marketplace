<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConfigurationAudit extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'configuration_setting_id',
        'setting_key',
        'old_value',
        'new_value',
        'action',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Possible audit actions.
     */
    public const ACTION_CREATED = 'created';

    public const ACTION_UPDATED = 'updated';

    public const ACTION_DELETED = 'deleted';

    public const ACTION_RESTORED = 'restored';

    public const ACTION_EXPORTED = 'exported';

    public const ACTION_IMPORTED = 'imported';

    /**
     * Get the user who made the change.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get recent audit logs.
     */
    public function scopeRecent($query, int $limit = 50)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    /**
     * Scope to get logs for a specific setting key.
     */
    public function scopeForKey($query, string $key)
    {
        return $query->where('setting_key', $key);
    }

    /**
     * Scope to get logs by a specific user.
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Get masked old value for display (for security).
     */
    public function getMaskedOldValue(): ?string
    {
        return $this->maskValue($this->old_value);
    }

    /**
     * Get masked new value for display (for security).
     */
    public function getMaskedNewValue(): ?string
    {
        return $this->maskValue($this->new_value);
    }

    /**
     * Mask sensitive values in logs.
     */
    protected function maskValue(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        // Don't mask if already encrypted-looking
        if (str_starts_with($value, 'eyJ')) {
            return '[encrypted]';
        }

        // Mask long values
        if (strlen($value) > 8) {
            return substr($value, 0, 4).'****'.substr($value, -4);
        }

        return '****';
    }

    /**
     * Log a configuration change.
     */
    public static function log(
        string $key,
        ?string $oldValue,
        ?string $newValue,
        string $action,
        ?int $userId = null
    ): self {
        // Mask sensitive values before storing in database
        $maskedOldValue = self::shouldMask($key) ? self::maskValue($oldValue) : $oldValue;
        $maskedNewValue = self::shouldMask($key) ? self::maskValue($newValue) : $newValue;

        return self::create([
            'setting_key' => $key,
            'old_value' => $maskedOldValue,
            'new_value' => $maskedNewValue,
            'action' => $action,
            'user_id' => $userId,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Determine if a key should be masked based on sensitive patterns.
     */
    private static function shouldMask(string $key): bool
    {
        $sensitivePatterns = [
            '*_secret*',
            '*_password*',
            '*_key',
            '*_token*',
            '*_api_key*',
            '*_private*',
            '*_certificate*',
        ];

        foreach ($sensitivePatterns as $pattern) {
            $regex = str_replace('*', '.*', $pattern);
            if (preg_match("/^{$regex}$/i", $key)) {
                return true;
            }
        }

        return false;
    }
}
