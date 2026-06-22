<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    public function log(string $action, ?object $subject = null, array $metadata = []): void
    {
        ActivityLog::create([
            'actor_id' => Auth::id(),
            'action' => $action,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject?->id,
            'metadata' => $metadata,
        ]);
    }
}
