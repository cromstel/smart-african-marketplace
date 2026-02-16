@extends('vendor.layout')

@section('title', 'Vendor Dashboard')
@section('header', 'Vendor Dashboard')

@section('content')
<div class="mb-6">
    <h3 class="text-2xl font-bold text-white">Welcome, {{ $vendor->business_name }}!</h3>
    <p class="text-gray-400 mt-1">Manage your products and track your sales</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-400">Total Products</p>
                <p class="text-3xl font-bold text-white mt-2">{{ $totalProducts }}</p>
            </div>
            <svg class="w-12 h-12 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
        </div>
        <p class="text-xs text-gray-500 mt-2">{{ $activeProducts }} active</p>
    </div>

    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-400">Total Orders</p>
                <p class="text-3xl font-bold text-white mt-2">{{ $totalOrders }}</p>
            </div>
            <svg class="w-12 h-12 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
            </svg>
        </div>
        <p class="text-xs text-gray-500 mt-2">{{ $pendingOrders }} pending</p>
    </div>

    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-400">Total Revenue</p>
                <p class="text-3xl font-bold text-white mt-2">${{ number_format($totalRevenue, 2) }}</p>
            </div>
            <svg class="w-12 h-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <p class="text-xs text-gray-500 mt-2">From paid orders</p>
    </div>

    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-400">Low Stock</p>
                <p class="text-3xl font-bold text-white mt-2">{{ $lowStockProducts }}</p>
            </div>
            <svg class="w-12 h-12 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <p class="text-xs text-gray-500 mt-2">≤ 10 units remaining</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
        <h3 class="text-xl font-bold text-white mb-4">Top Selling Products</h3>
        @if($topProducts->count() > 0)
            <div class="space-y-3">
                @foreach($topProducts as $product)
                    <div class="flex items-center justify-between p-3 bg-gray-900 rounded">
                        <div class="flex items-center space-x-3">
                            @if($product->featured_image)
                                <img src="{{ Storage::url($product->featured_image) }}" alt="{{ $product->name }}" class="w-12 h-12 object-cover rounded">
                            @else
                                <div class="w-12 h-12 bg-gray-700 rounded flex items-center justify-center">
                                    <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            @endif
                            <div>
                                <p class="text-white text-sm font-medium">{{ $product->name }}</p>
                                <p class="text-gray-400 text-xs">{{ $product->sales_count }} sold</p>
                            </div>
                        </div>
                        <p class="text-white font-semibold">${{ number_format($product->price, 2) }}</p>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-400 text-center py-8">No sales data available yet</p>
        @endif
    </div>

    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
        <h3 class="text-xl font-bold text-white mb-4">Recent Orders</h3>
        @if($recentOrders->count() > 0)
            <div class="space-y-3">
                @foreach($recentOrders as $order)
                    <div class="p-3 bg-gray-900 rounded">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-white text-sm font-bold">{{ $order->order_number }}</p>
                            <span class="px-2 py-1 text-xs rounded {{ 
                                $order->status === 'delivered' ? 'bg-green-900 text-green-300' : 
                                ($order->status === 'processing' ? 'bg-blue-900 text-blue-300' : 'bg-yellow-900 text-yellow-300') 
                            }}">
                                {{ ucfirst($order->status) }}
                            </span>
                        </div>
                        <p class="text-gray-400 text-xs">{{ $order->user->name }}</p>
                        <div class="mt-2 flex items-center justify-between">
                            <p class="text-gray-500 text-xs">{{ $order->created_at->format('M d, Y') }}</p>
                            <p class="text-white font-semibold text-sm">
                                ${{ number_format($order->items->sum('total'), 2) }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-400 text-center py-8">No orders yet</p>
        @endif
    </div>
</div>
@endsection
