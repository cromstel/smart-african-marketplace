<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class MaintenanceService
{
    private const CACHE_KEY = 'maintenance_mode';

    private const CONFIG_KEY = 'maintenance.enabled';

    /**
     * Check if maintenance mode is currently active.
     */
    public function isActive(): bool
    {
        // Check cache first for performance
        if (Cache::has(self::CACHE_KEY)) {
            return Cache::get(self::CACHE_KEY);
        }

        // Fall back to config value
        $value = Config::get(self::CONFIG_KEY, false);

        // Cache the value for subsequent requests
        Cache::put(self::CACHE_KEY, $value, 300); // Cache for 5 minutes

        return $value;
    }

    /**
     * Enable maintenance mode.
     */
    public function enable(): void
    {
        $this->setMaintenanceMode(true);
    }

    /**
     * Disable maintenance mode.
     */
    public function disable(): void
    {
        $this->setMaintenanceMode(false);
    }

    /**
     * Toggle maintenance mode.
     */
    public function toggle(): bool
    {
        $newState = ! $this->isActive();
        $this->setMaintenanceMode($newState);

        return $newState;
    }

    /**
     * Set maintenance mode to a specific state.
     */
    private function setMaintenanceMode(bool $enabled): void
    {
        // Update config
        Config::set(self::CONFIG_KEY, $enabled);

        // Update cache
        Cache::put(self::CACHE_KEY, $enabled, 300);

        // If enabling, also set Laravel's built-in maintenance mode
        if ($enabled) {
            $this->enableLaravelMaintenanceMode();
        } else {
            $this->disableLaravelMaintenanceMode();
        }
    }

    /**
     * Enable Laravel's built-in maintenance mode.
     */
    private function enableLaravelMaintenanceMode(): void
    {
        // The custom maintenance.php file in storage/framework handles
        // Laravel's built-in maintenance mode display.
        // This is used by public/index.php to show maintenance page
        // before the framework boots.

        // If you want to use Laravel's artisan down command instead,
        // use the console commands: php artisan maintenance:enable
        // which handles both custom and Laravel maintenance mode.
    }

    /**
     * Disable Laravel's built-in maintenance mode.
     */
    private function disableLaravelMaintenanceMode(): void
    {
        // The custom maintenance mode uses config/cache.
        // To disable Laravel's built-in maintenance mode,
        // use: php artisan maintenance:disable
    }

    /**
     * Get the maintenance mode status with additional info.
     */
    public function getStatus(): array
    {
        return [
            'active' => $this->isActive(),
            'message' => Config::get('maintenance.message', 'Site is under maintenance'),
            'allowed_ips' => Config::get('maintenance.allowed_ips', []),
            'allowed_emails' => Config::get('maintenance.allowed_emails', []),
        ];
    }

    /**
     * Check if a user can bypass maintenance mode.
     */
    public function canBypass($user): bool
    {
        if (! $user) {
            return false;
        }

        // Check role-based access using Spatie roles
        if (method_exists($user, 'hasRole')) {
            if ($user->hasRole('admin') || $user->hasRole('super_admin')) {
                return true;
            }
        }

        // Check if user has is_admin attribute
        if (isset($user->is_admin) && $user->is_admin === true) {
            return true;
        }

        // Check if user has isAdmin attribute
        if (isset($user->isAdmin) && $user->isAdmin === true) {
            return true;
        }

        // Check allowed emails from config
        $allowedEmails = Config::get('maintenance.allowed_emails', []);
        if (! empty($allowedEmails) && isset($user->email) && in_array($user->email, $allowedEmails)) {
            return true;
        }

        return false;
    }

    /**
     * Clear the maintenance mode cache.
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Update the maintenance mode configuration.
     */
    public function updateConfig(array $config): void
    {
        // Update each config value
        foreach ($config as $key => $value) {
            Config::set("maintenance.{$key}", $value);
        }

        // Clear cache so next request gets fresh config
        $this->clearCache();
    }
}
