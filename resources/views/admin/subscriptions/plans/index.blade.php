@extends('admin.layout')

@section('title', 'Subscription Plans')
@section('header', 'Subscription Plans Management')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h3 class="text-2xl font-bold text-white">Subscription Plans</h3>
        <p class="text-gray-400 mt-1">Manage subscription tiers and pricing</p>
    </div>
    <a href="{{ route('admin.subscriptions.plans.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
        </svg>
        Add Plan
    </a>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
        <div class="flex items-center justify-between mb-2">
            <span class="text-gray-400 text-sm">Total Plans</span>
            <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
        </div>
        <p class="text-3xl font-bold text-white">{{ $plans->count() }}</p>
    </div>

    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
        <div class="flex items-center justify-between mb-2">
            <span class="text-gray-400 text-sm">Active Plans</span>
            <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <p class="text-3xl font-bold text-white">{{ $plans->where('is_active', true)->count() }}</p>
    </div>

    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
        <div class="flex items-center justify-between mb-2">
            <span class="text-gray-400 text-sm">Total Subscribers</span>
            <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
        </div>
        <p class="text-3xl font-bold text-white">{{ $plans->sum('subscriptions_count') }}</p>
    </div>

    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
        <div class="flex items-center justify-between mb-2">
            <span class="text-gray-400 text-sm">Popular Plan</span>
            <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
            </svg>
        </div>
        <p class="text-xl font-bold text-white">{{ $plans->where('is_popular', true)->first()?->name ?? 'None' }}</p>
    </div>
</div>

<div class="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden">
    <table class="min-w-full divide-y divide-gray-700">
        <thead class="bg-gray-900">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Plan</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Price</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Limits</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Commission</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Subscribers</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-700">
            @forelse($plans as $plan)
                <tr class="hover:bg-gray-750">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div>
                                <div class="text-sm font-medium text-white flex items-center">
                                    {{ $plan->name }}
                                    @if($plan->is_popular)
                                        <span class="ml-2 px-2 py-1 text-xs rounded bg-amber-900 text-amber-300">Popular</span>
                                    @endif
                                </div>
                                <div class="text-sm text-gray-400">{{ ucfirst($plan->billing_cycle) }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-semibold text-white">${{ number_format($plan->price, 2) }}</div>
                        <div class="text-xs text-gray-400">{{ $plan->currency }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-xs text-gray-300">
                            <div>Products: <span class="font-medium text-white">{{ $plan->max_products == -1 ? 'Unlimited' : $plan->max_products }}</span></div>
                            <div>Orders: <span class="font-medium text-white">{{ $plan->max_orders == -1 ? 'Unlimited' : $plan->max_orders }}</span></div>
                            <div>Trial: <span class="font-medium text-white">{{ $plan->trial_days }} days</span></div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-sm font-medium text-white">{{ $plan->commission_rate }}%</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-sm text-gray-300">{{ $plan->subscriptions_count }}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <form action="{{ route('admin.subscriptions.plans.toggle', $plan) }}" method="POST">
                            @csrf
                            <button type="submit" class="inline-flex items-center">
                                @if($plan->is_active)
                                    <span class="px-2 py-1 text-xs rounded bg-green-900 text-green-300 cursor-pointer hover:bg-green-800">Active</span>
                                @else
                                    <span class="px-2 py-1 text-xs rounded bg-gray-700 text-gray-400 cursor-pointer hover:bg-gray-600">Inactive</span>
                                @endif
                            </button>
                        </form>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="{{ route('admin.subscriptions.plans.edit', $plan) }}" class="text-blue-400 hover:text-blue-300 mr-3">Edit</a>
                        <form action="{{ route('admin.subscriptions.plans.destroy', $plan) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure? This will affect {{ $plan->subscriptions_count }} subscribers.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-red-300" {{ $plan->subscriptions_count > 0 ? 'disabled' : '' }}>
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                        No subscription plans found. <a href="{{ route('admin.subscriptions.plans.create') }}" class="text-indigo-400 hover:text-indigo-300">Create one</a>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

