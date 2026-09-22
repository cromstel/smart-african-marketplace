@extends('vendor.layout')

@section('title', 'Refund Requests')

@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-bold text-white">Refund Requests</h2>
    <p class="text-gray-400 mt-1">Review refund requests for your orders.</p>
</div>

<div class="bg-gray-800 rounded-lg border border-gray-700">
    <div class="divide-y divide-gray-700">
        @forelse($refunds as $refund)
            <div class="px-4 py-3 flex items-center justify-between">
                <div>
                    <div class="text-gray-300">Order #{{ $refund->order->order_number }}</div>
                    <div class="text-xs text-gray-400">{{ $refund->user->email }}</div>
                </div>
                <div class="text-sm text-gray-400">{{ ucfirst($refund->status) }}</div>
                <a href="{{ route('vendor.refunds.show', $refund) }}" class="text-blue-400 hover:text-blue-300">View</a>
            </div>
        @empty
            <div class="px-4 py-6 text-gray-400">No refund requests.</div>
        @endforelse
    </div>
</div>

<div class="mt-6">
    {{ $refunds->links() }}
</div>
@endsection

