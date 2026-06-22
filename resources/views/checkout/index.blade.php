@extends('layouts.shop')

@section('title', 'Checkout')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h2 class="text-3xl font-bold text-white mb-8">Checkout</h2>

    @if(session('error'))
        <div class="mb-6 bg-red-900 border border-red-700 text-red-100 px-4 py-3 rounded">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 bg-red-900 border border-red-700 text-red-100 px-4 py-3 rounded">
            <ul class="list-disc pl-5 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

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
                                           {{ (string) old('shipping_address_id') === (string) $address->id ? 'checked' : ($address->is_default ? 'checked' : '') }} required
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
                                           {{ (string) old('billing_address_id') === (string) $address->id ? 'checked' : ($address->is_default ? 'checked' : '') }} required
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
                    <h2 class="text-xl font-bold text-white mb-4">Shipping Method</h2>
                    <label class="flex items-center p-4 border-2 border-gray-600 rounded-lg cursor-pointer hover:border-gray-500">
                        <input type="radio" name="shipping_method" value="standard" checked class="text-indigo-600 focus:ring-indigo-500">
                        <div class="ml-3">
                            <div class="text-white font-medium" id="shipping-method-label">{{ $shippingPreview['label'] ?? 'Standard Shipping' }}</div>
                            <div class="text-gray-400 text-sm">Cost: <span id="shipping-method-cost">${{ number_format($shippingPreview['shipping'] ?? 0, 2) }}</span></div>
                        </div>
                    </label>
                </div>

                <div class="bg-gray-800 rounded-lg border border-gray-700 p-6" x-data="{ method: '{{ old('payment_method', '') }}' }">
                    <h2 class="text-xl font-bold text-white mb-4">Payment Method</h2>

                    <div class="space-y-3">
                        <label class="flex items-center p-4 border-2 border-gray-600 rounded-lg cursor-pointer hover:border-gray-500">
                            <input type="radio" name="payment_method" value="stripe" x-model="method" required class="text-indigo-600 focus:ring-indigo-500">
                            <div class="ml-3">
                                <div class="text-white font-medium">Credit/Debit Card (Stripe)</div>
                                <div class="text-gray-400 text-sm">Pay securely with your card</div>
                            </div>
                        </label>

                        <label class="flex items-center p-4 border-2 border-gray-600 rounded-lg cursor-pointer hover:border-gray-500">
                            <input type="radio" name="payment_method" value="paystack" x-model="method" class="text-indigo-600 focus:ring-indigo-500">
                            <div class="ml-3">
                                <div class="text-white font-medium">Paystack</div>
                                <div class="text-gray-400 text-sm">Pay with Paystack (Cards, Bank Transfer, USSD)</div>
                            </div>
                        </label>

                        <label class="flex items-center p-4 border-2 border-gray-600 rounded-lg cursor-pointer hover:border-gray-500">
                            <input type="radio" name="payment_method" value="mobile_money" x-model="method" class="text-indigo-600 focus:ring-indigo-500">
                            <div class="ml-3">
                                <div class="text-white font-medium">Mobile Money</div>
                                <div class="text-gray-400 text-sm">Pay with mobile money</div>
                            </div>
                        </label>

                        <label class="flex items-center p-4 border-2 border-gray-600 rounded-lg cursor-pointer hover:border-gray-500">
                            <input type="radio" name="payment_method" value="cash_on_delivery" x-model="method" class="text-indigo-600 focus:ring-indigo-500">
                            <div class="ml-3">
                                <div class="text-white font-medium">Cash on Delivery</div>
                                <div class="text-gray-400 text-sm">Pay when you receive your order</div>
                            </div>
                        </label>

                        @if($isNordicCountry ?? false)
                        <label class="flex items-center p-4 border-2 border-green-600 rounded-lg cursor-pointer hover:border-green-500">
                            <input type="radio" name="payment_method" value="swish" x-model="method" class="text-green-600 focus:ring-green-500">
                            <div class="ml-3">
                                <div class="text-white font-medium">SWISH</div>
                                <div class="text-gray-400 text-sm">Pay instantly with Swish (Sweden, Norway, Denmark, Finland)</div>
                            </div>
                        </label>
                        @endif
                    </div>

                    <div class="mt-4" x-show="method === 'mobile_money'">
                        <label class="block text-sm text-gray-300 mb-1" for="mobile_money_provider">Mobile Money Provider</label>
                        <select id="mobile_money_provider" name="mobile_money_provider" x-bind:required="method === 'mobile_money'" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white mb-3">
                            <option value="mtn" {{ old('mobile_money_provider', 'mtn') === 'mtn' ? 'selected' : '' }}>MTN MoMo</option>
                            <option value="mpesa" {{ old('mobile_money_provider') === 'mpesa' ? 'selected' : '' }}>M-Pesa</option>
                        </select>
                        <label class="block text-sm text-gray-300 mb-1" for="phone_number">Mobile Money Phone Number</label>
                        <input type="text" id="phone_number" name="phone_number" value="{{ old('phone_number') }}"
                               x-bind:required="method === 'mobile_money'"
                               class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white"
                               placeholder="e.g. +233XXXXXXXXX">
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
                            <span id="summary-subtotal">${{ number_format($shippingPreview['subtotal'] ?? $cart->subtotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-gray-300">
                            <span>Tax</span>
                            <span id="summary-tax">${{ number_format($shippingPreview['tax'] ?? $cart->tax, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-gray-300">
                            <span>Shipping</span>
                            <span id="summary-shipping">${{ number_format($shippingPreview['shipping'] ?? $cart->shipping, 2) }}</span>
                        </div>
                        @if(($shippingPreview['discount'] ?? $cart->discount) > 0)
                            <div class="flex justify-between text-green-400">
                                <span>Discount</span>
                                <span id="summary-discount">-${{ number_format($shippingPreview['discount'] ?? $cart->discount, 2) }}</span>
                            </div>
                        @endif
                    </div>

                    <div class="border-t border-gray-700 pt-3 mb-6">
                        <div class="flex justify-between text-white text-xl font-bold">
                            <span>Total</span>
                            <span id="summary-total">${{ number_format($shippingPreview['total'] ?? $cart->total, 2) }}</span>
                        </div>
                        <p class="text-xs text-gray-400 mt-2">Shipping is calculated from your selected delivery address and current shipping rules.</p>
                    </div>

                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-3 px-6 rounded-lg font-medium">
                        Place Order
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    const shippingRadios = document.querySelectorAll('input[name="shipping_address_id"]');
    const shippingEndpoint = "{{ route('checkout.shipping-preview') }}";

    function formatMoney(value) {
        const amount = Number(value || 0);
        return `$${amount.toFixed(2)}`;
    }

    function updateShippingPreview(addressId) {
        fetch(`${shippingEndpoint}?address_id=${encodeURIComponent(addressId)}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then((response) => response.ok ? response.json() : null)
            .then((data) => {
                if (!data) {
                    return;
                }
                const shippingLabel = document.getElementById('shipping-method-label');
                const shippingCost = document.getElementById('shipping-method-cost');
                const summaryShipping = document.getElementById('summary-shipping');
                const summaryTax = document.getElementById('summary-tax');
                const summaryTotal = document.getElementById('summary-total');

                if (shippingLabel) {
                    shippingLabel.textContent = data.label || 'Standard Shipping';
                }
                if (shippingCost) {
                    shippingCost.textContent = formatMoney(data.shipping);
                }
                if (summaryShipping) {
                    summaryShipping.textContent = formatMoney(data.shipping);
                }
                if (summaryTax) {
                    summaryTax.textContent = formatMoney(data.tax);
                }
                if (summaryTotal) {
                    summaryTotal.textContent = formatMoney(data.total);
                }
            })
            .catch(() => {});
    }

    shippingRadios.forEach((radio) => {
        radio.addEventListener('change', () => {
            if (radio.checked) {
                updateShippingPreview(radio.value);
            }
        });
    });
</script>
@endsection

