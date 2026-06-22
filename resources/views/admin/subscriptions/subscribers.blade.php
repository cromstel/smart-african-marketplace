@extends('admin.layout')

@section('title', 'Subscribers')
@section('header', 'Subscription Subscribers')

@section('content')
<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4">
        <div class="bg-gray-800 border border-gray-700 rounded-lg p-6">
            <p class="text-sm text-gray-400">Total</p>
            <p class="text-2xl font-bold text-white mt-2">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="bg-gray-800 border border-gray-700 rounded-lg p-6">
            <p class="text-sm text-gray-400">Active</p>
            <p class="text-2xl font-bold text-green-400 mt-2">{{ number_format($stats['active']) }}</p>
        </div>
        <div class="bg-gray-800 border border-gray-700 rounded-lg p-6">
            <p class="text-sm text-gray-400">Trialing</p>
            <p class="text-2xl font-bold text-blue-400 mt-2">{{ number_format($stats['trialing']) }}</p>
        </div>
        <div class="bg-gray-800 border border-gray-700 rounded-lg p-6">
            <p class="text-sm text-gray-400">Canceled</p>
            <p class="text-2xl font-bold text-red-400 mt-2">{{ number_format($stats['canceled']) }}</p>
        </div>
        <div class="bg-gray-800 border border-gray-700 rounded-lg p-6">
            <p class="text-sm text-gray-400">Revenue</p>
            <p class="text-2xl font-bold text-white mt-2">${{ number_format((float) $stats['revenue'], 2) }}</p>
        </div>
    </div>

    <div class="bg-gray-800 border border-gray-700 rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-700">
            <h3 class="text-lg font-semibold text-white">Subscribers</h3>
        </div>

        @if($subscriptions->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-700">
                    <thead class="bg-gray-900/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Plan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Period Ends</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        @foreach($subscriptions as $subscription)
                            <tr class="hover:bg-gray-700/30">
                                <td class="px-6 py-4">
                                    <div class="text-white font-medium">{{ $subscription->user?->name ?? 'Unknown User' }}</div>
                                    <div class="text-sm text-gray-400">{{ $subscription->user?->email ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-4 text-gray-300">{{ $subscription->plan?->name ?? 'No Plan' }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs rounded-full bg-gray-700 text-gray-200 uppercase">
                                        {{ $subscription->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-300">
                                    {{ optional($subscription->current_period_end)->format('M d, Y') ?? '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-700">
                {{ $subscriptions->links() }}
            </div>
        @else
            <div class="px-6 py-10 text-center text-gray-400">
                No subscribers found.
            </div>
        @endif
    </div>
</div>
@endsection

