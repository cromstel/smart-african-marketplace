@extends('layouts.shop')

@section('title', 'Checkout')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-3xl font-bold text-white mb-8">Checkout</h1>

    <form action="{{ route('checkout.store') }}" method="POST">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
                    <h2 class="text-xl font-bold text-white mb-4">Shipping Address</h2>

                    @if($addresses->where('type', 'shipping')->count() > 0 || $addresses->where('type', 'both')->count() > 0)
                        <div class="space-y-3">
                            @foreach($addresses->whereIn('type', ['shipping', 'both']) as $address)
                                <label class="flex items-start p-4 border-2 rounded-lg cursor-pointer {{ $address->is_default ? 'border-indigo-500 bg-indigo-900/20' : 'border-gray-600 hover:border-gray-500' }}">
                                    <input type="radio" name="shipping_address_id" value="{{ $address->id }}" 
                                           {{ $address->is_default ? 'checked' : '' }} required
                                           class="mt-1 text-indigo-600 focus:ring-indigo-500">
                                    <div class="ml-3 flex-1">
                                        <div class="text-white font-medium">{{ $address->full_name }}</div>
                                        <div class="text-gray-400 text-sm mt-1">{{ $address->full_address }}</div>
                                        <div class="text-gray-400 text-sm">{{ $address->phone }}</div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <p class="text-gray-400 mb-4">No shipping address found</p>
                            <a href="{{ route('addresses.create') }}" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded">
                                Add Address
                            </a>
                        </div>
                    @endif
                </div>

                <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
                    <h2 class="text-xl font-bold text-white mb-4">Billing Address</h2>

                    @if($addresses->where('type', 'billing')->count() > 0 || $addresses->where('type', 'both')->count() > 0)
                        <div class="space-y-3">
                            @foreach($addresses->whereIn('type', ['billing', 'both']) as $address)
                                <label class="flex items-start p-4 border-2 rounded-lg cursor-pointer {{ $address->is_default ? 'border-indigo-500 bg-indigo-900/20' : 'border-gray-600 hover:border-gray-500' }}">
                                    <input type="radio" name="billing_address_id" value="{{ $address->id }}" 
                                           {{ $address->is_default ? 'checked' : '' }} required
                                           class="mt-1 text-indigo-600 focus:ring-indigo-500">
                                    <div class="ml-3 flex-1">
                                        <div class="text-white font-medium">{{ $address->full_name }}</div>
                                        <div class="text-gray-400 text-sm mt-1">{{ $address->full_address }}</div>
                                        <div class="text-gray-400 text-sm">{{ $address->phone }}</div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <p class="text-gray-400 mb-4">No billing address found</p>
                            <a href="{{ route('addresses.create') }}" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded">
                                Add Address
                            </a>
                        </div>
                    @endif
                </div>

                <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
                    <h2 class="text-xl font-bold text-white mb-4">Payment Method</h2>

                    <div class="space-y-3">
                        <label class="flex items-center p-4 border-2 border-gray-600 rounded-lg cursor-pointer hover:border-gray-500">
                            <input type="radio" name="payment_method" value="stripe" required class="text-indigo-600 focus:ring-indigo-500">
                            <div class="ml-3">
                                <div class="text-white font-medium">Credit/Debit Card (Stripe)</div>
                                <div class="text-gray-400 text-sm">Pay securely with your card</div>
                            </div>
                        </label>

                        <label class="flex items-center p-4 border-2 border-gray-600 rounded-lg cursor-pointer hover:border-gray-500">
                            <input type="radio" name="payment_method" value="paystack" class="text-indigo-600 focus:ring-indigo-500">
                            <div class="ml-3">
                                <div class="text-white font-medium">Paystack</div>
                                <div class="text-gray-400 text-sm">Pay with Paystack (Cards, Bank Transfer, USSD)</div>
                            </div>
                        </label>

                        <label class="flex items-center p-4 border-2 border-gray-600 rounded-lg cursor-pointer hover:border-gray-500">
                            <input type="radio" name="payment_method" value="cash_on_delivery" class="text-indigo-600 focus:ring-indigo-500">
                            <div class="ml-3">
                                <div class="text-white font-medium">Cash on Delivery</div>
                                <div class="text-gray-400 text-sm">Pay when you receive your order</div>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
                    <h2 class="text-xl font-bold text-white mb-4">Order Notes (Optional)</h2>
                    <textarea name="customer_notes" rows="3" 
                              class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white"
                              placeholder="Special instructions for your order"></textarea>
                </div>
            </div>

            <div class="lg:col-span-1">
                <div class="bg-gray-800 rounded-lg border border-gray-700 p-6 sticky top-4">
                    <h2 class="text-xl font-bold text-white mb-4">Order Summary</h2>

                    <div class="space-y-3 mb-4">
                        @foreach($cart->items as $item)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-300">{{ $item->product->name }} x{{ $item->quantity }}</span>
                                <span class="text-white">${{ number_format($item->subtotal, 2) }}</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="border-t border-gray-700 pt-3 space-y-2 mb-4">
                        <div class="flex justify-between text-gray-300">
                            <span>Subtotal</span>
                            <span>${{ number_format($cart->subtotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-gray-300">
                            <span>Tax</span>
                            <span>${{ number_format($cart->tax, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-gray-300">
                            <span>Shipping</span>
                            <span>${{ number_format($cart->shipping, 2) }}</span>
                        </div>
                    </div>

                    <div class="border-t border-gray-700 pt-3 mb-6">
                        <div class="flex justify-between text-white text-xl font-bold">
                            <span>Total</span>
                            <span>${{ number_format($cart->total, 2) }}</span>
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-3 px-6 rounded-lg font-medium">
                        Place Order
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
