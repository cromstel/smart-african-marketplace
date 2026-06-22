<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureModuleAccess
{
    public function handle(Request $request, Closure $next, string $moduleKey): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403, 'Unauthorized.');
        }

        if ($user->isAdmin()) {
            return $next($request);
        }

        if ($user->hasModuleAccess($moduleKey)) {
            return $next($request);
        }

        $payload = [
            'message' => 'Your current subscription does not include access to this module. Please upgrade to continue.',
            'module_key' => $moduleKey,
            'upgrade_url' => route('vendor.subscription.upgrade'),
        ];

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json($payload, 403);
        }

        return redirect()->route('vendor.subscription.upgrade')->with('error', $payload['message']);
    }
}
