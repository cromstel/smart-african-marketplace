@extends('admin.layout')

@section('title', 'Orders')
@section('header', 'Orders Management')

@section('content')
<div class="mb-6">
    <h3 class="text-2xl font-bold text-white">All Orders</h3>
    <p class="text-gray-400 mt-1">Manage customer orders</p>
</div>

<div class="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden">
    <table class="min-w-full divide-y divide-gray-700">
        <thead class="bg-gray-900">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Order #</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Customer</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Total</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Payment</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Date</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-700">
            @forelse($orders as $order)
                <tr class="hover:bg-gray-750">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-white">{{ $order->order_number }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                        {{ $order->user->name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-white font-semibold">
                        ${{ number_format($order->total, 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($order->payment_status === 'paid')
                            <span class="px-2 py-1 text-xs rounded bg-green-900 text-green-300">Paid</span>
                        @elseif($order->payment_status === 'pending')
                            <span class="px-2 py-1 text-xs rounded bg-yellow-900 text-yellow-300">Pending</span>
                        @elseif($order->payment_status === 'failed')
                            <span class="px-2 py-1 text-xs rounded bg-red-900 text-red-300">Failed</span>
                        @else
                            <span class="px-2 py-1 text-xs rounded bg-gray-700 text-gray-400">{{ ucfirst($order->payment_status) }}</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($order->status === 'delivered')
                            <span class="px-2 py-1 text-xs rounded bg-green-900 text-green-300">Delivered</span>
                        @elseif($order->status === 'shipped')
                            <span class="px-2 py-1 text-xs rounded bg-blue-900 text-blue-300">Shipped</span>
                        @elseif($order->status === 'processing')
                            <span class="px-2 py-1 text-xs rounded bg-yellow-900 text-yellow-300">Processing</span>
                        @elseif($order->status === 'cancelled')
                            <span class="px-2 py-1 text-xs rounded bg-red-900 text-red-300">Cancelled</span>
                        @else
                            <span class="px-2 py-1 text-xs rounded bg-gray-700 text-gray-400">{{ ucfirst($order->status) }}</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                        {{ $order->created_at->format('M d, Y') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="{{ route('admin.orders.show', $order) }}" class="text-indigo-400 hover:text-indigo-300 mr-3">View</a>
                        <a href="{{ route('admin.orders.edit', $order) }}" class="text-blue-400 hover:text-blue-300">Edit</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                        No orders yet
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">
    {{ $orders->links() }}
</div>
@endsection

