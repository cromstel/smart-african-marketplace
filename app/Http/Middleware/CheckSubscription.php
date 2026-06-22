<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    protected $exceptRoutes = [
        'subscriptions.index',
        'subscriptions.show',
        'subscriptions.checkout',
        'subscriptions.subscribe',
        'subscriptions.my',
        'subscriptions.cancel',
        'subscriptions.resume',
        'subscriptions.success',
        'profile.*',
        'logout',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();

        if ($user->isAdmin()) {
            return $next($request);
        }

        if ($this->shouldBypass($request)) {
            return $next($request);
        }

        $subscription = $user->effectiveSubscription();

        if (! $subscription) {
            return redirect()->route('subscriptions.index')
                ->with('warning', 'You need an active subscription to access this feature');
        }

        if ($subscription->isExpired()) {
            return redirect()->route('subscriptions.index')
                ->with('error', 'Your subscription has expired. Please renew to continue.');
        }

        if ($subscription->isPastDue()) {
            return redirect()->route('subscriptions.my')
                ->with('warning', 'Your subscription payment is past due. Please update your payment method.');
        }

        if ($subscription->isActive() || $subscription->onGracePeriod()) {
            return $next($request);
        }

        return redirect()->route('subscriptions.index')
            ->with('warning', 'You need an active subscription to access this feature');
    }

    protected function shouldBypass(Request $request): bool
    {
        foreach ($this->exceptRoutes as $pattern) {
            if ($request->routeIs($pattern)) {
                return true;
            }
        }

        return false;
    }
}
