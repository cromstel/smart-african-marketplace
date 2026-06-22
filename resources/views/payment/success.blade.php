@extends('layouts.shop')

@section('title', 'Payment Successful')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="bg-gray-800 rounded-lg border border-gray-700 p-8 text-center">
        <div class="w-20 h-20 bg-green-900 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-12 h-12 text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>

        <h2 class="text-3xl font-bold text-white mb-2">Payment Successful!</h2>
        <p class="text-gray-400 mb-8">Thank you for your order. Your payment has been processed successfully.</p>

        <div class="bg-gray-900 rounded-lg p-6 mb-8">
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div class="text-left">
                    <p class="text-gray-400">Order Number</p>
                    <p class="text-white font-medium">{{ $order->order_number }}</p>
                </div>
                <div class="text-right">
                    <p class="text-gray-400">Amount Paid</p>
                    <p class="text-white font-medium text-xl">${{ number_format($order->total, 2) }}</p>
                </div>
                <div class="text-left">
                    <p class="text-gray-400">Payment Method</p>
                    <p class="text-white font-medium">{{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</p>
                </div>
                <div class="text-right">
                    <p class="text-gray-400">Status</p>
                    <p class="text-green-400 font-medium">Paid</p>
                </div>
            </div>
        </div>

        <div class="space-x-4">
            <a href="{{ route('orders.show', $order) }}" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-medium">
                View Order Details
            </a>
            <a href="{{ route('shop.index') }}" class="inline-block bg-gray-700 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-medium">
                Continue Shopping
            </a>
        </div>
    </div>
</div>
@endsection

