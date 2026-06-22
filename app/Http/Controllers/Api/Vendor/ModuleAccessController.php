<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ModuleAccessController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $subscription = $user->effectiveSubscription();

        if (! $subscription || ! $subscription->isActive()) {
            return response()->json([
                'message' => 'You need an active subscription to access module information.',
                'upgrade_url' => route('vendor.subscription.upgrade'),
            ], 403);
        }

        $catalog = collect(config('modules.catalog', []))
            ->merge(config('modules.implemented_route_modules', []))
            ->map(function (array $module) use ($user) {
                return [
                    'key' => $module['key'],
                    'name' => $module['name'],
                    'implemented' => (bool) ($module['implemented'] ?? false),
                    'granted' => $user->hasModuleAccess($module['key']),
                ];
            })
            ->values();

        $grantedKeys = $catalog
            ->where('granted', true)
            ->pluck('key')
            ->values();

        return response()->json([
            'plan_slug' => $subscription->plan?->slug,
            'status' => $subscription->status,
            'granted_keys' => $grantedKeys,
            'catalog' => $catalog,
        ]);
    }
}
