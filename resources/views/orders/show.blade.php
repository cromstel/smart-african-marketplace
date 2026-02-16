@extends('layouts.shop')

@section('title', 'Order #' . $order->order_number)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
        <a href="{{ route('orders.index') }}" class="text-indigo-400 hover:text-indigo-300 mb-4 inline-block">
            ← Back to Orders
        </a>
        <h1 class="text-3xl font-bold text-white">Order #{{ $order->order_number }}</h1>
        <p class="text-gray-400 mt-1">Placed on {{ $order->created_at->format('F d, Y \a\t g:i A') }}</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-white">Order Status</h2>
                    @if($order->status === 'delivered')
                        <span class="px-4 py-2 rounded bg-green-900 text-green-300 font-medium">Delivered</span>
                    @elseif($order->status === 'shipped')
                        <span class="px-4 py-2 rounded bg-blue-900 text-blue-300 font-medium">Shipped</span>
                    @elseif($order->status === 'processing')
                        <span class="px-4 py-2 rounded bg-yellow-900 text-yellow-300 font-medium">Processing</span>
                    @elseif($order->status === 'cancelled')
                        <span class="px-4 py-2 rounded bg-red-900 text-red-300 font-medium">Cancelled</span>
                    @else
                        <span class="px-4 py-2 rounded bg-gray-700 text-gray-400 font-medium">{{ ucfirst($order->status) }}</span>
                    @endif
                </div>

                <div class="space-y-4">
                    @if($order->status === 'delivered' && $order->delivered_at)
                        <div class="flex items-center text-green-400">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>Delivered on {{ $order->delivered_at->format('M d, Y') }}</span>
                        </div>
                    @endif

                    @if($order->shipped_at)
                        <div class="flex items-center text-blue-400">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                            </svg>
                            <span>Shipped on {{ $order->shipped_at->format('M d, Y') }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
                <h2 class="text-xl font-bold text-white mb-4">Order Items</h2>
                <div class="space-y-4">
                    @foreach($order->items as $item)
                        <div class="flex items-center gap-4 pb-4 border-b border-gray-700 last:border-0 last:pb-0">
                            <div class="w-20 h-20 bg-gray-700 rounded flex-shrink-0 flex items-center justify-center text-gray-500 text-xs">
                                Product Image
                            </div>
                            <div class="flex-1">
                                <h3 class="text-white font-medium">{{ $item->product_name }}</h3>
                                <p class="text-gray-400 text-sm">SKU: {{ $item->product_sku }}</p>
                                <p class="text-gray-400 text-sm">Quantity: {{ $item->quantity }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-white font-medium">${{ number_format($item->total, 2) }}</p>
                                <p class="text-gray-400 text-sm">${{ number_format($item->price, 2) }} each</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
                    <h2 class="text-lg font-bold text-white mb-3">Shipping Address</h2>
                    @if($order->shippingAddress)
                        <div class="text-gray-300 text-sm space-y-1">
                            <p class="font-medium text-white">{{ $order->shippingAddress->full_name }}</p>
                            <p>{{ $order->shippingAddress->address_line1 }}</p>
                            @if($order->shippingAddress->address_line2)
                                <p>{{ $order->shippingAddress->address_line2 }}</p>
                            @endif
                            <p>{{ $order->shippingAddress->city }}, {{ $order->shippingAddress->state }} {{ $order->shippingAddress->postal_code }}</p>
                            <p>{{ $order->shippingAddress->country }}</p>
                            <p>{{ $order->shippingAddress->phone }}</p>
                        </div>
                    @else
                        <p class="text-gray-400">No shipping address</p>
                    @endif
                </div>

                <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
                    <h2 class="text-lg font-bold text-white mb-3">Billing Address</h2>
                    @if($order->billingAddress)
                        <div class="text-gray-300 text-sm space-y-1">
                            <p class="font-medium text-white">{{ $order->billingAddress->full_name }}</p>
                            <p>{{ $order->billingAddress->address_line1 }}</p>
                            @if($order->billingAddress->address_line2)
                                <p>{{ $order->billingAddress->address_line2 }}</p>
                            @endif
                            <p>{{ $order->billingAddress->city }}, {{ $order->billingAddress->state }} {{ $order->billingAddress->postal_code }}</p>
                            <p>{{ $order->billingAddress->country }}</p>
                            <p>{{ $order->billingAddress->phone }}</p>
                        </div>
                    @else
                        <p class="text-gray-400">No billing address</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="lg:col-span-1">
            <div class="bg-gray-800 rounded-lg border border-gray-700 p-6 sticky top-4">
                <h2 class="text-xl font-bold text-white mb-4">Order Summary</h2>

                <div class="space-y-3 mb-4">
                    <div class="flex justify-between text-gray-300">
                        <span>Subtotal</span>
                        <span>${{ number_format($order->subtotal, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-gray-300">
                        <span>Tax</span>
                        <span>${{ number_format($order->tax, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-gray-300">
                        <span>Shipping</span>
                        <span>${{ number_format($order->shipping_cost, 2) }}</span>
                    </div>
                    @if($order->discount > 0)
                        <div class="flex justify-between text-green-400">
                            <span>Discount</span>
                            <span>-${{ number_format($order->discount, 2) }}</span>
                        </div>
                    @endif
                </div>

                <div class="border-t border-gray-700 pt-3 mb-6">
                    <div class="flex justify-between text-white text-2xl font-bold">
                        <span>Total</span>
                        <span>${{ number_format($order->total, 2) }}</span>
                    </div>
                </div>

                <div class="space-y-3">
                    <div>
                        <p class="text-gray-400 text-sm mb-1">Payment Method</p>
                        <p class="text-white font-medium">{{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</p>
                    </div>

                    <div>
                        <p class="text-gray-400 text-sm mb-1">Payment Status</p>
                        @if($order->payment_status === 'paid')
                            <span class="px-3 py-1 text-sm rounded bg-green-900 text-green-300">Paid</span>
                        @elseif($order->payment_status === 'pending')
                            <span class="px-3 py-1 text-sm rounded bg-yellow-900 text-yellow-300">Pending</span>
                        @else
                            <span class="px-3 py-1 text-sm rounded bg-red-900 text-red-300">{{ ucfirst($order->payment_status) }}</span>
                        @endif
                    </div>

                    @if($order->payment)
                        <div>
                            <p class="text-gray-400 text-sm mb-1">Transaction ID</p>
                            <p class="text-white text-sm font-mono">{{ $order->payment->transaction_id }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
