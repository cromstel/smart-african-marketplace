@extends('vendor.layout')

@section('title', 'Shipment Details')
@section('header', 'Shipment Details')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('vendor.orders.show', $shipment->order) }}" class="text-gray-400 hover:text-white transition" aria-label="Back">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h2 class="text-2xl font-bold text-white">Shipment for Order #{{ $shipment->order->order_number }}</h2>
            <p class="text-gray-400 mt-1">Tracking Number: {{ $shipment->tracking_number }}</p>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-500/10 border border-green-500 text-green-400 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Tracking Timeline</h3>
                <div class="space-y-4">
                    @forelse(($shipment->tracking_history ?? []) as $entry)
                        <div class="p-4 bg-gray-700/30 rounded-lg border border-gray-700">
                            <div class="flex items-center justify-between gap-3">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-500/20 text-indigo-300">
                                    {{ ucwords(str_replace('_', ' ', $entry['status'] ?? 'update')) }}
                                </span>
                                <span class="text-xs text-gray-400">
                                    {{ \Illuminate\Support\Carbon::parse($entry['timestamp'] ?? now())->format('M d, Y H:i') }}
                                </span>
                            </div>
                            <p class="text-gray-200 mt-2">{{ $entry['location'] ?? 'Unknown location' }}</p>
                            @if(!empty($entry['notes']))
                                <p class="text-sm text-gray-400 mt-1">{{ $entry['notes'] }}</p>
                            @endif
                        </div>
                    @empty
                        <p class="text-gray-400">No tracking updates available yet.</p>
                    @endforelse
                </div>
            </div>

            @if($shipment->status !== 'delivered')
                <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-white mb-4">Add Tracking Update</h3>
                    <form method="POST" action="{{ route('vendor.shipments.tracking', $shipment) }}" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Status</label>
                                <select name="status" required class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-orange-500">
                                    @foreach(['pending', 'in_transit', 'out_for_delivery', 'delivered', 'failed', 'returned'] as $status)
                                        <option value="{{ $status }}" {{ old('status', $shipment->status) === $status ? 'selected' : '' }}>
                                            {{ ucwords(str_replace('_', ' ', $status)) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Current Location</label>
                                <input type="text" name="location" value="{{ old('location', $shipment->current_location) }}" required
                                       class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-orange-500"
                                       placeholder="City, warehouse, or checkpoint">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Notes (Optional)</label>
                            <textarea name="notes" rows="3" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-orange-500" placeholder="Additional context for customer">{{ old('notes') }}</textarea>
                        </div>
                        <button type="submit" class="bg-orange-600 hover:bg-orange-700 text-white px-6 py-2 rounded-lg transition font-semibold">
                            Save Update
                        </button>
                    </form>
                </div>
            @endif
        </div>

        <div class="space-y-6">
            <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Shipment Summary</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-400">Carrier</span>
                        <span class="text-white">{{ $shipment->carrier ?: 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Status</span>
                        <span class="text-white">{{ ucwords(str_replace('_', ' ', $shipment->status)) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Shipped At</span>
                        <span class="text-white">{{ optional($shipment->shipped_at)->format('M d, Y') ?: 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">ETA</span>
                        <span class="text-white">{{ optional($shipment->estimated_delivery)->format('M d, Y') ?: 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Delivered At</span>
                        <span class="text-white">{{ optional($shipment->delivered_at)->format('M d, Y H:i') ?: 'Not delivered' }}</span>
                    </div>
                </div>

                @if($shipment->carrier_url)
                    <a href="{{ $shipment->carrier_url }}" target="_blank" rel="noopener noreferrer"
                       class="mt-4 inline-flex items-center text-orange-400 hover:text-orange-300 transition">
                        Open carrier tracking page
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 3h7m0 0v7m0-7L10 14"/>
                        </svg>
                    </a>
                @endif

                <div class="mt-4">
                    @if($shipment->label_url)
                        <a href="{{ $shipment->label_url }}" target="_blank" class="inline-flex items-center text-indigo-400 hover:text-indigo-300">
                            View Shipping Label
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 3h7m0 0v7m0-7L10 14"/>
                            </svg>
                        </a>
                    @else
                        <form method="POST" action="{{ route('vendor.shipments.label', $shipment) }}">
                            @csrf
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded">Generate Label</button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Customer</h3>
                <p class="text-white">{{ $shipment->order->user->name }}</p>
                <p class="text-gray-400 text-sm">{{ $shipment->order->user->email }}</p>

                @if($shipment->order->shippingAddress)
                    <div class="mt-4 text-sm text-gray-300">
                        <p>{{ $shipment->order->shippingAddress->address_line1 }}</p>
                        @if($shipment->order->shippingAddress->address_line2)
                            <p>{{ $shipment->order->shippingAddress->address_line2 }}</p>
                        @endif
                        <p>{{ $shipment->order->shippingAddress->city }}, {{ $shipment->order->shippingAddress->state }} {{ $shipment->order->shippingAddress->postal_code }}</p>
                        <p>{{ $shipment->order->shippingAddress->country }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

