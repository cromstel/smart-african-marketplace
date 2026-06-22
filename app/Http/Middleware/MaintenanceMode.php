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
        'login',
        'register',
        'admin/login',
        'forgot-password',
        'reset-password/*',
        'verify-email',
        'verify-email/*',
        'email/verification-notification',
        'confirm-password',
        'password',
        'logout',
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
        $allowedIps = config('maintenance.allowed_ips', []);

        if (empty($allowedIps)) {
            return false;
        }

        $clientIp = $request->getClientIp();

        return in_array($clientIp, $allowedIps);
    }

    /**
     * Respond with the maintenance mode page.
     */
    protected function respondInMaintenanceMode(Request $request): Response
    {
        // Set appropriate HTTP status code and headers
        $response = response()->view('errors.maintenance', [
            'message' => config('maintenance.message', 'Site is under maintenance'),
            'expected' => config('maintenance.expected', null),
        ], 503);

        // Add cache control headers
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Retry-After', config('maintenance.retry', 3600));

        return $response;
    }
}
