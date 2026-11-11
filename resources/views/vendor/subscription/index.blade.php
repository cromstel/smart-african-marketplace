@extends('vendor.layout')

@section('title', 'My Subscription')
@section('header', 'My Subscription')

@section('content')
<div class="max-w-4xl">
    <div class="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden mb-6">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-2xl font-bold text-white">Current Plan</h3>
                <span class="px-4 py-2 rounded text-sm font-medium {{
                    $subscription->status === 'active' ? 'bg-green-900 text-green-300' :
                    ($subscription->status === 'trialing' ? 'bg-blue-900 text-blue-300' :
                    ($subscription->status === 'expired' ? 'bg-red-900 text-red-300' : 'bg-yellow-900 text-yellow-300'))
                }}">
                    {{ ucfirst($subscription->status) }}
                </span>
            </div>

            <div class="space-y-4">
                <div class="flex items-center justify-between py-3 border-b border-gray-700">
                    <span class="text-gray-400">Plan Name</span>
                    <span class="text-white font-medium">{{ $subscription->plan?->name ?? 'Free Trial' }}</span>
                </div>
                <div class="flex items-center justify-between py-3 border-b border-gray-700">
                    <span class="text-gray-400">Billing Cycle</span>
                    <span class="text-white font-medium">{{ ucfirst($subscription->plan?->billing_cycle ?? 'monthly') }}</span>
                </div>
                <div class="flex items-center justify-between py-3 border-b border-gray-700">
                    <span class="text-gray-400">Trial Ends</span>
                    <span class="text-white font-medium">{{ $subscription->trial_ends_at?->format('F j, Y') ?? 'N/A' }}</span>
                </div>
                <div class="flex items-center justify-between py-3">
                    <span class="text-gray-400">Current Period Ends</span>
                    <span class="text-white font-medium">{{ $subscription->current_period_end?->format('F j, Y') ?? 'N/A' }}</span>
                </div>
            </div>

            @if($subscription->plan && !empty($subscription->plan->features))
                <div class="mt-6 space-y-3">
                    <h4 class="text-lg font-bold text-white">Plan Features</h4>
                    <ul class="space-y-2">
                        @foreach($subscription->plan->features as $feature)
                            <li class="flex items-center space-x-2 text-gray-300">
                                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>{{ $feature }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="mt-8 flex items-center space-x-4">
                <a href="{{ route('vendor.subscription.upgrade') }}" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium">
                    Change Plan
                </a>
            </div>
        </div>
    </div>

    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
        <h3 class="text-xl font-bold text-white mb-4">Usage Statistics</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <p class="text-sm text-gray-400 mb-1">Products</p>
                <p class="text-2xl font-bold text-white">{{ auth()->user()->vendor->products()->count() }}</p>
                <p class="text-xs text-gray-400 mt-1">of {{ $subscription->plan?->max_products === -1 ? 'Unlimited' : ($subscription->plan?->max_products ?? 'N/A') }} allowed</p>
            </div>
            <div>
                <p class="text-sm text-gray-400 mb-1">Orders This Month</p>
                <p class="text-2xl font-bold text-white">
                    {{ auth()->user()->vendor->orderItems()->whereMonth('created_at', now()->month)->distinct('order_id')->count('order_id') }}
                </p>
            </div>
            <div>
                <p class="text-sm text-gray-400 mb-1">Total Revenue</p>
                <p class="text-2xl font-bold text-white">
                    ${{ number_format(auth()->user()->vendor->orderItems()->whereHas('order', fn($q) => $q->where('payment_status', 'paid'))->sum('total'), 2) }}
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

