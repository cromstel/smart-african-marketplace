@extends('admin.layout')

@section('title', 'Payouts')
@section('header', 'Payouts')

@section('content')
<div class="bg-gray-800 rounded-lg border border-gray-700">
    <div class="px-4 py-3 border-b border-gray-700 text-white font-semibold">Payout Queue</div>
    <div class="divide-y divide-gray-700">
        @forelse($payouts as $payout)
            <div class="px-4 py-3 flex items-center justify-between">
                <div>
                    <div class="text-gray-300">{{ $payout->vendor->business_name ?? 'Vendor' }} - ${{ number_format($payout->amount, 2) }}</div>
                    <div class="text-xs text-gray-400">{{ strtoupper($payout->method) }} · {{ $payout->created_at->format('M d, Y') }}</div>
                </div>
                <div class="text-sm text-gray-400">{{ ucfirst($payout->status) }}</div>
                <a href="{{ route('admin.payouts.show', $payout) }}" class="text-blue-400 hover:text-blue-300">View</a>
            </div>
        @empty
            <div class="px-4 py-6 text-gray-400">No payouts.</div>
        @endforelse
    </div>
</div>

<div class="mt-6">
    {{ $payouts->links() }}
</div>
@endsection

