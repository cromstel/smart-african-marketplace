@extends('admin.layout')

@section('title', 'Payout Details')
@section('header', 'Payout Details')

@section('content')
<div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
    <h3 class="text-xl font-semibold text-white mb-2">{{ $payout->vendor->business_name ?? 'Vendor' }}</h3>
    <p class="text-gray-400">Amount: ${{ number_format($payout->amount, 2) }}</p>
    <p class="text-gray-400">Method: {{ strtoupper($payout->method) }}</p>
    <p class="text-gray-400">Status: {{ ucfirst($payout->status) }}</p>

    <div class="mt-4 flex gap-3">
        @if(in_array($payout->status, ['pending', 'processing']))
            <form method="POST" action="{{ route('admin.payouts.process', $payout) }}">
                @csrf
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">Process</button>
            </form>
            <form method="POST" action="{{ route('admin.payouts.fail', $payout) }}">
                @csrf
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded">Mark Failed</button>
            </form>
        @endif
    </div>
</div>
@endsection

