<?php

namespace App\Services;

use App\Models\ConfigurationAudit;
use App\Models\ConfigurationSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ConfigurationService
{
    private const CACHE_PREFIX = 'config.';

    private const CACHE_TTL = 3600; // 1 hour

    private const ALL_CACHE_KEY = 'config.all';

    /**
     * Get a configuration value with caching and env fallback.
     */
    public static function get(string $key, $default = null)
    {
        // Check cache first
        $cacheKey = self::CACHE_PREFIX.$key;

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        // 1. First check database configuration
        $setting = ConfigurationSetting::where('key', $key)
            ->where('is_active', true)
            ->first();

        if ($setting) {
            $value = $setting->getCastValue();

            // Cache the value
            Cache::put($cacheKey, $value, self::CACHE_TTL);

            return $value ?? $default;
        }

        // 2. Fallback to the environment.
        //
        // Read through the config snapshot rather than env(): the key is
        // computed at runtime, so it cannot be declared in a config file, and
        // after `php artisan config:cache` env() returns null for anything that
        // lived only in .env. This fallback therefore worked in development and
        // silently stopped working in production. See config/env_snapshot.php.
        $envKey = strtoupper(str_replace('.', '_', $key));
        $envValue = config('env_snapshot.'.$envKey);

        if ($envValue !== null) {
            // Cache the env value
            Cache::put($cacheKey, $envValue, self::CACHE_TTL);

            return $envValue;
        }

        // 3. Return default value
        return $default;
    }

    /**
     * Get a boolean configuration value.
     */
    public static function getBool(string $key, bool $default = false): bool
    {
        $value = self::get($key, $default);

        if (is_bool($value)) {
            return $value;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $default;
    }

    /**
     * Get an integer configuration value.
     */
    public static function getInt(string $key, int $default = 0): int
    {
        $value = self::get($key, $default);

        if (is_int($value)) {
            return $value;
        }

        return (int) $value;
    }

    /**
     * Get an array configuration value.
     */
    public static function getArray(string $key, array $default = []): array
    {
        $value = self::get($key);

        if (is_array($value)) {
            return $value;
        }

        if ($value === null || $value === '') {
            return $default;
        }

        // Try to decode as JSON
        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : $default;
    }

    /**
     * Check if a configuration key exists.
     */
    public static function has(string $key): bool
    {
        // Check database
        if (ConfigurationSetting::where('key', $key)->exists()) {
            return true;
        }

        // Check the environment (via the snapshot -- see get() above for why).
        $envKey = strtoupper(str_replace('.', '_', $key));

        return config('env_snapshot.'.$envKey) !== null;
    }

    /**
     * Set a configuration value.
     */
    public static function set(string $key, $value, ?string $type = null): ConfigurationSetting
    {
        $setting = ConfigurationSetting::where('key', $key)->first();

        if (! $setting) {
            throw new \RuntimeException("Configuration setting '{$key}' does not exist. Create it first.");
        }

        // Determine type if not provided
        if ($type === null) {
            $type = $setting->type ?? 'text';
        }

        // Handle type casting
        $castedValue = self::castValue($value, $type);

        // Get old value for audit
        $oldValue = $setting->getDecryptedValue();

        // Set new value (will handle encryption via accessor)
        $setting->value = $castedValue;
        $setting->save();

        // Clear cache
        self::forget($key);

        // Record audit log entry
        self::createAuditEntry($key, $oldValue, $castedValue);

        // Log the change
        if (app()->bound('log')) {
            Log::info('Configuration updated', [
                'key' => $key,
                'type' => $type,
            ]);
        }

        return $setting;
    }

    /**
     * Create an audit log entry for configuration change.
     */
    private static function createAuditEntry(string $key, ?string $oldValue, ?string $newValue): void
    {
        try {
            ConfigurationAudit::create([
                'configuration_setting_id' => ConfigurationSetting::where('key', $key)->first()?->id,
                'action' => 'updated',
                'old_value' => $oldValue,
                'new_value' => $newValue,
                'changed_by' => auth()->id() ?? null,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to create configuration audit log: '.$e->getMessage());
        }
    }

    /**
     * Set multiple configuration values at once.
     */
    public static function setMany(array $settings): void
    {
        foreach ($settings as $key => $value) {
            try {
                self::set($key, $value);
            } catch (\Exception $e) {
                Log::warning("Failed to set configuration '{$key}': ".$e->getMessage());
            }
        }
    }

    /**
     * Clear configuration cache.
     */
    public static function forget(string $key): void
    {
        Cache::forget(self::CACHE_PREFIX.$key);
        Cache::forget(self::ALL_CACHE_KEY);
    }

    /**
     * Clear all configuration cache.
     */
    public static function flush(): void
    {
        Cache::flush();
    }

    /**
     * Get all configurations (cached).
     */
    public static function all(): array
    {
        if (Cache::has(self::ALL_CACHE_KEY)) {
            return Cache::get(self::ALL_CACHE_KEY);
        }

        $settings = ConfigurationSetting::active()
            ->with('category')
            ->get()
            ->mapWithKeys(function ($setting) {
                return [$setting->key => $setting->getCastValue()];
            })
            ->toArray();

        Cache::put(self::ALL_CACHE_KEY, $settings, self::CACHE_TTL);

        return $settings;
    }

    /**
     * Get configurations grouped by category.
     */
    public static function groupedByCategory(): array
    {
        $settings = ConfigurationSetting::active()
            ->with('category')
            ->orderBy('configuration_category_id')
            ->orderBy('sort_order')
            ->get();

        return $settings->groupBy('configuration_category_id')->toArray();
    }

    /**
     * Cast a value to the appropriate type.
     */
    private static function castValue($value, string $type)
    {
        if ($value === null || $value === '') {
            return null;
        }

        return match ($type) {
            'boolean' => $value ? 'true' : 'false',
            'integer' => (string) (int) $value,
            'float' => (string) (float) $value,
            'json', 'array' => is_array($value) ? json_encode($value) : $value,
            default => $value,
        };
    }

    /**
     * Get cache TTL in seconds.
     */
    public static function getCacheTtl(): int
    {
        return (int) config('settings.cache_ttl', self::CACHE_TTL);
    }

    /**
     * Set cache TTL.
     */
    public static function setCacheTtl(int $ttl): void
    {
        config(['settings.cache_ttl' => $ttl]);
    }
}
