@extends('admin.layout')

@section('title', 'Vendor Details')
@section('header', 'Vendor Details')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div>
        <h3 class="text-2xl font-bold text-white">{{ $vendor->business_name }}</h3>
        <p class="text-gray-400 mt-1">Joined {{ $vendor->created_at->format('F d, Y') }}</p>
    </div>
    <div class="flex gap-3">
        <a href="{{ route('admin.vendors.edit', $vendor) }}" class="bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
            Edit Vendor
        </a>
        <a href="{{ route('admin.vendors.ledger', $vendor) }}" class="bg-indigo-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-indigo-700 transition">
            View Ledger
        </a>
        <a href="{{ route('admin.vendors.index') }}" class="bg-gray-700 text-white px-6 py-3 rounded-lg font-semibold hover:bg-gray-600 transition">
            Back to List
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
        <div class="flex items-center justify-between mb-4">
            <div class="bg-blue-600 rounded-lg p-3">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
        </div>
        <p class="text-gray-400 text-sm mb-2">Total Products</p>
        <p class="text-3xl font-bold text-white">{{ $stats['total_products'] }}</p>
        <p class="text-green-400 text-sm mt-2">{{ $stats['active_products'] }} active</p>
    </div>

    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
        <div class="flex items-center justify-between mb-4">
            <div class="bg-green-600 rounded-lg p-3">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
        </div>
        <p class="text-gray-400 text-sm mb-2">Total Orders</p>
        <p class="text-3xl font-bold text-white">{{ $stats['total_orders'] }}</p>
    </div>

    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
        <div class="flex items-center justify-between mb-4">
            <div class="bg-orange-600 rounded-lg p-3">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        <p class="text-gray-400 text-sm mb-2">Total Sales</p>
        <p class="text-3xl font-bold text-white">${{ number_format($stats['total_sales'], 2) }}</p>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
        <div class="flex items-center justify-between mb-4">
            <div class="bg-purple-600 rounded-lg p-3">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </div>
        </div>
        <p class="text-gray-400 text-sm mb-2">Total Bookings</p>
        <p class="text-3xl font-bold text-white">{{ $stats['total_bookings'] }}</p>
    </div>

    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
        <div class="flex items-center justify-between mb-4">
            <div class="bg-yellow-600 rounded-lg p-3">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </div>
        </div>
        <p class="text-gray-400 text-sm mb-2">Confirmed Bookings</p>
        <p class="text-3xl font-bold text-white">{{ $stats['confirmed_bookings'] }}</p>
    </div>

    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
        <div class="flex items-center justify-between mb-4">
            <div class="bg-cyan-600 rounded-lg p-3">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        <p class="text-gray-400 text-sm mb-2">Booking Revenue</p>
        <p class="text-3xl font-bold text-white">${{ number_format($stats['booking_revenue'], 2) }}</p>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
        <p class="text-gray-400 text-sm mb-2">Cancellation Rate</p>
        <p class="text-3xl font-bold text-white">{{ number_format($stats['cancellation_rate'], 1) }}%</p>
    </div>
    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
        <p class="text-gray-400 text-sm mb-2">Avg Fulfillment (Days)</p>
        <p class="text-3xl font-bold text-white">{{ number_format($stats['avg_fulfillment_days'], 1) }}</p>
    </div>
    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
        <p class="text-gray-400 text-sm mb-2">Late Shipments</p>
        <p class="text-3xl font-bold text-white">{{ $stats['late_shipments'] }}</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
        <h4 class="text-lg font-semibold text-white mb-6 flex items-center">
            <svg class="w-5 h-5 mr-2 text-brand-from" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            Account Information
        </h4>
        <div class="space-y-4">
            <div>
                <p class="text-gray-400 text-sm">Owner Name</p>
                <p class="text-white font-medium">{{ $vendor->user->name }}</p>
            </div>
            <div>
                <p class="text-gray-400 text-sm">Email</p>
                <p class="text-white font-medium">{{ $vendor->user->email }}</p>
            </div>
            <div>
                <p class="text-gray-400 text-sm">Status</p>
                <div class="mt-1">
                    @if($vendor->status === 'approved')
                        <span class="px-3 py-1 text-xs rounded-full bg-green-900 text-green-300 font-semibold">Approved</span>
                    @elseif($vendor->status === 'pending')
                        <span class="px-3 py-1 text-xs rounded-full bg-yellow-900 text-yellow-300 font-semibold">Pending</span>
                    @elseif($vendor->status === 'suspended')
                        <span class="px-3 py-1 text-xs rounded-full bg-red-900 text-red-300 font-semibold">Suspended</span>
                    @else
                        <span class="px-3 py-1 text-xs rounded-full bg-gray-700 text-gray-400 font-semibold">{{ ucfirst($vendor->status) }}</span>
                    @endif
                </div>
            </div>
            <div>
                <p class="text-gray-400 text-sm">Commission Rate</p>
                <p class="text-white font-medium">{{ number_format($vendor->commission_rate, 2) }}%</p>
            </div>
            @if($vendor->approved_at)
            <div>
                <p class="text-gray-400 text-sm">Approved Date</p>
                <p class="text-white font-medium">{{ $vendor->approved_at->format('M d, Y g:i A') }}</p>
            </div>
            @endif
        </div>
    </div>

    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
        <h4 class="text-lg font-semibold text-white mb-6 flex items-center">
            <svg class="w-5 h-5 mr-2 text-brand-from" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            Business Information
        </h4>
        <div class="space-y-4">
            <div>
                <p class="text-gray-400 text-sm">Business Email</p>
                <p class="text-white font-medium">{{ $vendor->business_email }}</p>
            </div>
            <div>
                <p class="text-gray-400 text-sm">Business Phone</p>
                <p class="text-white font-medium">{{ $vendor->business_phone }}</p>
            </div>
            <div>
                <p class="text-gray-400 text-sm">Business Address</p>
                <p class="text-white font-medium">{{ $vendor->business_address }}</p>
            </div>
            @if($vendor->business_registration_number)
            <div>
                <p class="text-gray-400 text-sm">Registration Number</p>
                <p class="text-white font-medium">{{ $vendor->business_registration_number }}</p>
            </div>
            @endif
            @if($vendor->tax_id)
            <div>
                <p class="text-gray-400 text-sm">Tax ID</p>
                <p class="text-white font-medium">{{ $vendor->tax_id }}</p>
            </div>
            @endif
        </div>
    </div>
</div>

<div class="bg-gray-800 rounded-lg border border-gray-700 p-6 mb-8">
    <h4 class="text-lg font-semibold text-white mb-4">Vendor Communication</h4>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="space-y-3">
            <div>
                <p class="text-gray-400 text-sm">Primary Email</p>
                <p class="text-white">{{ $vendor->business_email ?: $vendor->user->email }}</p>
            </div>
            <div>
                <p class="text-gray-400 text-sm">Owner Email</p>
                <p class="text-white">{{ $vendor->user->email }}</p>
            </div>
            <div>
                <p class="text-gray-400 text-sm">Phone</p>
                <p class="text-white">{{ $vendor->business_phone ?: 'N/A' }}</p>
            </div>
            @if($vendor->business_email)
                <a href="mailto:{{ $vendor->business_email }}" class="inline-flex items-center px-4 py-2 rounded bg-indigo-600 hover:bg-indigo-700 text-white text-sm">Open Email Client</a>
            @endif
        </div>
        <form method="POST" action="{{ route('admin.vendors.message', $vendor) }}" class="space-y-3">
            @csrf
            <div>
                <label class="block text-sm text-gray-300 mb-1">Subject</label>
                <input type="text" name="subject" value="{{ old('subject') }}" class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2 text-white" required maxlength="255">
            </div>
            <div>
                <label class="block text-sm text-gray-300 mb-1">Message</label>
                <textarea name="message" rows="5" class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2 text-white" required maxlength="5000">{{ old('message') }}</textarea>
            </div>
            <input type="hidden" name="channel" value="email">
            <button type="submit" class="px-4 py-2 rounded bg-brand-from hover:bg-orange-500 text-white">Send Message</button>
        </form>
    </div>
</div>

@if($vendor->description)
<div class="bg-gray-800 rounded-lg border border-gray-700 p-6 mb-8">
    <h4 class="text-lg font-semibold text-white mb-4">Description</h4>
    <p class="text-gray-300 leading-relaxed">{{ $vendor->description }}</p>
</div>
@endif

<div class="bg-gray-800 rounded-lg border border-gray-700 mb-8">
    <div class="p-6 border-b border-gray-700">
        <h4 class="text-lg font-semibold text-white flex items-center gap-2">
            <svg class="w-5 h-5 text-brand-from" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Recent Bookings
        </h4>
    </div>
    <div class="p-6">
        @if($recentBookings->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full text-left">
                    <thead>
                        <tr>
                            <th class="pb-3 text-xs font-semibold uppercase tracking-wide text-gray-400">Date</th>
                            <th class="pb-3 text-xs font-semibold uppercase tracking-wide text-gray-400">Service</th>
                            <th class="pb-3 text-xs font-semibold uppercase tracking-wide text-gray-400">Customer</th>
                            <th class="pb-3 text-xs font-semibold uppercase tracking-wide text-gray-400">Price</th>
                            <th class="pb-3 text-xs font-semibold uppercase tracking-wide text-gray-400">Status</th>
                        </tr>
                    </thead>
                    <tbody class="space-y-3">
                        @foreach($recentBookings as $booking)
                            <tr class="bg-gray-900 rounded-lg">
                                <td class="px-3 py-4 text-sm text-gray-300 whitespace-nowrap">
                                    {{ $booking->booking_date->format('M d, Y') }}<br>
                                    <span class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($booking->booking_time)->format('g:i A') }}</span>
                                </td>
                                <td class="px-3 py-4 text-sm text-white">{{ $booking->salonService->name }}</td>
                                <td class="px-3 py-4 text-sm text-gray-300">{{ $booking->user->name }}</td>
                                <td class="px-3 py-4 text-sm text-white">${{ number_format($booking->price, 2) }}</td>
                                <td class="px-3 py-4">
                                    @php
                                        $statusColor = match($booking->status) {
                                            'pending' => 'bg-yellow-900 text-yellow-300',
                                            'confirmed' => 'bg-green-900 text-green-300',
                                            'completed' => 'bg-blue-900 text-blue-300',
                                            'cancelled' => 'bg-red-900 text-red-300',
                                            default => 'bg-gray-700 text-gray-300',
                                        };
                                    @endphp
                                    <span class="px-3 py-1 text-xs rounded-full font-semibold {{ $statusColor }}">{{ ucfirst($booking->status) }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-12 text-gray-400">
                No recent bookings for this vendor.
            </div>
        @endif
    </div>
</div>

<div class="bg-gray-800 rounded-lg border border-gray-700">
    <div class="p-6 border-b border-gray-700">
        <h4 class="text-lg font-semibold text-white flex items-center">
            <svg class="w-5 h-5 mr-2 text-brand-from" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
            Recent Products
        </h4>
    </div>
    <div class="p-6">
        @if($vendor->products->count() > 0)
            <div class="space-y-3">
                @foreach($vendor->products as $product)
                    <div class="flex items-center justify-between p-4 bg-gray-900 rounded-lg hover:bg-gray-750 transition">
                        <div class="flex-1">
                            <p class="text-white font-medium">{{ $product->name }}</p>
                            <div class="flex items-center gap-3 mt-1">
                                <span class="text-gray-400 text-sm">${{ number_format($product->price, 2) }}</span>
                                <span class="text-gray-400">•</span>
                                <span class="text-gray-400 text-sm">Stock: {{ $product->stock_quantity }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            @if($product->is_active)
                                <span class="px-3 py-1 text-xs rounded-full bg-green-900 text-green-300">Active</span>
                            @else
                                <span class="px-3 py-1 text-xs rounded-full bg-gray-700 text-gray-400">Inactive</span>
                            @endif
                            <a href="{{ route('admin.products.show', $product) }}" class="text-blue-400 hover:text-blue-300 text-sm">
                                View
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
            @if($stats['total_products'] > 10)
                <div class="mt-6 text-center">
                    <a href="{{ route('admin.products.index', ['vendor' => $vendor->id]) }}" class="text-brand-from hover:text-orange-400 font-medium inline-flex items-center gap-2">
                        View All Products
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            @endif
        @else
            <div class="text-center py-12">
                <svg class="w-16 h-16 mx-auto text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <p class="text-gray-400">No products added yet</p>
            </div>
        @endif
    </div>
</div>
@endsection

