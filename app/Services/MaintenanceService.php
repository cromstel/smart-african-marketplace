<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class MaintenanceService
{
    private const CACHE_KEY = 'maintenance_mode';

    /**
     * Config keys this service persists, and the config/maintenance.php
     * default each falls back to when no Setting row exists yet.
     *
     * Everything here used to be read/written through the Config facade or
     * a 5-minute Cache::put() -- both process-local or TTL-bound, so every
     * write path (this service, the admin controller, and the
     * maintenance:enable/disable console commands) silently lost its
     * changes: a custom title/message never survived past the request or
     * CLI process that set it, and the boolean "enabled" flag itself
     * expired after 5 minutes, quietly taking a site out of maintenance
     * mode while work was still in progress. Setting has no TTL, so this
     * makes the config actually durable.
     */
    private const KEYS = ['title', 'message', 'retry', 'expected', 'allowed_ips', 'allowed_emails'];

    /**
     * Check if maintenance mode is currently active.
     */
    public function isActive(): bool
    {
        return Cache::remember(self::CACHE_KEY, 300, function () {
            return (bool) Setting::getValue('maintenance_enabled', config('maintenance.enabled', false));
        });
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
        Setting::set('maintenance_enabled', $enabled);
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * The full maintenance configuration, persisted values layered over the
     * config/maintenance.php defaults. This is what admin.maintenance.index
     * and the enforcing middleware should both read -- reading config()
     * directly, as both used to, only ever sees the static file, never
     * anything an admin has actually saved.
     */
    public function getConfig(): array
    {
        $config = [];

        foreach (self::KEYS as $key) {
            $config[$key] = Setting::getValue("maintenance_{$key}", config("maintenance.{$key}"));
        }

        return $config;
    }

    /**
     * Get the maintenance mode status with additional info.
     */
    public function getStatus(): array
    {
        $config = $this->getConfig();

        return [
            'active' => $this->isActive(),
            'message' => $config['message'],
            'allowed_ips' => $config['allowed_ips'],
            'allowed_emails' => $config['allowed_emails'],
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

        // Check allowed emails from persisted config
        $allowedEmails = Setting::getValue('maintenance_allowed_emails', config('maintenance.allowed_emails', []));
        if (! empty($allowedEmails) && isset($user->email) && in_array($user->email, $allowedEmails)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether a client IP is on the allowed list.
     */
    public function isIpAllowed(?string $ip): bool
    {
        $allowedIps = Setting::getValue('maintenance_allowed_ips', config('maintenance.allowed_ips', []));

        if (empty($allowedIps) || ! $ip) {
            return false;
        }

        return in_array($ip, $allowedIps);
    }

    /**
     * Clear the maintenance mode cache.
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Persist the maintenance mode configuration. Only keys in self::KEYS
     * are written -- anything else in $config (e.g. 'enabled', which has
     * its own dedicated setMaintenanceMode() path) is ignored rather than
     * silently creating a stray Setting row.
     */
    public function updateConfig(array $config): void
    {
        foreach (self::KEYS as $key) {
            if (array_key_exists($key, $config)) {
                Setting::set("maintenance_{$key}", $config[$key]);
            }
        }

        $this->clearCache();
    }
}
