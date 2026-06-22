@extends('layouts.shop')

@section('title', 'Payment Failed')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="bg-gray-800 rounded-lg border border-gray-700 p-8 text-center">
        <div class="w-20 h-20 bg-red-900 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-12 h-12 text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </div>

        <h2 class="text-3xl font-bold text-white mb-2">Payment Failed</h2>
        <p class="text-gray-400 mb-8">We couldn't process your payment. Please try again or use a different payment method.</p>

        <div class="bg-gray-900 rounded-lg p-6 mb-8">
            <div class="text-sm">
                <p class="text-gray-400 mb-2">Order Number</p>
                <p class="text-white font-medium text-lg">{{ $order->order_number }}</p>
                <p class="text-gray-400 mt-4 mb-2">Amount</p>
                <p class="text-white font-medium text-xl">${{ number_format($order->total, 2) }}</p>
            </div>
        </div>

        <div class="space-x-4">
            <a href="{{ route('checkout.index') }}" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-medium">
                Try Again
            </a>
            <a href="{{ route('cart.index') }}" class="inline-block bg-gray-700 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-medium">
                Back to Cart
            </a>
        </div>
    </div>
</div>
@endsection

