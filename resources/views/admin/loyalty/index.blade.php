@extends('admin.layout')

@section('title', 'Loyalty Program')
@section('header', 'Loyalty Program')

@section('content')
<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="bg-gray-800 border border-gray-700 rounded-lg p-6">
            <p class="text-sm text-gray-400">Total Earned</p>
            <p class="text-2xl font-bold text-white mt-2">{{ number_format((int) $stats['total_earned']) }}</p>
        </div>
        <div class="bg-gray-800 border border-gray-700 rounded-lg p-6">
            <p class="text-sm text-gray-400">Total Redeemed</p>
            <p class="text-2xl font-bold text-white mt-2">{{ number_format((int) $stats['total_redeemed']) }}</p>
        </div>
        <div class="bg-gray-800 border border-gray-700 rounded-lg p-6">
            <p class="text-sm text-gray-400">Total Expired</p>
            <p class="text-2xl font-bold text-white mt-2">{{ number_format((int) $stats['total_expired']) }}</p>
        </div>
        <div class="bg-gray-800 border border-gray-700 rounded-lg p-6">
            <p class="text-sm text-gray-400">Active Balance</p>
            <p class="text-2xl font-bold text-white mt-2">{{ number_format((int) $stats['active_balance']) }}</p>
        </div>
    </div>

    <div class="bg-gray-800 border border-gray-700 rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-700">
            <h3 class="text-lg font-semibold text-white">Loyalty Transactions</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-700">
                <thead class="bg-gray-900/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Points</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @forelse($points as $point)
                        <tr class="hover:bg-gray-700/30">
                            <td class="px-6 py-4 text-gray-300">{{ $point->user?->name ?? 'Unknown User' }}</td>
                            <td class="px-6 py-4 {{ $point->points >= 0 ? 'text-green-400' : 'text-red-400' }}">{{ $point->points }}</td>
                            <td class="px-6 py-4 text-gray-300 uppercase">{{ $point->type }}</td>
                            <td class="px-6 py-4 text-gray-400">{{ $point->description }}</td>
                            <td class="px-6 py-4 text-gray-400">{{ $point->created_at->format('M d, Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-400">No loyalty transactions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-700">
            {{ $points->links() }}
        </div>
    </div>
</div>
@endsection

