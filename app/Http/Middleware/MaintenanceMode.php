<?php

namespace App\Http\Middleware;

use App\Services\MaintenanceService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MaintenanceMode
{
    /**
     * The maintenance service instance.
     */
    protected MaintenanceService $maintenanceService;

    /**
     * Paths that should be excluded from maintenance mode.
     */
    protected array $except = [
        'admin/*',
        'vendor/*',
        'api/*',
        '_debugbar/*',
        'telescope/*',
        'auth/login',
        'auth/register',
        'auth/admin/login',
        'auth/forgot-password',
        'auth/reset-password/*',
        'auth/verify-email',
        'auth/verify-email/*',
        'auth/email/verification-notification',
        'auth/confirm-password',
        'password',
        'auth/logout',
    ];

    /**
     * Create a new middleware instance.
     */
    public function __construct(MaintenanceService $maintenanceService)
    {
        $this->maintenanceService = $maintenanceService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // If maintenance mode is not active, allow the request
        if (! $this->maintenanceService->isActive()) {
            return $next($request);
        }

        // Check if the current user can bypass maintenance mode
        if ($this->canBypass($request)) {
            return $next($request);
        }

        // Check if the path is excepted
        if ($this->isExcepted($request)) {
            return $next($request);
        }

        // Check IP whitelist
        if ($this->isIpAllowed($request)) {
            return $next($request);
        }

        // Return maintenance page response
        return $this->respondInMaintenanceMode($request);
    }

    /**
     * Determine if the request has a user that can bypass maintenance mode.
     */
    protected function canBypass(Request $request): bool
    {
        $user = $request->user();

        return $this->maintenanceService->canBypass($user);
    }

    /**
     * Determine if the request path is excepted from maintenance mode.
     */
    protected function isExcepted(Request $request): bool
    {
        foreach ($this->except as $pattern) {
            if ($request->is($pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if the request IP is allowed.
     */
    protected function isIpAllowed(Request $request): bool
    {
        return $this->maintenanceService->isIpAllowed($request->getClientIp());
    }

    /**
     * Respond with the maintenance mode page.
     */
    protected function respondInMaintenanceMode(Request $request): Response
    {
        // Regression: this read config('maintenance.*') directly, which is
        // only ever the static file default -- an admin's saved
        // title/message/expected/retry never reached the page real
        // visitors see, even before the persistence fix, because this
        // middleware never consulted MaintenanceService/Setting at all.
        $config = $this->maintenanceService->getConfig();

        $response = response()->view('errors.maintenance', [
            'title' => $config['title'],
            'message' => $config['message'],
            'expected' => $config['expected'],
        ], 503);

        // Add cache control headers
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Retry-After', $config['retry']);

        return $response;
    }
}
