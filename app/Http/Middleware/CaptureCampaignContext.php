<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CaptureCampaignContext
{
    public function handle(Request $request, \Closure $next): Response
    {
        $hasCampaignData = $request->hasAny(['utm_campaign', 'utm_source', 'utm_medium', 'cr']);
        if ($hasCampaignData) {
            session([
                'lead_context' => [
                    'utm_campaign' => $request->query('utm_campaign'),
                    'utm_source' => $request->query('utm_source'),
                    'utm_medium' => $request->query('utm_medium'),
                    'utm_term' => $request->query('utm_term'),
                    'utm_content' => $request->query('utm_content'),
                    'campaign_recipient_token' => $request->query('cr'),
                    'referrer' => substr((string) $request->headers->get('referer'), 0, 1900),
                    'ip_address' => $request->ip(),
                    'user_agent' => substr((string) $request->userAgent(), 0, 1900),
                    'session_id' => $request->session()->getId(),
                    'captured_at' => now()->toDateTimeString(),
                ],
            ]);
        }

        return $next($request);
    }
}
