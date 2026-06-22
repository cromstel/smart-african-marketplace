<?php

namespace App\Http\Middleware;

use App\Models\AnalyticsEvent;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackUserBehavior
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $this->shouldTrack($request, $response)) {
            return $response;
        }

        try {
            AnalyticsEvent::create([
                'event_name' => 'page_view',
                'event_category' => $this->resolveCategory($request),
                'route_name' => $request->route()?->getName(),
                'path' => $request->path(),
                'url' => $request->fullUrl(),
                'user_id' => optional($request->user())->id,
                'session_id' => $request->hasSession() ? $request->session()->getId() : null,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'referer' => $request->headers->get('referer'),
                'properties' => [
                    'method' => $request->method(),
                    'locale' => app()->getLocale(),
                    'is_authenticated' => $request->user() !== null,
                ],
                'occurred_at' => now(),
            ]);
        } catch (\Throwable $exception) {
            // No-op: analytics tracking should never block user flows
        }

        return $response;
    }

    private function shouldTrack(Request $request, Response $response): bool
    {
        if ($request->method() !== 'GET') {
            return false;
        }

        if ($response->getStatusCode() >= 400) {
            return false;
        }

        if ($request->expectsJson() || str_starts_with($request->path(), 'api/')) {
            return false;
        }

        if ($this->isAssetPath($request->path())) {
            return false;
        }

        return true;
    }

    private function isAssetPath(string $path): bool
    {
        return str_starts_with($path, 'build/')
            || str_starts_with($path, 'storage/')
            || str_starts_with($path, 'images/')
            || str_ends_with($path, '.js')
            || str_ends_with($path, '.css')
            || str_ends_with($path, '.map')
            || str_ends_with($path, '.png')
            || str_ends_with($path, '.jpg')
            || str_ends_with($path, '.jpeg')
            || str_ends_with($path, '.svg')
            || str_ends_with($path, '.webp')
            || str_ends_with($path, '.ico');
    }

    private function resolveCategory(Request $request): string
    {
        $name = (string) $request->route()?->getName();

        if (str_starts_with($name, 'admin.')) {
            return 'admin';
        }
        if (str_starts_with($name, 'vendor.')) {
            return 'vendor';
        }
        if (str_starts_with($name, 'shop.')) {
            return 'shop';
        }
        if (str_starts_with($name, 'blog.')) {
            return 'blog';
        }

        return 'web';
    }
}
