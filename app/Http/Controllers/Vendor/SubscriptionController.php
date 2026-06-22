<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\VendorSubscription;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'vendor']);
    }

    public function index()
    {
        $user = auth()->user();
        $subscription = $user->effectiveSubscription();

        if (! $subscription) {
            return redirect()->route('subscriptions.index')
                ->with('warning', 'No active subscription found. Please choose a plan.');
        }

        return view('vendor.subscription.index', compact('subscription'));
    }

    public function upgrade()
    {
        $user = auth()->user();
        $subscription = $user->effectiveSubscription();

        $plans = SubscriptionPlan::query()
            ->active()
            ->where('slug', '!=', 'free-trial')
            ->orderBy('sort_order')
            ->orderBy('price')
            ->get();

        return view('vendor.subscription.upgrade', compact('subscription', 'plans'));
    }

    public function processUpgrade(Request $request)
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id',
        ]);

        $user = auth()->user();
        $plan = SubscriptionPlan::findOrFail($validated['plan_id']);
        $subscription = $user->effectiveSubscription();

        if (! $subscription) {
            return redirect()->route('subscriptions.index')
                ->with('error', 'No subscription found. Please choose a plan first.');
        }

        $subscription->update([
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'current_period_start' => now(),
            'current_period_end' => now()->addMonth(),
            'trial_ends_at' => null,
            'cancel_at_period_end' => false,
            'canceled_at' => null,
        ]);

        if ($user->vendor) {
            VendorSubscription::updateOrCreate(
                ['vendor_id' => $user->vendor->id],
                [
                    'plan_name' => $plan->name,
                    'plan_price' => $plan->price,
                    'status' => 'active',
                    'current_period_start' => now(),
                    'current_period_end' => now()->addMonth(),
                    'trial_ends_at' => null,
                    'notification_sent' => false,
                ]
            );
        }

        return redirect()->route('vendor.subscription.index')
            ->with('success', "Successfully upgraded to {$plan->name} plan!");
    }
}
