@extends('layouts.shop')

@section('content')
<x-hero-section
    pageIdentifier="pricing"
    heightPreset="standard"
    heading-tag="h2"
    :fallbackTitle="__('ui.subscription.plan_details_title')"
    :fallbackSubtitle="__('ui.subscription.plan_details_subtitle')"
    fallbackImage="/images/hero-banners/pricing-banner.avif"
    
/>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200">
            <div class="p-8 md:p-12">
                <div class="flex items-start justify-between mb-6">
                    <div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wider bg-orange-100 text-orange-700">
                            {{ $plan->billing_cycle_label ?? ucfirst($plan->billing_cycle) }}
                        </span>
                        @if($plan->is_popular)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wider bg-indigo-100 text-indigo-700 ml-2">
                                Popular
                            </span>
                        @endif
                        <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mt-2">{{ $plan->name }}</h1>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <span class="text-5xl font-bold text-gray-900">${{ number_format($plan->price, 0) }}</span>
                        <span class="text-gray-500 ml-2 align-middle">
                            {{ __('ui.subscription.per', ['cycle' => $plan->billing_cycle === 'monthly' ? 'month' : 'year']) }}
                        </span>
                    </div>
                </div>

                <p class="text-gray-600 mb-8 leading-relaxed">{{ $plan->description }}</p>

                @if($plan->trial_days > 0)
                    <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-8 flex items-start gap-3">
                        <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-green-800">{{ __('ui.subscription.free_trial') }}</p>
                            <p class="text-xs text-green-600">{{ __('ui.subscription.trial_days', ['days' => $plan->trial_days]) }}</p>
                        </div>
                    </div>
                @endif

                <div class="space-y-4 mb-8">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-gray-900">
                                <strong>{{ $plan->max_products === -1 ? __('ui.subscription.unlimited') : $plan->max_products }}</strong> {{ __('ui.subscription.products') }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-gray-900">
                                <strong>{{ $plan->max_orders === -1 ? __('ui.subscription.unlimited') : $plan->max_orders }}</strong> {{ __('ui.subscription.orders_per_month') }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-gray-900">
                                <strong>{{ $plan->commission_rate }}%</strong> {{ __('ui.subscription.commission_rate') }}
                            </p>
                        </div>
                    </div>
                </div>

                @php
                    $allFeatures = is_array($plan->features) ? $plan->features : [];
                @endphp

                @if(count($allFeatures) > 0)
                    <div class="mb-8">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">{{ __('ui.subscription.features') }}</h3>
                        <ul class="space-y-3">
                            @foreach($allFeatures as $feature)
                                <li class="flex items-start gap-3">
                                    <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span class="text-gray-700 text-sm">{{ $feature }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @auth
                    @if($userSubscription && $userSubscription->subscription_plan_id === $plan->id)
                        <button disabled class="w-full bg-gray-400 text-white rounded-xl px-6 py-4 font-semibold">
                            {{ __('ui.subscription.current_plan') }}
                        </button>
                    @elseif(auth()->user()?->hasActiveSubscription())
                        <a href="{{ route('subscriptions.my') }}" class="block w-full bg-orange-600 hover:bg-orange-700 text-white text-center rounded-xl px-6 py-4 font-semibold transition-colors">
                            {{ __('ui.subscription.manage_subscription') }}
                        </a>
                    @else
                        <a href="{{ route('subscriptions.checkout', $plan) }}" class="block w-full bg-gradient-to-r from-orange-600 to-red-600 hover:from-orange-700 hover:to-red-700 text-white text-center rounded-xl px-6 py-4 font-semibold transition-all duration-200 shadow-lg hover:shadow-xl">
                            {{ __('ui.subscription.get_started') }}
                        </a>
                    @endif
                @else
                    <a href="{{ route('login') }}?redirect={{ urlencode(route('subscriptions.checkout', $plan)) }}" class="block w-full bg-gradient-to-r from-orange-600 to-red-600 hover:from-orange-700 hover:to-red-700 text-white text-center rounded-xl px-6 py-4 font-semibold transition-all duration-200 shadow-lg hover:shadow-xl">
                        {{ __('ui.subscription.get_started') }}
                    </a>
                @endauth
            </div>
        </div>

        <div class="mt-8 text-center">
            <a href="{{ route('subscriptions.index') }}" class="text-orange-600 hover:text-orange-700 font-medium inline-flex items-center gap-1.5">
                {{ __('ui.subscription.back_to_plans') }}
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
        </div>
    </div>
</div>
@endsection
