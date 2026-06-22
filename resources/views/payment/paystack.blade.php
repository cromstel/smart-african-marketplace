@extends('layouts.shop')

@section('title', 'Paystack Payment')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h2 class="text-3xl font-bold text-white mb-8">Paystack Payment</h2>

    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6 mb-6">
        <h2 class="text-xl font-bold text-white mb-4">Order Summary</h2>
        <div class="space-y-2">
            <div class="flex justify-between text-gray-300">
                <span>Order Number</span>
                <span class="text-white font-medium">{{ $order->order_number }}</span>
            </div>
            <div class="flex justify-between text-gray-300">
                <span>Amount to Pay</span>
                <span class="text-white font-bold text-2xl">${{ number_format($order->total, 2) }}</span>
            </div>
        </div>
    </div>

    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6 text-center">
        <button id="paystack-button" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-3 px-6 rounded-lg font-medium">
            Pay with Paystack
        </button>
    </div>

    <div class="mt-6 text-center">
        <a href="{{ route('cart.index') }}" class="text-gray-400 hover:text-gray-300 text-sm">
            ← Back to Cart
        </a>
    </div>
</div>

<script src="https://js.paystack.co/v1/inline.js"></script>
<script>
    const paystackButton = document.getElementById('paystack-button');
    
    paystackButton.addEventListener('click', function() {
        const handler = PaystackPop.setup({
            key: '{{ $paystackPublicKey }}',
            email: '{{ auth()->user()->email }}',
            amount: {{ $order->total * 100 }},
            currency: 'USD',
            ref: 'PS-' + Math.floor((Math.random() * 1000000000) + 1),
            metadata: {
                order_id: {{ $order->id }},
                order_number: '{{ $order->order_number }}',
                custom_fields: [
                    {
                        display_name: "Order Number",
                        variable_name: "order_number",
                        value: "{{ $order->order_number }}"
                    }
                ]
            },
            callback: function(response) {
                window.location = '{{ route("payment.paystack.callback", $order) }}?reference=' + response.reference;
            },
            onClose: function() {
                alert('Payment window closed');
            }
        });
        handler.openIframe();
    });
</script>
@endsection

