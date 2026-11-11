@extends('layouts.app')

@section('title', 'Mobile Money Payment')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="mb-6">
            <a href="{{ route('orders.show', $order) }}" class="text-indigo-400 hover:text-indigo-300 text-sm">
                ← Back to Order
            </a>
        </div>

        <div class="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden">
            <div class="bg-gray-900 px-6 py-4 border-b border-gray-700">
                <h2 class="text-2xl font-bold text-white">Mobile Money Payment</h2>
                <p class="text-gray-400 text-sm mt-1">Order #{{ $order->order_number }}</p>
            </div>

            <div class="p-6 space-y-6">
                <div class="bg-indigo-900 border border-indigo-700 rounded-lg p-4">
                    <div class="flex items-start">
                        <svg class="w-6 h-6 text-indigo-300 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <p class="text-indigo-100 font-semibold mb-1">How Mobile Money Payment Works</p>
                            <ol class="text-indigo-200 text-sm space-y-1 list-decimal list-inside">
                                <li>Enter your mobile money phone number below</li>
                                <li>You'll receive a prompt on your phone to approve the payment</li>
                                <li>Enter your PIN to complete the transaction</li>
                                <li>Your order will be confirmed automatically</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-900 rounded-lg p-6">
                    <h2 class="text-lg font-bold text-white mb-4">Payment Summary</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-400">Subtotal</span>
                            <span class="text-white">${{ number_format($order->subtotal, 2) }}</span>
                        </div>
                        @if($order->tax > 0)
                            <div class="flex justify-between">
                                <span class="text-gray-400">Tax</span>
                                <span class="text-white">${{ number_format($order->tax, 2) }}</span>
                            </div>
                        @endif
                        @if($order->shipping_cost > 0)
                            <div class="flex justify-between">
                                <span class="text-gray-400">Shipping</span>
                                <span class="text-white">${{ number_format($order->shipping_cost, 2) }}</span>
                            </div>
                        @endif
                        @if($order->discount > 0)
                            <div class="flex justify-between">
                                <span class="text-gray-400">Discount</span>
                                <span class="text-green-400">-${{ number_format($order->discount, 2) }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between border-t border-gray-700 pt-3">
                            <span class="text-white font-bold">Total Amount</span>
                            <span class="text-2xl font-bold text-white">${{ number_format($order->total, 2) }}</span>
                        </div>
                    </div>
                </div>

                <form action="{{ route('payment.momo.process', $order) }}" method="POST" class="space-y-6">
                    @csrf

                    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
                        <h2 class="text-lg font-bold text-white mb-4">Enter Payment Details</h2>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                Provider *
                            </label>
                            <select name="provider" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2" required>
                                <option value="mtn" {{ old('provider', $provider ?? 'mtn') === 'mtn' ? 'selected' : '' }}>MTN MoMo</option>
                                <option value="mpesa" {{ old('provider', $provider ?? 'mtn') === 'mpesa' ? 'selected' : '' }}>M-Pesa</option>
                            </select>
                            @error('provider') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                Mobile Money Phone Number *
                            </label>
                            <div class="flex items-center gap-2">
                                <div class="flex items-center bg-gray-700 border border-gray-600 rounded-lg px-4 py-2">
                                    <span class="text-white">+</span>
                                </div>
                                <input type="tel" name="phone_number" value="{{ old('phone_number') }}" required 
                                       placeholder="2547XXXXXXXX (Country code + number)"
                                       pattern="[0-9]{10,15}"
                                       class="flex-1 bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                                       title="Enter your phone number with country code (e.g., 2547XXXXXXXX)">
                            </div>
                            <p class="text-xs text-gray-500 mt-2">
                                Enter your mobile money number with country code (e.g., 2547XXXXXXXX for Kenya, 233XXXXXXXXX for Ghana)
                            </p>
                            @error('phone_number') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="mt-6 bg-gray-900 rounded p-4">
                            <h3 class="text-white font-semibold mb-2 text-sm flex items-center">
                                <svg class="w-5 h-5 mr-2 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                Supported Networks
                            </h3>
                            <p class="text-gray-400 text-xs">
                                MTN Mobile Money, Airtel Money, M-Pesa, and other regional mobile money providers
                            </p>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-medium flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                            Pay ${{ number_format($order->total, 2) }}
                        </button>
                        <a href="{{ route('orders.show', $order) }}" class="bg-gray-700 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-medium">
                            Cancel
                        </a>
                    </div>
                </form>

                <div class="bg-yellow-900 border border-yellow-700 rounded-lg p-4">
                    <p class="text-yellow-100 text-sm">
                        <strong>Important:</strong> Keep your phone nearby. You will receive a payment prompt shortly after clicking "Pay". 
                        Make sure you have sufficient balance in your mobile money account.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

