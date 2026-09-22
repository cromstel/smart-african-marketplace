@extends('vendor.layout')

@section('title', 'Wallet')

@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-bold text-white">Wallet</h2>
    <p class="text-gray-400 mt-1">Track your earnings and ledger activity.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-gray-800 rounded-lg border border-gray-700 p-5">
        <p class="text-sm text-gray-400">Available</p>
        <p class="text-2xl font-bold text-white">${{ number_format($balance->available, 2) }}</p>
    </div>
    <div class="bg-gray-800 rounded-lg border border-gray-700 p-5">
        <p class="text-sm text-gray-400">Pending</p>
        <p class="text-2xl font-bold text-white">${{ number_format($balance->pending, 2) }}</p>
    </div>
    <div class="bg-gray-800 rounded-lg border border-gray-700 p-5">
        <p class="text-sm text-gray-400">Lifetime Earnings</p>
        <p class="text-2xl font-bold text-white">${{ number_format($balance->lifetime_earnings, 2) }}</p>
    </div>
</div>

<div class="bg-gray-800 rounded-lg border border-gray-700">
    <div class="px-4 py-3 border-b border-gray-700 text-white font-semibold">Ledger</div>
    <div class="divide-y divide-gray-700">
        @forelse($ledger as $entry)
            <div class="px-4 py-3 flex items-center justify-between">
                <div>
                    <div class="text-gray-300">{{ str_replace('_', ' ', ucfirst($entry->type)) }}</div>
                    <div class="text-xs text-gray-400">{{ $entry->created_at->format('M d, Y') }}</div>
                </div>
                <div class="text-sm text-gray-400">${{ number_format($entry->amount, 2) }}</div>
            </div>
        @empty
            <div class="px-4 py-6 text-gray-400">No ledger activity yet.</div>
        @endforelse
    </div>
</div>

<div class="mt-6">
    {{ $ledger->links() }}
</div>
@endsection

