@extends('layouts.shop')

@section('title', 'My Orders')

@section('content')
<x-hero-section
    pageIdentifier="orders"
    fallbackTitle="My Orders"
    fallbackSubtitle="Track and manage your orders"
    fallbackImage="/images/hero-banner.avif"
    :animated="true"
/>

<div class="container mx-auto px-4 py-8">

    @if($orders->count() > 0)
        <div class="space-y-4">
            @foreach($orders as $order)
                @php
                    $orderItems = $order->is_parent && $order->group
                        ? $order->group->orders->where('is_parent', false)->flatMap->items
                        : $order->items;
                @endphp
                <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-xl font-bold text-white">Order #{{ $order->order_number }}</h3>
                            <p class="text-gray-400 text-sm">Placed on {{ $order->created_at->format('M d, Y') }}</p>
                        </div>
                        <div class="text-right">
                            <div class="text-2xl font-bold text-white">${{ number_format($order->total, 2) }}</div>
                            <div class="mt-1">
                                @if($order->status === 'delivered')
                                    <span class="px-3 py-1 text-sm rounded bg-green-900 text-green-300">Delivered</span>
                                @elseif($order->status === 'shipped')
                                    <span class="px-3 py-1 text-sm rounded bg-blue-900 text-blue-300">Shipped</span>
                                @elseif($order->status === 'processing')
                                    <span class="px-3 py-1 text-sm rounded bg-yellow-900 text-yellow-300">Processing</span>
                                @elseif($order->status === 'cancelled')
                                    <span class="px-3 py-1 text-sm rounded bg-red-900 text-red-300">Cancelled</span>
                                @else
                                    <span class="px-3 py-1 text-sm rounded bg-gray-700 text-gray-400">{{ ucfirst($order->status) }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-gray-700 pt-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <p class="text-gray-400 text-sm mb-1">Payment Method</p>
                                <p class="text-white">{{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</p>
                            </div>
                            <div>
                                <p class="text-gray-400 text-sm mb-1">Payment Status</p>
                                @if($order->payment_status === 'paid')
                                    <span class="px-2 py-1 text-xs rounded bg-green-900 text-green-300">Paid</span>
                                @else
                                    <span class="px-2 py-1 text-xs rounded bg-yellow-900 text-yellow-300">{{ ucfirst($order->payment_status) }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="mb-4">
                            <p class="text-gray-400 text-sm mb-2">Items ({{ $orderItems->count() }})</p>
                            <div class="space-y-2">
                                @foreach($orderItems->take(3) as $item)
                                    <div class="flex items-center gap-3 text-sm">
                                        <span class="text-gray-400">{{ $item->quantity }}x</span>
                                        <span class="text-white">{{ $item->product_name }}</span>
                                        <span class="text-gray-400 ml-auto">${{ number_format($item->total, 2) }}</span>
                                    </div>
                                @endforeach
                                @if($orderItems->count() > 3)
                                    <p class="text-gray-400 text-sm">And {{ $orderItems->count() - 3 }} more items...</p>
                                @endif
                            </div>
                        </div>

                        <a href="{{ route('orders.show', $order) }}" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded">
                            View Details
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $orders->links() }}
        </div>
    @else
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-12 text-center">
            <svg class="w-16 h-16 mx-auto text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <h2 class="text-2xl font-bold text-white mb-2">No orders yet</h2>
            <p class="text-gray-400 mb-6">Start shopping to place your first order</p>
            <a href="{{ route('shop.index') }}" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-medium">
                Start Shopping
            </a>
        </div>
    @endif
</div>
@endsection

