@extends('admin.layout')

@section('title', 'Withdrawal Requests')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-2xl font-bold text-white">Withdrawal Requests</h3>
            <p class="text-gray-400 mt-1">Manage vendor withdrawal requests</p>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Total Requests</p>
                    <p class="text-3xl font-bold text-white mt-1">{{ $stats['total'] }}</p>
                </div>
                <div class="bg-blue-500/20 p-3 rounded-lg">
                    <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Pending</p>
                    <p class="text-3xl font-bold text-yellow-500 mt-1">{{ $stats['pending'] }}</p>
                </div>
                <div class="bg-yellow-500/20 p-3 rounded-lg">
                    <svg class="w-8 h-8 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Processing</p>
                    <p class="text-3xl font-bold text-indigo-500 mt-1">{{ $stats['processing'] }}</p>
                </div>
                <div class="bg-indigo-500/20 p-3 rounded-lg">
                    <svg class="w-8 h-8 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Completed</p>
                    <p class="text-3xl font-bold text-green-500 mt-1">{{ $stats['completed'] }}</p>
                </div>
                <div class="bg-green-500/20 p-3 rounded-lg">
                    <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Total Paid</p>
                    <p class="text-2xl font-bold text-white mt-1">${{ number_format($stats['total_amount'], 2) }}</p>
                </div>
                <div class="bg-purple-500/20 p-3 rounded-lg">
                    <svg class="w-8 h-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-gray-800 rounded-lg border border-gray-700">
        <div class="p-6 border-b border-gray-700">
            <form method="GET" action="{{ route('admin.withdrawals.index') }}" class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by reference, vendor name, or email..." class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-orange-500">
                </div>
                <select name="status" class="bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-orange-500">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
                <button type="submit" class="bg-orange-600 hover:bg-orange-700 text-white px-6 py-2 rounded-lg transition">
                    Filter
                </button>
                @if(request()->hasAny(['search', 'status']))
                    <a href="{{ route('admin.withdrawals.index') }}" class="bg-gray-700 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition text-center">
                        Clear
                    </a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Reference</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Vendor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Fee</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Net</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Method</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @forelse($withdrawals as $withdrawal)
                        <tr class="hover:bg-gray-700/30 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-white">{{ $withdrawal->withdrawal_number }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-white">{{ $withdrawal->user->name }}</div>
                                <div class="text-xs text-gray-400">{{ $withdrawal->user->email }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-white">${{ number_format($withdrawal->amount, 2) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-400">${{ number_format($withdrawal->fee, 2) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-green-500">${{ number_format($withdrawal->net_amount, 2) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-300">{{ ucfirst(str_replace('_', ' ', $withdrawal->method)) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-yellow-500/20 text-yellow-500 border-yellow-500/50',
                                        'processing' => 'bg-indigo-500/20 text-indigo-500 border-indigo-500/50',
                                        'completed' => 'bg-green-500/20 text-green-500 border-green-500/50',
                                        'rejected' => 'bg-red-500/20 text-red-500 border-red-500/50',
                                    ];
                                @endphp
                                <span class="px-2 py-1 text-xs font-semibold rounded-full border {{ $statusColors[$withdrawal->status] ?? '' }}">
                                    {{ ucfirst($withdrawal->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                                {{ $withdrawal->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="{{ route('admin.withdrawals.show', $withdrawal) }}" class="text-orange-500 hover:text-orange-400">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center">
                                <svg class="w-16 h-16 mx-auto text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                </svg>
                                <p class="text-gray-400 text-lg">No withdrawal requests found</p>
                                <p class="text-gray-400 text-sm mt-2">Vendor withdrawal requests will appear here</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($withdrawals->hasPages())
            <div class="p-6 border-t border-gray-700">
                {{ $withdrawals->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

