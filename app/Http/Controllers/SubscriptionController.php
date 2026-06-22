<?php

namespace App\Http\Controllers;

use App\Mail\SubscriptionCreated;
use App\Models\SubscriptionPlan;
use App\Services\Payment\PaystackPaymentService;
use App\Services\Payment\StripePaymentService;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SubscriptionController extends Controller
{
    protected $subscriptionService;

    protected $stripeService;

    protected $paystackService;

    public function __construct(
        SubscriptionService $subscriptionService,
        StripePaymentService $stripeService,
        PaystackPaymentService $paystackService
    ) {
        $this->subscriptionService = $subscriptionService;
        $this->stripeService = $stripeService;
        $this->paystackService = $paystackService;
    }

    public function index()
    {
        $plans = SubscriptionPlan::active()
            ->where('slug', '!=', 'free-trial')
            ->orderBy('sort_order')
            ->orderBy('price')
            ->get();

        $userSubscription = Auth::check() ? Auth::user()->subscription : null;

        return view('subscriptions.index', compact('plans', 'userSubscription'));
    }

    public function show(SubscriptionPlan $plan)
    {
        if (! $plan->is_active) {
            abort(404);
        }

        return view('subscriptions.show', compact('plan'));
    }

    public function checkout(SubscriptionPlan $plan)
    {
        if (! $plan->is_active) {
            return redirect()->route('subscriptions.index')
                ->with('error', 'This plan is not available');
        }

        $user = Auth::user();

        if ($user->hasActiveSubscription()) {
            return redirect()->route('subscriptions.my')
                ->with('error', 'You already have an active subscription');
        }

        return view('subscriptions.checkout', compact('plan'));
    }

    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id',
            'payment_provider' => 'nullable|in:stripe,paystack,momo',
            'payment_method_id' => 'nullable|string',
        ]);

        $plan = SubscriptionPlan::findOrFail($validated['plan_id']);
        $user = Auth::user();

        if ($user->hasActiveSubscription()) {
            return redirect()->route('subscriptions.my')
                ->with('error', 'You already have an active subscription');
        }

        try {
            $providerSubscriptionId = null;
            $paymentProvider = $validated['payment_provider'] ?? null;

            if ($paymentProvider === 'stripe' && $this->stripeService->isConfigured()) {
                $result = $this->stripeService->createSubscription(
                    $user,
                    $plan,
                    $validated['payment_method_id'] ?? null
                );
                $providerSubscriptionId = $result['subscription_id'];
            } elseif ($paymentProvider === 'paystack' && $this->paystackService->isConfigured()) {
                $result = $this->paystackService->createSubscription(
                    $user,
                    $plan
                );
                $providerSubscriptionId = $result['subscription_code'] ?? null;
            }

            $subscription = $this->subscriptionService->createSubscription(
                $user,
                $plan,
                $paymentProvider,
                $providerSubscriptionId
            );

            Mail::to($user->email)->send(new SubscriptionCreated($subscription));

            return redirect()->route('subscriptions.success')
                ->with('success', 'Subscription started successfully! Your free trial has begun.');
        } catch (\Exception $e) {
            Log::error('Subscription creation failed: '.$e->getMessage());

            return redirect()->back()
                ->with('error', 'Failed to create subscription. Please try again or contact support.');
        }
    }

    public function mySubscription()
    {
        $user = Auth::user();
        $subscription = $user->subscription;

        if (! $subscription) {
            return redirect()->route('subscriptions.index')
                ->with('info', 'You do not have an active subscription');
        }

        $payments = $subscription->payments()->latest()->paginate(10);

        return view('subscriptions.my-subscription', compact('subscription', 'payments'));
    }

    public function cancel(Request $request)
    {
        $user = Auth::user();
        $subscription = $user->subscription;

        if (! $subscription || ! $subscription->isActive()) {
            return redirect()->back()
                ->with('error', 'No active subscription found');
        }

        $immediately = $request->boolean('immediately', false);

        try {
            if ($subscription->payment_provider === 'stripe' && $subscription->payment_provider_subscription_id) {
                $this->stripeService->cancelSubscription(
                    $subscription->payment_provider_subscription_id,
                    $immediately
                );
            } elseif ($subscription->payment_provider === 'paystack' && $subscription->payment_provider_subscription_id) {
                // Paystack cancellation requires email token which we should store
                // For now, just cancel locally
                Log::info('Paystack cancellation requires manual handling via dashboard');
            }

            $this->subscriptionService->cancelSubscription($subscription, $immediately);

            $message = $immediately
                ? 'Your subscription has been canceled immediately'
                : 'Your subscription will be canceled at the end of the billing period';

            return redirect()->route('subscriptions.my')
                ->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Subscription cancellation failed: '.$e->getMessage());

            return redirect()->back()
                ->with('error', 'Failed to cancel subscription. Please try again or contact support.');
        }
    }

    public function resume()
    {
        $user = Auth::user();
        $subscription = $user->subscription;

        if (! $subscription || ! $subscription->onGracePeriod()) {
            return redirect()->back()
                ->with('error', 'Cannot resume subscription');
        }

        try {
            if ($subscription->payment_provider === 'stripe' && $subscription->payment_provider_subscription_id) {
                $this->stripeService->resumeSubscription(
                    $subscription->payment_provider_subscription_id
                );
            } elseif ($subscription->payment_provider === 'paystack' && $subscription->payment_provider_subscription_id) {
                // Paystack resume requires email token
                Log::info('Paystack resume requires manual handling via dashboard');
            }

            $this->subscriptionService->resumeSubscription($subscription);

            return redirect()->route('subscriptions.my')
                ->with('success', 'Your subscription has been resumed');
        } catch (\Exception $e) {
            Log::error('Subscription resume failed: '.$e->getMessage());

            return redirect()->back()
                ->with('error', 'Failed to resume subscription. Please try again or contact support.');
        }
    }

    public function success()
    {
        return view('subscriptions.success');
    }
}
