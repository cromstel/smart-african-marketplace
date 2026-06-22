@extends('admin.layout')

@section('title', 'Order Details')
@section('header', 'Order #'.$order->order_number)

@section('content')
<div class="space-y-6">
    <div class="flex justify-end">
        <a href="{{ route('admin.orders.edit', $order) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg">
            Edit Order
        </a>
    </div>

    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-white mb-4">Summary</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
            <div>
                <p class="text-gray-400">Customer</p>
                <p class="text-white">{{ $order->user->name }}</p>
                <p class="text-gray-300">{{ $order->user->email }}</p>
            </div>
            <div>
                <p class="text-gray-400">Order Status</p>
                <p class="text-white">{{ ucfirst($order->status) }}</p>
            </div>
            <div>
                <p class="text-gray-400">Payment Status</p>
                <p class="text-white">{{ ucfirst($order->payment_status) }}</p>
            </div>
            <div>
                <p class="text-gray-400">Total</p>
                <p class="text-white font-semibold">${{ number_format($order->total, 2) }}</p>
            </div>
            <div>
                <p class="text-gray-400">Tracking Number</p>
                <p class="text-white">{{ $order->tracking_number ?: 'N/A' }}</p>
            </div>
            <div>
                <p class="text-gray-400">Created</p>
                <p class="text-white">{{ $order->created_at->format('M d, Y H:i') }}</p>
            </div>
        </div>
    </div>

    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-white mb-4">Order Items</h3>
        <div class="space-y-3">
            @php
                $adminItems = $order->items;
                if ($order->is_parent && $order->group) {
                    $adminItems = $order->group->orders->where('is_parent', false)->flatMap->items;
                }
            @endphp
            @foreach($adminItems as $item)
                <div class="flex justify-between items-center p-3 bg-gray-900 rounded">
                    <div>
                        <p class="text-white">{{ $item->product_name }}</p>
                        <p class="text-gray-400 text-sm">Qty: {{ $item->quantity }} x ${{ number_format($item->price, 2) }}</p>
                    </div>
                    <p class="text-white">${{ number_format($item->total, 2) }}</p>
                </div>
            @endforeach
        </div>
    </div>

    @if($order->shippingAddress)
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-white mb-4">Shipping Address</h3>
            <div class="text-gray-300">
                <p class="text-white font-medium">{{ $order->shippingAddress->full_name }}</p>
                <p>{{ $order->shippingAddress->address_line1 }}</p>
                @if($order->shippingAddress->address_line2)
                    <p>{{ $order->shippingAddress->address_line2 }}</p>
                @endif
                <p>{{ $order->shippingAddress->city }}, {{ $order->shippingAddress->state }} {{ $order->shippingAddress->postal_code }}</p>
                <p>{{ $order->shippingAddress->country }}</p>
                <p class="mt-1">Phone: {{ $order->shippingAddress->phone }}</p>
            </div>
        </div>
    @endif
</div>
@endsection

