@extends('layouts.shop')

@section('title', 'Pay with SWISH')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-lg mx-auto">
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h2 class="text-2xl font-bold text-white mb-6">Pay with SWISH</h2>

            @if(session('error'))
                <div class="mb-6 bg-red-900 border border-red-700 text-red-100 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            @if(session('success'))
                <div class="mb-6 bg-green-900 border border-green-700 text-green-100 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <div class="mb-6">
                <p class="text-gray-300 mb-2">Order Number:</p>
                <p class="text-white font-semibold text-lg">{{ $order->order_number }}</p>
            </div>

            <div class="mb-6">
                <p class="text-gray-300 mb-2">Amount:</p>
                <p class="text-white font-semibold text-lg">SEK {{ number_format($order->total, 2) }}</p>
            </div>

            @if(isset($qrCodeImage) && $qrCodeImage)
            <div class="mb-6 text-center">
                <p class="text-gray-300 mb-3">Scan this QR code with your Swish app:</p>
                <div class="inline-block bg-white p-4 rounded-lg">
                    <img src="{{ $qrCodeImage }}" 
                         alt="SWISH QR Code" 
                         class="w-48 h-48">
                </div>
            </div>
            @endif

            <form action="{{ route('payment.swish.process', $order) }}" method="POST">
                @csrf
                
                <div class="mb-4">
                    <label for="phone_number" class="block text-sm text-gray-300 mb-2">
                        Phone Number (Swedish mobile)
                    </label>
                    <input type="text" 
                           id="phone_number" 
                           name="phone_number" 
                           value="{{ old('phone_number') }}"
                           required
                           class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white"
                           placeholder="+46 70 123 45 67">
                    <p class="text-gray-400 text-sm mt-1">Enter the phone number linked to your Swish account</p>
                    @error('phone_number')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" 
                        class="w-full bg-green-600 hover:bg-green-700 text-white py-3 px-6 rounded-lg font-medium mb-4">
                    Pay with Swish
                </button>

                <a href="{{ route('checkout.index') }}" class="block text-center text-gray-400 hover:text-white">
                    Back to Checkout
                </a>
            </form>
        </div>

        <div class="mt-6 bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-white mb-3">How to pay with Swish:</h3>
            <ol class="list-decimal list-inside text-gray-300 space-y-2">
                <li>Enter your Swedish mobile number above</li>
                <li>Click "Pay with Swish"</li>
                <li>Open your Swish app on your phone</li>
                <li>Confirm the payment in the app</li>
                <li>Wait for confirmation on this page</li>
            </ol>
        </div>
    </div>
</div>

<script>
    // Poll for payment status every 3 seconds
    let statusInterval;
    
    function checkPaymentStatus() {
        fetch('{{ route("payment.swish.status", $order) }}')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'completed') {
                    clearInterval(statusInterval);
                    window.location.href = '{{ route("payment.success", $order) }}';
                } else if (data.status === 'failed' || data.status === 'cancelled') {
                    clearInterval(statusInterval);
                    alert(data.message);
                }
            })
            .catch(error => console.error('Error checking status:', error));
    }

    // Start polling after page load
    document.addEventListener('DOMContentLoaded', function() {
        statusInterval = setInterval(checkPaymentStatus, 3000);
    });
</script>
@endsection
