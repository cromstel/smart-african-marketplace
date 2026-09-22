<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\VendorSubscription;
use App\Services\Payment\StripePaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    protected StripePaymentService $stripeService;

    public function __construct(StripePaymentService $stripeService)
    {
        $this->middleware(['auth', 'vendor']);
        $this->stripeService = $stripeService;
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

        // Regression: this previously just flipped subscription_plan_id and
        // reset current_period_end locally, with no call to any payment
        // gateway at all -- any vendor could switch straight to the most
        // expensive plan for free by submitting this form. A subscription
        // billed through Stripe must have its Stripe-side subscription
        // updated too (proration handled by Stripe itself via changePlan());
        // one with no payment provider on file (e.g. the free plan
        // auto-assigned on approval) must go through the real checkout flow
        // -- which collects payment -- before it can move to a paid plan.
        if ((float) $plan->price > 0) {
            if ($subscription->payment_provider === 'stripe' && $subscription->payment_provider_subscription_id) {
                // changePlan() calls the Stripe SDK outside its own
                // try/catch when Stripe isn't configured (throws before
                // reaching its internal error handling) -- guard here so a
                // gateway config change after signup degrades to a message
                // instead of a 500.
                try {
                    $changed = $this->stripeService->changePlan($subscription->payment_provider_subscription_id, $plan);
                } catch (\Throwable $e) {
                    Log::error('Stripe plan change threw during vendor self-service upgrade', [
                        'user_id' => $user->id,
                        'subscription_id' => $subscription->id,
                        'target_plan_id' => $plan->id,
                        'error' => $e->getMessage(),
                    ]);
                    $changed = false;
                }

                if (! $changed) {
                    return redirect()->back()
                        ->with('error', 'We could not update your billing for this plan. Please try again or contact support.');
                }
            } else {
                return redirect()->route('subscriptions.checkout', $plan)
                    ->with('info', 'This plan requires payment -- please complete checkout to switch to it.');
            }
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
