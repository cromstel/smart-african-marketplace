<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SubscriptionPlanController extends Controller
{
    public function index()
    {
        $plans = SubscriptionPlan::withCount('subscriptions')
            ->orderBy('sort_order')
            ->get();

        return view('admin.subscriptions.plans.index', compact('plans'));
    }

    public function create()
    {
        $modulesCatalog = collect(config('modules.catalog', []))
            ->merge(config('modules.implemented_route_modules', []))
            ->values();

        return view('admin.subscriptions.plans.create', compact('modulesCatalog'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'billing_cycle' => 'required|in:monthly,yearly,lifetime',
            'trial_days' => 'required|integer|min:0',
            'features' => 'nullable|array',
            'feature_keys' => 'nullable|array',
            'max_products' => 'required|integer|min:-1',
            'max_orders' => 'required|integer|min:-1',
            'commission_rate' => 'required|numeric|min:0|max:100',
            'is_popular' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $validated['features'] = $request->input('features', []);
        $validated['feature_keys'] = $request->input('feature_keys', []);

        SubscriptionPlan::create($validated);

        return redirect()
            ->route('admin.subscriptions.plans.index')
            ->with('success', 'Subscription plan created successfully');
    }

    public function edit(SubscriptionPlan $plan)
    {
        $modulesCatalog = collect(config('modules.catalog', []))
            ->merge(config('modules.implemented_route_modules', []))
            ->values();

        return view('admin.subscriptions.plans.edit', compact('plan', 'modulesCatalog'));
    }

    public function update(Request $request, SubscriptionPlan $plan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'billing_cycle' => 'required|in:monthly,yearly,lifetime',
            'trial_days' => 'required|integer|min:0',
            'features' => 'nullable|array',
            'feature_keys' => 'nullable|array',
            'max_products' => 'required|integer|min:-1',
            'max_orders' => 'required|integer|min:-1',
            'commission_rate' => 'required|numeric|min:0|max:100',
            'is_popular' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $validated['features'] = $request->input('features', []);
        $validated['feature_keys'] = $request->input('feature_keys', []);

        $plan->update($validated);

        return redirect()
            ->route('admin.subscriptions.plans.index')
            ->with('success', 'Subscription plan updated successfully');
    }

    public function destroy(SubscriptionPlan $plan)
    {
        $activeSubscriptions = $plan->subscriptions()->whereIn('status', ['active', 'trialing'])->count();

        if ($activeSubscriptions > 0) {
            return redirect()
                ->route('admin.subscriptions.plans.index')
                ->with('error', "Cannot delete plan with {$activeSubscriptions} active subscriptions");
        }

        $plan->delete();

        return redirect()
            ->route('admin.subscriptions.plans.index')
            ->with('success', 'Subscription plan deleted successfully');
    }

    public function toggle(SubscriptionPlan $plan)
    {
        $plan->update(['is_active' => ! $plan->is_active]);

        return redirect()
            ->route('admin.subscriptions.plans.index')
            ->with('success', 'Plan status updated successfully');
    }

    public function subscribers()
    {
        $subscriptions = UserSubscription::with(['user', 'plan'])
            ->latest()
            ->paginate(20);

        $revenue = 0;
        if (Schema::hasTable('subscription_payments')) {
            $revenue = DB::table('subscription_payments')
                ->where('status', 'paid')
                ->sum('amount');
        }

        $stats = [
            'total' => UserSubscription::count(),
            'active' => UserSubscription::active()->count(),
            'trialing' => UserSubscription::where('status', 'trialing')->count(),
            'canceled' => UserSubscription::where('status', 'canceled')->count(),
            'revenue' => $revenue,
        ];

        return view('admin.subscriptions.subscribers', compact('subscriptions', 'stats'));
    }
}
