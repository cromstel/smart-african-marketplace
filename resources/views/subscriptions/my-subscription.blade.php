@extends('layouts.shop')

@section('content')
<div class="container mx-auto px-4 py-12">
    <div class="max-w-5xl mx-auto">
        <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-8">My Subscription</h2>

        @if($subscription->isTrialing())
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8">
                <div class="flex items-start">
                    <svg class="w-6 h-6 text-blue-600 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <h3 class="font-bold text-blue-900">Free Trial Active</h3>
                        <p class="text-blue-700 mt-1">
                            Your trial ends on {{ $subscription->trial_ends_at->format('F j, Y') }} 
                            ({{ $subscription->daysUntilTrialEnds() }} days remaining). 
                            You won't be charged until then.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        @if($subscription->cancel_at_period_end)
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-6 mb-8">
                <div class="flex items-start justify-between">
                    <div class="flex items-start">
                        <svg class="w-6 h-6 text-amber-600 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <div>
                            <h3 class="font-bold text-amber-900">Subscription Scheduled for Cancellation</h3>
                            <p class="text-amber-700 mt-1">
                                Your subscription will end on {{ $subscription->current_period_end->format('F j, Y') }}. 
                                You'll continue to have access until then.
                            </p>
                        </div>
                    </div>
                    <form action="{{ route('subscriptions.resume') }}" method="POST">
                        @csrf
                        <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg font-medium text-sm whitespace-nowrap">
                            Resume Subscription
                        </button>
                    </form>
                </div>
            </div>
        @endif

        <div class="grid md:grid-cols-3 gap-8">
            <div class="md:col-span-2 space-y-8">
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                    <div class="bg-gradient-to-r from-orange-600 to-red-600 p-6 text-white">
                        <h2 class="text-2xl font-bold">{{ $subscription->plan->name }}</h2>
                        <p class="text-orange-100 mt-1">{{ $subscription->plan->description }}</p>
                    </div>

                    <div class="p-6">
                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div>
                                <p class="text-sm text-gray-600">Status</p>
                                <p class="text-lg font-semibold text-gray-900 mt-1">
                                    @if($subscription->isTrialing())
                                        <span class="text-blue-600">On Trial</span>
                                    @elseif($subscription->isActive())
                                        <span class="text-green-600">Active</span>
                                    @elseif($subscription->isPastDue())
                                        <span class="text-red-600">Past Due</span>
                                    @else
                                        <span class="text-gray-600">{{ ucfirst($subscription->status) }}</span>
                                    @endif
                                </p>
                            </div>

                            <div>
                                <p class="text-sm text-gray-600">Billing Cycle</p>
                                <p class="text-lg font-semibold text-gray-900 mt-1">{{ ucfirst($subscription->plan->billing_cycle) }}</p>
                            </div>

                            <div>
                                <p class="text-sm text-gray-600">Price</p>
                                <p class="text-lg font-semibold text-gray-900 mt-1">
                                    ${{ number_format($subscription->plan->price, 2) }}/{{ $subscription->plan->billing_cycle === 'monthly' ? 'mo' : 'yr' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-sm text-gray-600">Next Billing Date</p>
                                <p class="text-lg font-semibold text-gray-900 mt-1">
                                    {{ $subscription->current_period_end ? $subscription->current_period_end->format('M j, Y') : 'N/A' }}
                                </p>
                            </div>
                        </div>

                        <div class="border-t border-gray-200 pt-6">
                            <h3 class="font-bold text-gray-900 mb-4">Plan Features</h3>
                            <div class="grid md:grid-cols-2 gap-3">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span class="text-gray-700 text-sm">
                                        {{ $subscription->plan->max_products === -1 ? 'Unlimited' : $subscription->plan->max_products }} Products
                                    </span>
                                </div>

                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span class="text-gray-700 text-sm">
                                        {{ $subscription->plan->max_orders === -1 ? 'Unlimited' : $subscription->plan->max_orders }} Orders/month
                                    </span>
                                </div>

                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span class="text-gray-700 text-sm">
                                        {{ $subscription->plan->commission_rate }}% Commission Rate
                                    </span>
                                </div>

                                @if($subscription->plan->features && count($subscription->plan->features) > 0)
                                    @foreach($subscription->plan->features as $feature)
                                        <div class="flex items-start">
                                            <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            <span class="text-gray-700 text-sm">{{ $feature }}</span>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        @if(!$subscription->cancel_at_period_end)
                            <div class="border-t border-gray-200 pt-6 mt-6">
                                <form action="{{ route('subscriptions.cancel') }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel your subscription? You will retain access until the end of your billing period.')">
                                    @csrf
                                    <button type="submit" class="text-red-600 hover:text-red-700 font-medium text-sm">
                                        Cancel Subscription
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900">Payment History</h2>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Provider</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($payments as $payment)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $payment->created_at->format('M j, Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            ${{ number_format($payment->amount, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($payment->status === 'paid')
                                                <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-800">Paid</span>
                                            @elseif($payment->status === 'failed')
                                                <span class="px-2 py-1 text-xs rounded bg-red-100 text-red-800">Failed</span>
                                            @else
                                                <span class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-800">{{ ucfirst($payment->status) }}</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                            {{ ucfirst($payment->payment_provider ?? 'N/A') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                            No payment history yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($payments->hasPages())
                        <div class="p-4 border-t border-gray-200">
                            {{ $payments->links() }}
                        </div>
                    @endif
                </div>
            </div>

            <div class="md:col-span-1">
                <div class="bg-white rounded-2xl shadow-lg p-6 sticky top-24">
                    <h3 class="font-bold text-gray-900 mb-4">Quick Actions</h3>
                    <div class="space-y-3">
                        <a href="{{ route('subscriptions.index') }}" class="block text-center bg-gray-100 hover:bg-gray-200 text-gray-800 px-4 py-3 rounded-lg font-medium text-sm transition-colors">
                            View All Plans
                        </a>

                        <a href="{{ route('contact.index') }}" class="block text-center bg-gray-100 hover:bg-gray-200 text-gray-800 px-4 py-3 rounded-lg font-medium text-sm transition-colors">
                            Contact Support
                        </a>
                    </div>

                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <h4 class="font-semibold text-gray-900 mb-2">Need Help?</h4>
                        <p class="text-sm text-gray-600 mb-3">Questions about your subscription or billing?</p>
                        <a href="{{ route('contact.index') }}" class="text-orange-600 hover:text-orange-700 text-sm font-medium">
                            Get Support →
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

