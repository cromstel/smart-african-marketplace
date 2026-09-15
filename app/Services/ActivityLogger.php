<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    /**
     * $actorId lets a caller running outside the authenticated web request
     * (e.g. a queued job) record who actually initiated the action, since
     * Auth::id() resolves to null in a queue worker's CLI context. Every
     * existing call site omits it and keeps today's Auth::id() behavior.
     */
    public function log(string $action, ?object $subject = null, array $metadata = [], ?int $actorId = null): void
    {
        ActivityLog::create([
            'actor_id' => $actorId ?? Auth::id(),
            'action' => $action,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject?->id,
            'metadata' => $metadata,
        ]);
    }
}
