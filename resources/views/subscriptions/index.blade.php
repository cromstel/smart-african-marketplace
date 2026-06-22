@extends('layouts.shop')

@push('head')
<style>
    @keyframes riseIn {
        from {
            opacity: 0;
            transform: translateY(26px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    @keyframes pulseGlow {
        0%, 100% {
            box-shadow: 0 0 0 0 rgba(234, 88, 12, 0.22);
        }
        50% {
            box-shadow: 0 0 0 12px rgba(234, 88, 12, 0);
        }
    }
    .reveal-item {
        opacity: 1;
        transform: translateY(0);
        transition: opacity 500ms ease, transform 500ms ease;
    }
    .reveal-ready .reveal-item {
        opacity: 0;
        transform: translateY(26px);
    }
    .reveal-item.is-visible {
        opacity: 1;
        transform: translateY(0);
        animation: riseIn 520ms ease-out;
    }
    .glow-chip {
        animation: pulseGlow 2.2s ease-in-out infinite;
    }
</style>
@endpush

@php
    $isTrialing = auth()->check() && $userSubscription && $userSubscription->isTrialing();
@endphp

@section('content')
<x-hero-section 
    page-identifier="pricing"
    fallback-title="Choose Your Perfect Plan"
    fallback-subtitle="Compare paid vendor plans and upgrade anytime to unlock more capacity and growth features."
    heading-tag="h2"
    fallback-image="/images/hero-banners/pricing-banner.avif"
/>

<div class="relative z-20 container mx-auto px-4 -mt-6 md:-mt-12 pb-16">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-10 items-stretch">
        @foreach($plans as $plan)
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden transition-all duration-300 hover:shadow-2xl hover:-translate-y-2 flex flex-col {{ $plan->is_popular ? 'ring-4 ring-orange-500 relative' : '' }}">
                @if($plan->is_popular)
                    <div class="absolute top-0 right-0 bg-orange-500 text-white px-4 py-1 rounded-bl-lg text-sm font-bold">
                        POPULAR
                    </div>
                @endif

                <div class="p-8 flex flex-col flex-grow">
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ $plan->name }}</h3>
                    <p class="text-gray-700 text-sm mb-6 min-h-[3rem]">{{ $plan->description }}</p>

                    <div class="mb-6">
                        <div class="flex items-baseline">
                            <span class="text-5xl font-bold text-gray-900">${{ number_format($plan->price, 0) }}</span>
                            <span class="text-gray-700 ml-2">/{{ $plan->billing_cycle === 'monthly' ? 'mo' : ($plan->billing_cycle === 'yearly' ? 'yr' : 'lifetime') }}</span>
                        </div>
                        @if($plan->trial_days > 0)
                            <p class="text-sm text-green-600 mt-2 font-medium">
                                {{ $plan->trial_days }} days free trial
                            </p>
                        @endif
                    </div>

                    @auth
                        @if($userSubscription && $userSubscription->subscription_plan_id === $plan->id)
                            <button disabled class="w-full bg-gray-400 text-white rounded-lg px-6 py-3 font-semibold mt-auto mb-6 cursor-not-allowed">
                                Current Plan
                            </button>
                        @elseif(auth()->user()?->hasActiveSubscription())
                            <a href="{{ route('subscriptions.my') }}" class="block w-full bg-orange-600 hover:bg-orange-700 text-white text-center rounded-lg px-6 py-3 font-semibold mt-auto mb-6 transition-colors">
                                Manage Subscription
                            </a>
                        @else
                            <a href="{{ route('subscriptions.checkout', $plan) }}" class="block w-full bg-orange-600 hover:bg-orange-700 text-white text-center rounded-lg px-6 py-3 font-semibold mt-auto mb-6 transition-colors">
                                Get Started
                            </a>
                        @endif
                    @else
                        <a href="{{ route('login') }}?redirect={{ urlencode(route('subscriptions.checkout', $plan)) }}" class="block w-full bg-orange-600 hover:bg-orange-700 text-white text-center rounded-lg px-6 py-3 font-semibold mt-auto mb-6 transition-colors">
                            Get Started
                        </a>
                    @endauth

                    <div class="space-y-3">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span class="text-gray-700 text-sm">
                                <strong>{{ $plan->max_products === -1 ? 'Unlimited' : $plan->max_products }}</strong> Products
                            </span>
                        </div>

                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span class="text-gray-700 text-sm">
                                <strong>{{ $plan->max_orders === -1 ? 'Unlimited' : $plan->max_orders }}</strong> Orders/month
                            </span>
                        </div>

                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span class="text-gray-700 text-sm">
                                <strong>{{ $plan->commission_rate }}%</strong> Commission Rate
                            </span>
                        </div>

                        @php
                            $allFeatures = is_array($plan->features) ? $plan->features : [];
                            $visibleFeatureLimit = 4;
                            $visibleFeatures = array_slice($allFeatures, 0, $visibleFeatureLimit);
                            $hiddenFeatures = array_slice($allFeatures, $visibleFeatureLimit);
                        @endphp

                        @if(count($visibleFeatures) > 0)
                            @foreach($visibleFeatures as $feature)
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span class="text-gray-700 text-sm">{{ $feature }}</span>
                                </div>
                            @endforeach
                        @endif

                        @if(count($hiddenFeatures) > 0)
                            <div id="plan-features-{{ $plan->id }}" class="hidden mt-3 space-y-3">
                                @foreach($hiddenFeatures as $feature)
                                    <div class="flex items-start">
                                        <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <span class="text-gray-700 text-sm">{{ $feature }}</span>
                                    </div>
                                @endforeach
                            </div>

                            <button
                                type="button"
                                class="mt-4 text-sm font-semibold text-orange-600 hover:text-orange-700 transition-colors"
                                data-features-toggle
                                data-target="plan-features-{{ $plan->id }}"
                                data-expand-label="See all features ({{ count($hiddenFeatures) }} more)"
                                data-collapse-label="Show fewer features"
                                aria-expanded="false"
                            >
                                See all features ({{ count($hiddenFeatures) }} more)
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if($isTrialing)
        <div class="mt-10">
            <div class="rounded-2xl border border-orange-200 bg-orange-50 p-5 md:p-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-wide text-orange-700">Trial Active</p>
                        <p class="text-gray-800 mt-1">
                            Your trial is active. Upgrade to a paid plan to keep access and expand your vendor capabilities.
                        </p>
                    </div>
                    <a href="{{ route('subscriptions.my') }}" class="inline-flex items-center justify-center bg-orange-600 hover:bg-orange-700 text-white rounded-lg px-5 py-2.5 font-semibold transition-colors">
                        Upgrade Now
                    </a>
                </div>
            </div>
        </div>
    @endif

    <div class="mt-16 md:mt-24">
        <div class="text-center mb-8 md:mb-12">
            <span class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-semibold uppercase tracking-wider bg-orange-100 text-orange-700 glow-chip">
                Built for Growth
            </span>
            <h2 class="mt-4 text-3xl md:text-4xl font-bold text-gray-900">Functionalities for Vendors</h2>
            <p class="mt-3 text-gray-600 max-w-2xl mx-auto">
                Tools that help vendors launch quickly, sell consistently, and scale with predictable operations.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 md:gap-10">
            <div class="reveal-item group bg-white rounded-2xl border border-orange-100 p-6 shadow-lg hover:-translate-y-1 hover:shadow-xl transition-all duration-300">
                <h3 class="text-lg font-bold text-gray-900 mb-2">Catalog Management</h3>
                <p class="text-sm text-gray-700">Manage products, variants, pricing, and stock from one place.</p>
            </div>
            <div class="reveal-item group bg-white rounded-2xl border border-orange-100 p-6 shadow-lg hover:-translate-y-1 hover:shadow-xl transition-all duration-300">
                <h3 class="text-lg font-bold text-gray-900 mb-2">Order Operations</h3>
                <p class="text-sm text-gray-700">Track orders, shipping updates, and fulfillment statuses in real time.</p>
            </div>
            <div class="reveal-item group bg-white rounded-2xl border border-orange-100 p-6 shadow-lg hover:-translate-y-1 hover:shadow-xl transition-all duration-300">
                <h3 class="text-lg font-bold text-gray-900 mb-2">Revenue Controls</h3>
                <p class="text-sm text-gray-700">Clear commission visibility, withdrawal workflows, and plan-based limits.</p>
            </div>
            <div class="reveal-item group bg-white rounded-2xl border border-orange-100 p-6 shadow-lg hover:-translate-y-1 hover:shadow-xl transition-all duration-300">
                <h3 class="text-lg font-bold text-gray-900 mb-2">Enterprise Domains</h3>
                <p class="text-sm text-gray-700">Enterprise vendors can map verified custom domains to branded storefronts.</p>
            </div>
        </div>
    </div>

    <div class="mt-14 md:mt-20">
        <div class="rounded-3xl bg-gradient-to-br from-gray-900 via-gray-800 to-black p-8 md:p-12 overflow-hidden relative">
            <div class="absolute -top-24 -right-20 w-72 h-72 rounded-full bg-orange-500/20 blur-3xl"></div>
            <div class="absolute -bottom-28 -left-16 w-72 h-72 rounded-full bg-red-500/20 blur-3xl"></div>
            <div class="relative">
                <div class="text-center mb-8 md:mb-10">
                    <h2 class="text-3xl md:text-4xl font-bold text-white">Ultimate Customer Experience</h2>
                    <p class="mt-3 text-gray-300 max-w-2xl mx-auto">
                        Every plan supports a fast and trusted shopping journey that keeps customers returning.
                    </p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 md:gap-10">
                    <div class="reveal-item bg-white rounded-2xl border border-orange-100 p-6 text-gray-900 shadow-lg">
                        <h3 class="font-bold text-lg mb-2 text-gray-900">Fast Discovery</h3>
                        <p class="text-sm text-gray-700">Customers quickly find products and services through curated browsing flows.</p>
                    </div>
                    <div class="reveal-item bg-white rounded-2xl border border-orange-100 p-6 text-gray-900 shadow-lg">
                        <h3 class="font-bold text-lg mb-2 text-gray-900">Trusted Checkout</h3>
                        <p class="text-sm text-gray-700">Secure payments and clear order visibility from checkout to delivery.</p>
                    </div>
                    <div class="reveal-item bg-white rounded-2xl border border-orange-100 p-6 text-gray-900 shadow-lg">
                        <h3 class="font-bold text-lg mb-2 text-gray-900">Loyal Retention</h3>
                        <p class="text-sm text-gray-700">Wishlist, loyalty, and referrals create a repeat-purchase flywheel.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-16 bg-gray-50 rounded-2xl p-8 md:p-12">
        <div class="text-center mb-8">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Frequently Asked Questions</h2>
        </div>

        <div class="grid md:grid-cols-2 gap-6 max-w-4xl mx-auto">
            <div>
                <h3 class="font-semibold text-gray-900 mb-2">Can I change my plan later?</h3>
                <p class="text-gray-600 text-sm">Yes! You can upgrade or downgrade your plan at any time from your subscription dashboard.</p>
            </div>

            <div>
                <h3 class="font-semibold text-gray-900 mb-2">What happens after my trial ends?</h3>
                <p class="text-gray-600 text-sm">Your trial will automatically convert to a paid subscription. You'll receive reminders 7 days before it ends.</p>
            </div>

            <div>
                <h3 class="font-semibold text-gray-900 mb-2">Can I cancel anytime?</h3>
                <p class="text-gray-600 text-sm">Absolutely! You can cancel your subscription at any time. You'll retain access until the end of your billing period.</p>
            </div>

            <div>
                <h3 class="font-semibold text-gray-900 mb-2">What payment methods do you accept?</h3>
                <p class="text-gray-600 text-sm">We accept all major credit cards via Stripe and Paystack, plus mobile money (MTN, Airtel) for African markets.</p>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.documentElement.classList.add('reveal-ready');
        const items = document.querySelectorAll('.reveal-item');
        if (!('IntersectionObserver' in window)) {
            items.forEach((el) => el.classList.add('is-visible'));
            return;
        }

        const observer = new IntersectionObserver((entries, obs) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    obs.unobserve(entry.target);
                }
            });
        }, { threshold: 0.18 });

        items.forEach((el, index) => {
            el.style.transitionDelay = (index % 4) * 70 + 'ms';
            observer.observe(el);
        });

        document.querySelectorAll('[data-features-toggle]').forEach((btn) => {
            btn.addEventListener('click', function () {
                const targetId = this.getAttribute('data-target');
                const target = targetId ? document.getElementById(targetId) : null;
                if (!target) {
                    return;
                }

                const isHidden = target.classList.contains('hidden');
                target.classList.toggle('hidden');
                this.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
                this.textContent = isHidden
                    ? (this.getAttribute('data-collapse-label') || 'Show fewer features')
                    : (this.getAttribute('data-expand-label') || 'See all features');
            });
        });
    });
</script>
@endsection

