<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscriptionLimits
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, ?string $limitType = null): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isVendor()) {
            return $next($request);
        }

        $subscription = $user->effectiveSubscription();

        if (! $subscription || ! $subscription->isActive()) {
            return redirect()
                ->route('subscriptions.index')
                ->with('error', 'You need an active subscription to access this feature.');
        }

        if ($limitType === 'products') {
            return $this->checkProductLimit($user, $subscription, $next, $request);
        }

        if ($limitType === 'orders') {
            return $this->checkOrderLimit($user, $subscription, $next, $request);
        }

        return $next($request);
    }

    protected function checkProductLimit($user, $subscription, $next, $request): Response
    {
        if ($subscription->plan->hasUnlimitedProducts()) {
            return $next($request);
        }

        $currentProductCount = $user->products()->count();

        if ($request->isMethod('post') || $request->isMethod('put')) {
            $productId = $request->route('product');

            if (! $productId && $subscription->hasReachedProductLimit($currentProductCount)) {
                return redirect()
                    ->back()
                    ->with('error', "You've reached your plan's limit of {$subscription->plan->max_products} products. Upgrade to add more.");
            }
        }

        return $next($request);
    }

    protected function checkOrderLimit($user, $subscription, $next, $request): Response
    {
        if ($subscription->plan->hasUnlimitedOrders()) {
            return $next($request);
        }

        $currentMonthOrders = $user->vendorOrders()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        if ($subscription->hasReachedOrderLimit($currentMonthOrders)) {
            return redirect()
                ->route('subscriptions.my')
                ->with('warning', "You've reached your plan's limit of {$subscription->plan->max_orders} orders this month. Upgrade for more capacity.");
        }

        return $next($request);
    }
}
