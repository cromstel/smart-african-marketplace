@extends('admin.layout')

@section('title', 'Referrals')
@section('header', 'Referral Management')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h3 class="text-2xl font-bold text-white">All Referrals</h3>
        <p class="text-gray-400 mt-1">Manage customer referrals and rewards</p>
    </div>
    <a href="{{ route('admin.referrals.settings') }}" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg inline-flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        Settings
    </a>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
    <div class="bg-gray-800 rounded-lg border border-gray-700 p-4">
        <div class="text-gray-400 text-sm">Total Referrals</div>
        <div class="text-2xl font-bold text-white">{{ $stats['total_referrals'] }}</div>
    </div>
    <div class="bg-gray-800 rounded-lg border border-gray-700 p-4">
        <div class="text-gray-400 text-sm">Pending</div>
        <div class="text-2xl font-bold text-yellow-400">{{ $stats['pending'] }}</div>
    </div>
    <div class="bg-gray-800 rounded-lg border border-gray-700 p-4">
        <div class="text-gray-400 text-sm">Completed</div>
        <div class="text-2xl font-bold text-green-400">{{ $stats['completed'] }}</div>
    </div>
    <div class="bg-gray-800 rounded-lg border border-gray-700 p-4">
        <div class="text-gray-400 text-sm">Rewards Paid</div>
        <div class="text-2xl font-bold text-white">${{ number_format($stats['total_rewards_paid'], 2) }}</div>
    </div>
    <div class="bg-gray-800 rounded-lg border border-gray-700 p-4">
        <div class="text-gray-400 text-sm">Pending Rewards</div>
        <div class="text-2xl font-bold text-indigo-400">${{ number_format($stats['pending_rewards'], 2) }}</div>
    </div>
</div>

<!-- Filters -->
<div class="bg-gray-800 rounded-lg border border-gray-700 p-4 mb-6">
    <form method="GET" class="flex flex-wrap gap-4">
        <div>
            <label class="block text-sm text-gray-400 mb-1">Status</label>
            <select name="status" class="bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-white">
                <option value="">All Statuses</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
        </div>
        <div class="flex items-end">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg">Filter</button>
            <a href="{{ route('admin.referrals.index') }}" class="ml-2 text-gray-400 hover:text-white px-4 py-2">Clear</a>
        </div>
    </form>
</div>

<!-- Top Referrers -->
@if($topReferrers->count() > 0)
<div class="bg-gray-800 rounded-lg border border-gray-700 p-4 mb-6">
    <h4 class="text-lg font-semibold text-white mb-4">Top Referrers</h4>
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        @foreach($topReferrers as $referrer)
            <div class="bg-gray-750 rounded-lg p-3 text-center">
                <div class="text-white font-medium">{{ $referrer->name }}</div>
                <div class="text-green-400 font-bold">${{ number_format($referrer->referral_earnings, 2) }}</div>
            </div>
        @endforeach
    </div>
</div>
@endif

<!-- Referral Analytics -->
<div class="bg-gray-800 rounded-lg border border-gray-700 p-4 mb-6">
    <h4 class="text-lg font-semibold text-white mb-4">Referral Analytics</h4>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
        <div class="bg-gray-900 rounded-lg p-3 border border-gray-700">
            <div class="text-xs text-gray-400">30d Conversion Rate</div>
            <div class="text-xl font-bold text-emerald-400">{{ number_format($analytics['conversion_rate_30d'], 1) }}%</div>
        </div>
        <div class="bg-gray-900 rounded-lg p-3 border border-gray-700">
            <div class="text-xs text-gray-400">Avg Reward (Completed)</div>
            <div class="text-xl font-bold text-white">${{ number_format($analytics['avg_reward_completed'], 2) }}</div>
        </div>
        <div class="bg-gray-900 rounded-lg p-3 border border-gray-700">
            <div class="text-xs text-gray-400">New Referrals (30d)</div>
            <div class="text-xl font-bold text-white">{{ $analytics['new_referrals_30d'] }}</div>
        </div>
        <div class="bg-gray-900 rounded-lg p-3 border border-gray-700">
            <div class="text-xs text-gray-400">Completed (30d)</div>
            <div class="text-xl font-bold text-white">{{ $analytics['completed_30d'] }}</div>
        </div>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-2">
        @foreach($analytics['trend_14d'] as $point)
            <div class="bg-gray-900 rounded p-2 border border-gray-700 text-center">
                <div class="text-[10px] text-gray-400">{{ $point['label'] }}</div>
                <div class="text-sm font-semibold text-white">{{ $point['total'] }}</div>
            </div>
        @endforeach
    </div>
</div>

<!-- Referrals Table -->
<div class="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden">
    <table class="min-w-full divide-y divide-gray-700">
        <thead class="bg-gray-900">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Referrer</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Referred</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Reward</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Paid</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Date</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-700">
            @forelse($referrals as $referral)
                <tr class="hover:bg-gray-750">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-white">{{ $referral->referrer->name ?? 'N/A' }}</div>
                        <div class="text-xs text-gray-400">{{ $referral->referrer->email ?? 'N/A' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-white">{{ $referral->referred->name ?? 'N/A' }}</div>
                        <div class="text-xs text-gray-400">{{ $referral->referred->email ?? 'N/A' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-white font-semibold">
                        ${{ number_format($referral->reward_amount, 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($referral->status === 'pending')
                            <span class="px-2 py-1 text-xs rounded bg-yellow-900 text-yellow-300">Pending</span>
                        @elseif($referral->status === 'completed')
                            <span class="px-2 py-1 text-xs rounded bg-green-900 text-green-300">Completed</span>
                        @else
                            <span class="px-2 py-1 text-xs rounded bg-red-900 text-red-300">Rejected</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($referral->reward_paid)
                            <span class="text-green-400">Yes</span>
                        @else
                            <span class="text-gray-400">No</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                        {{ $referral->created_at->format('M d, Y') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        @if($referral->status === 'pending')
                            <form action="{{ route('admin.referrals.approve', $referral) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-green-400 hover:text-green-300 mr-3" onclick="return confirm('Approve this referral and pay the reward?')">Approve</button>
                            </form>
                            <form action="{{ route('admin.referrals.reject', $referral) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-red-400 hover:text-red-300" onclick="return confirm('Reject this referral?')">Reject</button>
                            </form>
                        @else
                            <span class="text-gray-500">-</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-gray-400">
                        No referrals found. Referrals will appear here when customers refer new users.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($referrals->hasPages())
    <div class="mt-6">
        {{ $referrals->links() }}
    </div>
@endif
@endsection

