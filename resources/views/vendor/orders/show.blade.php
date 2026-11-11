@extends('vendor.layout')

@section('title', 'Order Details - ' . $order->order_number)
@section('header', 'Order Details')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('vendor.orders.index') }}" class="text-gray-400 hover:text-white transition" aria-label="Back">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h3 class="text-2xl font-bold text-white">{{ $order->order_number }}</h3>
                <p class="text-gray-400 mt-1">Order placed on {{ $order->created_at->format('F d, Y \a\t h:i A') }}</p>
            </div>
        </div>
        @if($order->shipment)
            <a href="{{ route('vendor.shipments.show', $order->shipment) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg transition inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                </svg>
                View Shipment
            </a>
        @else
            <a href="{{ route('vendor.shipments.create', $order) }}" class="bg-orange-600 hover:bg-orange-700 text-white px-6 py-2 rounded-lg transition inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Create Shipment
            </a>
        @endif
    </div>

    @if(session('success'))
        <div class="bg-green-500/10 border border-green-500 text-green-500 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-500/10 border border-red-500 text-red-500 px-4 py-3 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
                <h4 class="text-lg font-semibold text-white mb-4">Order Items</h4>
                <div class="space-y-4">
                    @foreach($vendorItems as $item)
                        <div class="flex items-center gap-4 p-4 bg-gray-700/30 rounded-lg">
                            @if($item->product->images->first())
                                <img src="{{ '/storage/' . ltrim($item->product->images->first()->image_path ?? '', '/') }}" alt="{{ $item->product->name }}" class="w-16 h-16 object-cover rounded-lg">
                            @else
                                <div class="w-16 h-16 bg-gray-700 rounded-lg flex items-center justify-center">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            @endif
                            <div class="flex-1">
                                <h5 class="text-white font-medium">{{ $item->product->name }}</h5>
                                <p class="text-gray-400 text-sm">Quantity: {{ $item->quantity }}</p>
                                <p class="text-gray-400 text-sm">SKU: {{ $item->product->sku }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-white font-semibold">${{ number_format($item->price, 2) }}</p>
                                <p class="text-gray-400 text-sm">each</p>
                                <p class="text-orange-500 font-semibold mt-1">${{ number_format($item->price * $item->quantity, 2) }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-6 pt-6 border-t border-gray-700">
                    <div class="flex justify-between text-white">
                        <span class="font-semibold">Vendor Total</span>
                        <span class="text-xl font-bold text-orange-500">${{ number_format($vendorTotal, 2) }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
                <h4 class="text-lg font-semibold text-white mb-4">Order Notes</h4>
                <form method="POST" action="{{ route('vendor.orders.addNote', $order) }}" class="mb-6">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <textarea name="note" rows="3" required placeholder="Add a note about this order..." class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:border-orange-500"></textarea>
                        </div>
                        <div class="flex items-center gap-4">
                            <label class="flex items-center text-gray-300 cursor-pointer">
                                <input type="checkbox" name="is_customer_visible" value="1" class="mr-2 rounded bg-gray-700 border-gray-600 text-orange-600 focus:ring-orange-500">
                                <span class="text-sm">Visible to customer</span>
                            </label>
                            <button type="submit" class="ml-auto bg-orange-600 hover:bg-orange-700 text-white px-6 py-2 rounded-lg transition">
                                Add Note
                            </button>
                        </div>
                    </div>
                </form>

                <div class="space-y-3">
                    @forelse($order->notes as $note)
                        <div class="p-4 bg-gray-700/30 rounded-lg border-l-4 {{ $note->note_type === 'system' ? 'border-blue-500' : ($note->is_customer_visible ? 'border-green-500' : 'border-yellow-500') }}">
                            <div class="flex items-start justify-between mb-2">
                                <div class="flex items-center gap-2">
                                    <span class="text-white font-medium">{{ $note->user->name }}</span>
                                    @if($note->note_type === 'system')
                                        <span class="px-2 py-0.5 text-xs bg-blue-500/20 text-blue-500 rounded-full">System</span>
                                    @elseif($note->is_customer_visible)
                                        <span class="px-2 py-0.5 text-xs bg-green-500/20 text-green-500 rounded-full">Public</span>
                                    @else
                                        <span class="px-2 py-0.5 text-xs bg-yellow-500/20 text-yellow-500 rounded-full">Private</span>
                                    @endif
                                </div>
                                <span class="text-gray-400 text-sm">{{ $note->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-gray-300">{{ $note->note }}</p>
                        </div>
                    @empty
                        <p class="text-gray-400 text-center py-8">No notes yet</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
                <h4 class="text-lg font-semibold text-white mb-4">Update Status</h4>
                <form method="POST" action="{{ route('vendor.orders.updateStatus', $order) }}">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Order Status</label>
                            <select name="status" required class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-orange-500">
                                <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="processing" {{ $order->status == 'processing' ? 'selected' : '' }}>Processing</option>
                                <option value="shipped" {{ $order->status == 'shipped' ? 'selected' : '' }}>Shipped</option>
                                <option value="delivered" {{ $order->status == 'delivered' ? 'selected' : '' }}>Delivered</option>
                                <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        <button type="submit" class="w-full bg-orange-600 hover:bg-orange-700 text-white px-6 py-3 rounded-lg transition font-semibold">
                            Update Status
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
                <h4 class="text-lg font-semibold text-white mb-4">Customer Information</h4>
                <div class="space-y-3">
                    <div>
                        <p class="text-gray-400 text-sm">Name</p>
                        <p class="text-white">{{ $order->user->name }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-sm">Email</p>
                        <p class="text-white">{{ $order->user->email }}</p>
                    </div>
                    @if($order->user->phone)
                        <div>
                            <p class="text-gray-400 text-sm">Phone</p>
                            <p class="text-white">{{ $order->user->phone }}</p>
                        </div>
                    @endif
                </div>
            </div>

            @if($order->shippingAddress)
                <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
                    <h4 class="text-lg font-semibold text-white mb-4">Shipping Address</h4>
                    <div class="text-gray-300">
                        <p>{{ $order->shippingAddress->address_line1 }}</p>
                        @if($order->shippingAddress->address_line2)
                            <p>{{ $order->shippingAddress->address_line2 }}</p>
                        @endif
                        <p>{{ $order->shippingAddress->city }}, {{ $order->shippingAddress->state }} {{ $order->shippingAddress->postal_code }}</p>
                        <p>{{ $order->shippingAddress->country }}</p>
                        @if($order->shippingAddress->phone)
                            <p class="mt-2">Phone: {{ $order->shippingAddress->phone }}</p>
                        @endif
                    </div>
                </div>
            @endif

            <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
                <h4 class="text-lg font-semibold text-white mb-4">Order Summary</h4>
                <div class="space-y-2">
                    <div class="flex justify-between text-gray-300">
                        <span>Payment Status</span>
                        @php
                            $paymentColors = [
                                'pending' => 'text-yellow-500',
                                'paid' => 'text-green-500',
                                'failed' => 'text-red-500',
                                'refunded' => 'text-gray-400',
                            ];
                        @endphp
                        <span class="font-semibold {{ $paymentColors[$order->payment_status] ?? '' }}">
                            {{ ucfirst($order->payment_status) }}
                        </span>
                    </div>
                    <div class="flex justify-between text-gray-300">
                        <span>Payment Method</span>
                        <span class="font-semibold">{{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</span>
                    </div>
                    @if($order->shipped_at)
                        <div class="flex justify-between text-gray-300">
                            <span>Shipped At</span>
                            <span>{{ $order->shipped_at->format('M d, Y') }}</span>
                        </div>
                    @endif
                    @if($order->delivered_at)
                        <div class="flex justify-between text-gray-300">
                            <span>Delivered At</span>
                            <span>{{ $order->delivered_at->format('M d, Y') }}</span>
                        </div>
                    @endif
                    @if($order->customer_notes)
                        <div class="pt-3 mt-3 border-t border-gray-700">
                            <p class="text-gray-400 text-sm mb-1">Customer Notes</p>
                            <p class="text-gray-300 text-sm">{{ $order->customer_notes }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

