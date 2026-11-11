@extends('vendor.layout')

@section('title', 'Create Shipment')
@section('header', 'Create Shipment')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('vendor.orders.show', $order) }}" class="text-gray-400 hover:text-white transition" aria-label="Back">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h3 class="text-2xl font-bold text-white">Create Shipment</h3>
            <p class="text-gray-400 mt-1">Order #{{ $order->order_number }}</p>
        </div>
    </div>

    @if($errors->any())
        <div class="bg-red-500/10 border border-red-500 rounded-lg p-4">
            <p class="text-red-500 font-semibold mb-2">Please fix the following errors:</p>
            <ul class="list-disc list-inside text-red-400">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
        <h4 class="text-lg font-semibold text-white mb-4">Order Information</h4>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <p class="text-gray-400 text-sm">Customer</p>
                <p class="text-white">{{ $order->user->name }}</p>
                <p class="text-gray-400 text-sm">{{ $order->user->email }}</p>
            </div>
            <div>
                <p class="text-gray-400 text-sm">Order Total</p>
                <p class="text-white font-semibold text-xl">${{ number_format($order->total, 2) }}</p>
            </div>
            @if($order->shippingAddress)
                <div class="col-span-2">
                    <p class="text-gray-400 text-sm mb-2">Shipping Address</p>
                    <div class="bg-gray-700/30 rounded-lg p-3 text-gray-300">
                        <p>{{ $order->shippingAddress->address_line1 }}</p>
                        @if($order->shippingAddress->address_line2)
                            <p>{{ $order->shippingAddress->address_line2 }}</p>
                        @endif
                        <p>{{ $order->shippingAddress->city }}, {{ $order->shippingAddress->state }} {{ $order->shippingAddress->postal_code }}</p>
                        <p>{{ $order->shippingAddress->country }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <form method="POST" action="{{ route('vendor.shipments.store', $order) }}" class="space-y-6">
        @csrf

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h4 class="text-lg font-semibold text-white mb-4">Shipment Details</h4>
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Tracking Number *</label>
                        <input 
                            type="text" 
                            name="tracking_number" 
                            value="{{ old('tracking_number') }}" 
                            required 
                            placeholder="e.g., 1Z999AA10123456784"
                            class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:border-orange-500"
                        >
                        <p class="text-gray-400 text-xs mt-1">Unique tracking number from carrier</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Carrier *</label>
                        <select 
                            name="carrier" 
                            id="carrier-select"
                            required 
                            class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-orange-500"
                        >
                            <option value="">Select carrier...</option>
                            <option value="DHL" {{ old('carrier') == 'DHL' ? 'selected' : '' }}>DHL</option>
                            <option value="FedEx" {{ old('carrier') == 'FedEx' ? 'selected' : '' }}>FedEx</option>
                            <option value="UPS" {{ old('carrier') == 'UPS' ? 'selected' : '' }}>UPS</option>
                            <option value="USPS" {{ old('carrier') == 'USPS' ? 'selected' : '' }}>USPS</option>
                            <option value="TNT" {{ old('carrier') == 'TNT' ? 'selected' : '' }}>TNT</option>
                            <option value="Aramex" {{ old('carrier') == 'Aramex' ? 'selected' : '' }}>Aramex</option>
                            <option value="Other" {{ old('carrier') == 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                </div>

                <div id="custom-carrier" style="display: none;">
                    <label class="block text-sm font-medium text-gray-300 mb-2">Custom Carrier Name</label>
                    <input 
                        type="text" 
                        id="custom-carrier-input"
                        placeholder="Enter carrier name"
                        class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:border-orange-500"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Carrier Tracking URL (Optional)</label>
                    <input 
                        type="url" 
                        name="carrier_url" 
                        value="{{ old('carrier_url') }}" 
                        placeholder="https://example.com/track/..."
                        class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:border-orange-500"
                    >
                    <p class="text-gray-400 text-xs mt-1">Full URL where customers can track their shipment</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Estimated Delivery Date (Optional)</label>
                    <input 
                        type="date" 
                        name="estimated_delivery" 
                        value="{{ old('estimated_delivery') }}" 
                        min="{{ now()->addDay()->format('Y-m-d') }}"
                        class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-orange-500"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Delivery Notes (Optional)</label>
                    <textarea 
                        name="notes" 
                        rows="3" 
                        placeholder="Any special delivery instructions or notes..."
                        class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:border-orange-500"
                    >{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <div class="bg-blue-500/10 border border-blue-500 text-blue-400 px-4 py-3 rounded-lg">
            <div class="flex items-start">
                <svg class="w-5 h-5 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <p class="font-semibold">Important</p>
                    <p class="text-sm mt-1">Creating a shipment will automatically update the order status to "Shipped" and send an email notification to the customer with tracking information.</p>
                </div>
            </div>
        </div>

        <div class="flex gap-4">
            <button type="submit" class="bg-orange-600 hover:bg-orange-700 text-white px-8 py-3 rounded-lg transition font-semibold">
                Create Shipment
            </button>
            <a href="{{ route('vendor.orders.show', $order) }}" class="bg-gray-700 hover:bg-gray-600 text-white px-8 py-3 rounded-lg transition font-semibold">
                Cancel
            </a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const carrierSelect = document.getElementById('carrier-select');
    const customCarrierDiv = document.getElementById('custom-carrier');
    const customCarrierInput = document.getElementById('custom-carrier-input');

    const updateCarrierFieldState = function () {
        const usingCustomCarrier = carrierSelect.value === 'Other';
        customCarrierDiv.style.display = usingCustomCarrier ? 'block' : 'none';
        customCarrierInput.required = usingCustomCarrier;
        customCarrierInput.name = usingCustomCarrier ? 'carrier' : '';
        carrierSelect.name = usingCustomCarrier ? '' : 'carrier';
    };

    carrierSelect.addEventListener('change', updateCarrierFieldState);
    updateCarrierFieldState();
});
</script>
@endsection

