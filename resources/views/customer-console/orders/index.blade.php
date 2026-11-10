@extends('layouts.customer-console')

@section('title', 'Orders - Customer Console')

@section('content')
<h2 class="text-lg font-semibold text-white mb-6">My Orders</h2>

<div class="bg-gray-800 border border-gray-700 rounded-lg p-6">
    <table class="w-full text-left">
        <thead>
            <tr class="border-b border-gray-700">
                <th class="py-3 px-4 text-gray-400 text-sm font-medium">Order #</th>
                <th class="py-3 px-4 text-gray-400 text-sm font-medium">Date</th>
                <th class="py-3 px-4 text-gray-400 text-sm font-medium">Total</th>
                <th class="py-3 px-4 text-gray-400 text-sm font-medium">Status</th>
                <th class="py-3 px-4 text-gray-400 text-sm font-medium">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $order)
            <tr class="border-b border-gray-700">
                <td class="py-3 px-4 text-white">#{{ $order->id }}</td>
                <td class="py-3 px-4 text-white">{{ $order->created_at }}</td>
                <td class="py-3 px-4 text-white">${{ number_format($order->total, 2) }}</td>
                <td class="py-3 px-4 text-white">
                    <span class="px-2 py-1 bg-yellow-900/30 text-yellow-400 rounded text-xs">{{ $order->status }}</span>
                </td>
                <td class="py-3 px-4">
                    <a href="{{ route('customer.orders.show', $order) }}" class="text-indigo-400 hover:text-indigo-300 text-sm">View</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="py-4 text-center text-gray-400">No orders found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
