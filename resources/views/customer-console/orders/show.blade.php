@extends('layouts.customer-console')

@section('title', 'Order Details - Customer Console')

@section('content')
<div class="max-w-2xl mx-auto">
    <h2 class="text-lg font-semibold text-white mb-6">Order Details</h2>

    <div class="bg-gray-800 border border-gray-700 rounded-lg p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <p class="text-sm text-gray-400">Order #</p>
                <p class="text-white">#{{ $order->id }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-400">Date</p>
                <p class="text-white">{{ $order->created_at }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-400">Total</p>
                <p class="text-white">${{ number_format($order->total, 2) }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-400">Status</p>
                <span class="px-2 py-1 bg-yellow-900/30 text-yellow-400 rounded text-xs">{{ $order->status }}</span>
            </div>
        </div>
    </div>
</div>
@endsection
