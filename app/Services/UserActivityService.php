<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserActivityService
{
    /**
     * Log a user activity.
     */
    public function log(string $action, ?User $subject = null, array $metadata = []): ActivityLog
    {
        return ActivityLog::create([
            'actor_id' => Auth::id(),
            'action' => $action,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject?->id,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Log user created activity.
     */
    public function logUserCreated(User $user): ActivityLog
    {
        return $this->log('user_created', $user, [
            'user_email' => $user->email,
            'user_role' => $user->role,
        ]);
    }

    /**
     * Log user updated activity.
     */
    public function logUserUpdated(User $user, array $changes): ActivityLog
    {
        return $this->log('user_updated', $user, [
            'changes' => $changes,
        ]);
    }

    /**
     * Log user deleted/deactivated activity.
     */
    public function logUserDeactivated(User $user): ActivityLog
    {
        return $this->log('user_deactivated', $user, [
            'user_email' => $user->email,
        ]);
    }

    /**
     * Log user activated activity.
     */
    public function logUserActivated(User $user): ActivityLog
    {
        return $this->log('user_activated', $user, [
            'user_email' => $user->email,
        ]);
    }

    /**
     * Log user role changed activity.
     */
    public function logRoleChanged(User $user, string $oldRole, string $newRole): ActivityLog
    {
        return $this->log('user_role_changed', $user, [
            'old_role' => $oldRole,
            'new_role' => $newRole,
        ]);
    }

    /**
     * Log password reset sent activity.
     */
    public function logPasswordResetSent(User $user): ActivityLog
    {
        return $this->log('password_reset_sent', $user, [
            'user_email' => $user->email,
        ]);
    }

    /**
     * Log two-factor enabled activity.
     */
    public function logTwoFactorEnabled(User $user): ActivityLog
    {
        return $this->log('two_factor_enabled', $user, [
            'user_email' => $user->email,
        ]);
    }

    /**
     * Log two-factor disabled activity.
     */
    public function logTwoFactorDisabled(User $user): ActivityLog
    {
        return $this->log('two_factor_disabled', $user, [
            'user_email' => $user->email,
        ]);
    }

    /**
     * Log login activity.
     */
    public function logLogin(User $user, ?string $ipAddress = null): ActivityLog
    {
        return $this->log('user_login', $user, [
            'ip_address' => $ipAddress,
        ]);
    }

    /**
     * Log logout activity.
     */
    public function logLogout(User $user): ActivityLog
    {
        return $this->log('user_logout', $user);
    }

    /**
     * Log failed login attempt.
     */
    public function logFailedLogin(string $email, ?string $ipAddress = null): ActivityLog
    {
        return ActivityLog::create([
            'actor_id' => null,
            'action' => 'failed_login',
            'subject_type' => User::class,
            'subject_id' => null,
            'metadata' => [
                'email' => $email,
                'ip_address' => $ipAddress,
            ],
        ]);
    }

    /**
     * Get activity logs for a user.
     */
    public function getUserActivity(User $user, int $limit = 50)
    {
        return ActivityLog::where('subject_type', User::class)
            ->where('subject_id', $user->id)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get all activity logs with optional filtering.
     */
    public function getActivityLogs(array $filters = [], int $perPage = 20)
    {
        $query = ActivityLog::query()->orderByDesc('created_at');

        if (! empty($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        if (! empty($filters['actor_id'])) {
            $query->where('actor_id', $filters['actor_id']);
        }

        if (! empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        return $query->paginate($perPage);
    }
}
