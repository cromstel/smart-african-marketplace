<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

/**
 * Legacy Setting Model - for backward compatibility with existing settings table
 *
 * For new configuration management, use ConfigurationSetting model instead.
 *
 * @property string $key
 * @property string $value
 * @property string $type
 */
class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
    ];

    /**
     * Sensitive key patterns that should be encrypted.
     */
    protected static array $sensitivePatterns = [
        '*_secret*',
        '*_password*',
        '*_key',
        '*_token*',
        '*_api_key*',
        '*_private*',
    ];

    /**
     * Get a setting value with type casting.
     *
     * @deprecated Use getValue() instead to avoid conflicts with Eloquent query builder.
     */
    public static function get(string $key, $default = null)
    {
        return static::getValue($key, $default);
    }

    /**
     * Get a setting value with type casting.
     */
    public static function getValue(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();

        if (! $setting) {
            return $default;
        }

        return match ($setting->type) {
            'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $setting->value,
            'float' => (float) $setting->value,
            'array' => json_decode($setting->value, true),
            default => $setting->value,
        };
    }

    /**
     * Get a setting value and decrypt if needed.
     */
    public static function getDecrypted(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();

        if (! $setting) {
            return $default;
        }

        $value = match ($setting->type) {
            'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $setting->value,
            'float' => (float) $setting->value,
            'array' => json_decode($setting->value, true),
            default => $setting->value,
        };

        // Check if value looks encrypted and try to decrypt
        if (is_string($value) && static::isEncrypted($value)) {
            try {
                return Crypt::decryptString($value);
            } catch (\Exception $e) {
                Log::warning("Failed to decrypt setting: {$key}", [
                    'error' => $e->getMessage(),
                ]);

                return $value;
            }
        }

        return $value;
    }

    /**
     * Set a setting value with automatic type casting.
     */
    public static function set(string $key, $value, string $type = 'string'): void
    {
        if (is_array($value)) {
            $value = json_encode($value);
            $type = 'array';
        } elseif (is_bool($value)) {
            $value = $value ? '1' : '0';
            $type = 'boolean';
        } elseif (is_int($value)) {
            $type = 'integer';
        } elseif (is_float($value)) {
            $type = 'float';
        }

        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'type' => $type]
        );
    }

    /**
     * Set a sensitive value that will be encrypted.
     */
    public static function setSecure(string $key, string $value, string $type = 'string'): void
    {
        try {
            $encryptedValue = Crypt::encryptString($value);
            static::updateOrCreate(
                ['key' => $key],
                ['value' => $encryptedValue, 'type' => $type]
            );
        } catch (\Exception $e) {
            Log::error("Failed to encrypt setting: {$key}", [
                'error' => $e->getMessage(),
            ]);
            // Fall back to plain storage
            static::set($key, $value, $type);
        }
    }

    /**
     * Check if a value appears to be encrypted.
     */
    public static function isEncrypted(string $value): bool
    {
        if (empty($value)) {
            return false;
        }

        // Laravel encrypted strings start with "eyJ" (base64 encoded JSON)
        if (str_starts_with($value, 'eyJ')) {
            try {
                Crypt::decryptString($value);

                return true;
            } catch (\Exception $e) {
                return false;
            }
        }

        return false;
    }

    /**
     * Determine if a key should be encrypted based on patterns.
     */
    public static function shouldEncrypt(string $key): bool
    {
        foreach (self::$sensitivePatterns as $pattern) {
            $regex = str_replace('*', '.*', $pattern);
            if (preg_match("/^{$regex}$/i", $key)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get setting with validation rules.
     */
    public static function getWithValidation(string $key, array $validationRules, $default = null)
    {
        $setting = static::where('key', $key)->first();

        if (! $setting) {
            return $default;
        }

        // Apply validation if rules provided
        if (! empty($validationRules)) {
            $validator = \Validator::make(
                [$key => $setting->value],
                [$key => $validationRules]
            );

            if ($validator->fails()) {
                Log::warning("Setting validation failed: {$key}", [
                    'errors' => $validator->errors()->toArray(),
                ]);

                return $default;
            }
        }

        return match ($setting->type) {
            'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $setting->value,
            'float' => (float) $setting->value,
            'array' => json_decode($setting->value, true),
            default => $setting->value,
        };
    }

    /**
     * Get multiple settings at once.
     *
     * @return array<string, mixed>
     */
    public static function getMany(array $keys, array $defaults = []): array
    {
        /** @var Collection<int, Setting> $settings */
        $settings = static::query()->whereIn('key', $keys)->get()->keyBy('key');

        $result = [];
        foreach ($keys as $key) {
            $default = $defaults[$key] ?? null;
            $setting = $settings[$key] ?? null;

            if (! $setting) {
                $result[$key] = $default;

                continue;
            }

            $result[$key] = match ($setting->type) {
                'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
                'integer' => (int) $setting->value,
                'float' => (float) $setting->value,
                'array' => json_decode($setting->value, true),
                default => $setting->value,
            };
        }

        return $result;
    }

    /**
     * Check if a setting exists.
     */
    public static function has(string $key): bool
    {
        return static::where('key', $key)->exists();
    }

    /**
     * Delete a setting.
     */
    public static function remove(string $key): bool
    {
        return static::where('key', $key)->delete() > 0;
    }

    /**
     * Get all settings as key-value array.
     */
    public static function allAsArray(): array
    {
        return static::query()->get()->keyBy('key')->map(function ($setting) {
            return match ($setting->type) {
                'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
                'integer' => (int) $setting->value,
                'float' => (float) $setting->value,
                'array' => json_decode($setting->value, true),
                default => $setting->value,
            };
        })->toArray();
    }

    /**
     * Get all settings (legacy accessor for backward compatibility).
     */
    public static function getSettings($key, $default = null)
    {
        return static::getValue($key, $default);
    }
}
