@extends('admin.layout')

@section('title', 'Edit Order')
@section('header', 'Edit Order: #' . $order->order_number)

@section('content')
<div class="max-w-4xl">
    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6 mb-6">
        <h2 class="text-xl font-bold text-white mb-4">Order Information</h2>
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-gray-400">Customer</p>
                <p class="text-white font-medium">{{ $order->user->name }}</p>
                <p class="text-gray-400">{{ $order->user->email }}</p>
            </div>
            <div>
                <p class="text-gray-400">Order Date</p>
                <p class="text-white font-medium">{{ $order->created_at->format('F d, Y H:i') }}</p>
            </div>
            <div>
                <p class="text-gray-400">Total Amount</p>
                <p class="text-white font-medium text-lg">${{ number_format($order->total, 2) }}</p>
            </div>
            <div>
                <p class="text-gray-400">Payment Status</p>
                <p class="text-white font-medium">{{ ucfirst($order->payment_status) }}</p>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.orders.update', $order) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h2 class="text-xl font-bold text-white mb-4">Update Order Status</h2>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Order Status *</label>
                    <select name="status" required class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                        <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>Processing</option>
                        <option value="shipped" {{ $order->status === 'shipped' ? 'selected' : '' }}>Shipped</option>
                        <option value="delivered" {{ $order->status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                        <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        <option value="refunded" {{ $order->status === 'refunded' ? 'selected' : '' }}>Refunded</option>
                    </select>
                    <p class="text-gray-400 text-xs mt-2">
                        ⚠️ Changing status to "Shipped" or "Delivered" will automatically send email notification to customer
                    </p>
                    @error('status') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Payment Status *</label>
                    <select name="payment_status" required class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                        <option value="pending" {{ $order->payment_status === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="paid" {{ $order->payment_status === 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="failed" {{ $order->payment_status === 'failed' ? 'selected' : '' }}>Failed</option>
                        <option value="refunded" {{ $order->payment_status === 'refunded' ? 'selected' : '' }}>Refunded</option>
                    </select>
                    @error('payment_status') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Tracking Number (Optional)</label>
                    <input type="text" name="tracking_number" value="{{ old('tracking_number', $order->tracking_number) }}"
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                           placeholder="e.g., 1Z999AA10123456784">
                    <p class="text-gray-400 text-xs mt-1">Add tracking number if status is "Shipped"</p>
                    @error('tracking_number') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Admin Notes</label>
                    <textarea name="admin_notes" rows="3" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">{{ old('admin_notes', $order->admin_notes) }}</textarea>
                    @error('admin_notes') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h2 class="text-xl font-bold text-white mb-4">Order Items</h2>
            <div class="space-y-3">
                @foreach($order->items as $item)
                    <div class="flex justify-between items-center p-3 bg-gray-900 rounded">
                        <div>
                            <p class="text-white font-medium">{{ $item->product_name }}</p>
                            <p class="text-gray-400 text-sm">Quantity: {{ $item->quantity }} × ${{ number_format($item->price, 2) }}</p>
                        </div>
                        <p class="text-white font-semibold">${{ number_format($item->total, 2) }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h2 class="text-xl font-bold text-white mb-4">Shipping Address</h2>
            <div class="bg-gray-900 p-4 rounded text-gray-300">
                <p class="font-medium text-white">{{ $order->shippingAddress->full_name }}</p>
                <p>{{ $order->shippingAddress->address_line1 }}</p>
                @if($order->shippingAddress->address_line2)
                    <p>{{ $order->shippingAddress->address_line2 }}</p>
                @endif
                <p>{{ $order->shippingAddress->city }}, {{ $order->shippingAddress->state }} {{ $order->shippingAddress->postal_code }}</p>
                <p>{{ $order->shippingAddress->country }}</p>
                <p class="mt-2">Phone: {{ $order->shippingAddress->phone }}</p>
            </div>
        </div>

        <div class="flex justify-end gap-4">
            <a href="{{ route('admin.orders.index') }}" class="bg-gray-700 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-medium">
                Cancel
            </a>
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-medium">
                Update Order Status
            </button>
        </div>
    </form>
</div>
@endsection

