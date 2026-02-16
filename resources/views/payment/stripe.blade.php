@extends('layouts.shop')

@section('title', 'Stripe Payment')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-3xl font-bold text-white mb-8">Stripe Payment</h1>

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

    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
        <form action="{{ route('payment.stripe.process', $order) }}" method="POST" id="payment-form">
            @csrf

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-300 mb-2">Card Details</label>
                <div id="card-element" class="bg-gray-700 border border-gray-600 rounded-lg px-4 py-3"></div>
                <div id="card-errors" class="text-red-400 text-sm mt-2"></div>
            </div>

            <button type="submit" id="submit-button" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-3 px-6 rounded-lg font-medium">
                Pay ${{ number_format($order->total, 2) }}
            </button>
        </form>
    </div>

    <div class="mt-6 text-center">
        <a href="{{ route('cart.index') }}" class="text-gray-400 hover:text-gray-300 text-sm">
            ← Back to Cart
        </a>
    </div>
</div>

<script src="https://js.stripe.com/v3/"></script>
<script>
    const stripe = Stripe('{{ $stripePublicKey }}');
    const elements = stripe.elements();
    const cardElement = elements.create('card', {
        style: {
            base: {
                color: '#fff',
                fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
                fontSmoothing: 'antialiased',
                fontSize: '16px',
                '::placeholder': {
                    color: '#9ca3af'
                }
            },
            invalid: {
                color: '#fa755a',
                iconColor: '#fa755a'
            }
        }
    });

    cardElement.mount('#card-element');

    cardElement.on('change', function(event) {
        const displayError = document.getElementById('card-errors');
        if (event.error) {
            displayError.textContent = event.error.message;
        } else {
            displayError.textContent = '';
        }
    });

    const form = document.getElementById('payment-form');
    const submitButton = document.getElementById('submit-button');

    form.addEventListener('submit', async function(event) {
        event.preventDefault();
        submitButton.disabled = true;
        submitButton.textContent = 'Processing...';

        const {token, error} = await stripe.createToken(cardElement);

        if (error) {
            const errorElement = document.getElementById('card-errors');
            errorElement.textContent = error.message;
            submitButton.disabled = false;
            submitButton.textContent = 'Pay ${{ number_format($order->total, 2) }}';
        } else {
            const hiddenInput = document.createElement('input');
            hiddenInput.setAttribute('type', 'hidden');
            hiddenInput.setAttribute('name', 'stripeToken');
            hiddenInput.setAttribute('value', token.id);
            form.appendChild(hiddenInput);
            form.submit();
        }
    });
</script>
@endsection
