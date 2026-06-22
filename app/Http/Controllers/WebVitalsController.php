<?php

namespace App\Http\Controllers;

use App\Models\AnalyticsEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebVitalsController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'payload' => ['required', 'array'],
            'payload.fcp' => ['nullable', 'numeric'],
            'payload.lcp' => ['nullable', 'numeric'],
            'payload.cls' => ['nullable', 'numeric'],
            'payload.inp' => ['nullable', 'numeric'],
            'payload.ttfb' => ['nullable', 'numeric'],
            'payload.path' => ['nullable', 'string', 'max:255'],
        ]);

        AnalyticsEvent::create([
            'event_name' => 'web_vitals',
            'event_category' => 'performance',
            'route_name' => $request->route()?->getName(),
            'path' => $validated['payload']['path'] ?? $request->path(),
            'url' => $request->fullUrl(),
            'user_id' => optional($request->user())->id,
            'session_id' => $request->hasSession() ? $request->session()->getId() : null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referer' => $request->headers->get('referer'),
            'properties' => $validated['payload'],
            'occurred_at' => now(),
        ]);

        return response()->json(['tracked' => true]);
    }
}
