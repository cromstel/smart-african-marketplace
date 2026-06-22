@extends('admin.layout')

@section('title', 'Refund Requests')
@section('header', 'Refund Requests')

@section('content')
<div class="bg-gray-800 rounded-lg border border-gray-700">
    <div class="px-4 py-3 border-b border-gray-700 text-white font-semibold">Refund Requests</div>
    <div class="divide-y divide-gray-700">
        @forelse($refunds as $refund)
            <div class="px-4 py-3 flex items-center justify-between">
                <div>
                    <div class="text-gray-300">Order #{{ $refund->order->order_number }}</div>
                    <div class="text-xs text-gray-500">{{ $refund->user->email }}</div>
                </div>
                <div class="text-sm text-gray-400">{{ ucfirst($refund->status) }}</div>
                <a href="{{ route('admin.refunds.show', $refund) }}" class="text-blue-400 hover:text-blue-300">View</a>
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

