@extends('vendor.layout')

@section('title', 'Payouts')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold text-white">Payouts</h2>
        <p class="text-gray-400 mt-1">Request and track payouts.</p>
    </div>
    <a href="{{ route('vendor.payouts.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded">Request Payout</a>
</div>

<div class="bg-gray-800 rounded-lg border border-gray-700 p-5 mb-6">
    <p class="text-sm text-gray-400">Available Balance</p>
    <p class="text-2xl font-bold text-white">${{ number_format($balance->available, 2) }}</p>
</div>

<div class="bg-gray-800 rounded-lg border border-gray-700">
    <div class="divide-y divide-gray-700">
        @forelse($payouts as $payout)
            <div class="px-4 py-3 flex items-center justify-between">
                <div>
                    <div class="text-gray-300">{{ strtoupper($payout->method) }} - ${{ number_format($payout->amount, 2) }}</div>
                    <div class="text-xs text-gray-400">{{ $payout->created_at->format('M d, Y') }}</div>
                </div>
                <div class="text-sm text-gray-400">{{ ucfirst($payout->status) }}</div>
            </div>
        @empty
            <div class="px-4 py-6 text-gray-400">No payouts yet.</div>
        @endforelse
    </div>
</div>

<div class="mt-6">
    {{ $payouts->links() }}
</div>
@endsection

