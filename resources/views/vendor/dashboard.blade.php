@extends('vendor.layout')

@section('title', 'Vendor Dashboard')
@section('header', 'Vendor Dashboard')

@section('content')
@if($subscription)
    <x-subscription-countdown :subscription="$subscription" />
@endif

<div class="mb-6">
    <div class="flex items-center gap-4">
        @php
            $dashboardImage = $vendor->profile_image ?: (optional($vendor->theme)->logo ?: $vendor->logo);
        @endphp
        @if($dashboardImage)
            <div class="h-16 w-16 rounded-lg bg-gray-800 border border-gray-700 flex items-center justify-center overflow-hidden">
                <img src="{{ '/storage/' . ltrim($dashboardImage ?? '', '/') }}" alt="{{ $vendor->business_name }} profile image" class="h-full w-full object-cover">
            </div>
        @else
            <div class="h-16 w-16 rounded-full bg-gray-800 border border-gray-700 flex items-center justify-center text-white font-semibold">
                {{ strtoupper(substr($vendor->business_name, 0, 1)) }}
            </div>
        @endif
        <div>
            <h3 class="text-2xl font-bold text-white">Welcome, {{ $vendor->business_name }}!</h3>
            <p class="text-gray-400 mt-1">Manage your products and track your sales</p>
        </div>
    </div>
</div>

@if(isset($verification))
    <div class="mb-8 bg-gray-800 rounded-lg border border-gray-700 p-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h4 class="text-lg font-semibold text-white">Verification Status</h4>
                <p class="text-gray-400 text-sm mt-1">Current status: <span class="text-white">{{ ucfirst($verification->status) }}</span></p>
            </div>
            <a href="{{ route('vendor.verification.index') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg">
                Manage Verification
            </a>
        </div>
    </div>
@endif

@if($canAnnouncements && $unreadAnnouncements->count() > 0)
    <div class="mb-6 space-y-3">
        @foreach($unreadAnnouncements as $announcement)
            <div class="bg-gray-800 rounded-lg border border-gray-700 p-4 border-l-4 
                {{ $announcement->type === 'info' ? 'border-l-blue-500' : '' }}
                {{ $announcement->type === 'warning' ? 'border-l-yellow-500' : '' }}
                {{ $announcement->type === 'success' ? 'border-l-green-500' : '' }}
                {{ $announcement->type === 'urgent' ? 'border-l-red-500' : '' }}">
                <div class="flex items-start space-x-3">
                    <span class="text-2xl flex-shrink-0">{{ $announcement->type_icon }}</span>
                    <div class="flex-1">
                        <div class="flex items-start justify-between">
                            <div>
                                <h4 class="text-lg font-bold text-white">{{ $announcement->title }}</h4>
                                <span class="px-2 py-1 text-xs rounded-full inline-block mt-1
                                    {{ $announcement->type === 'info' ? 'bg-blue-900 text-blue-300' : '' }}
                                    {{ $announcement->type === 'warning' ? 'bg-yellow-900 text-yellow-300' : '' }}
                                    {{ $announcement->type === 'success' ? 'bg-green-900 text-green-300' : '' }}
                                    {{ $announcement->type === 'urgent' ? 'bg-red-900 text-red-300' : '' }}">
                                    {{ ucfirst($announcement->type) }}
                                </span>
                            </div>
                            <p class="text-gray-400 text-sm">{{ $announcement->created_at->diffForHumans() }}</p>
                        </div>
                        <p class="text-gray-300 mt-2">{{ Str::limit($announcement->content, 150) }}</p>
                        <a href="{{ route('vendor.announcements.index') }}" class="inline-block mt-2 text-indigo-400 hover:text-indigo-300 text-sm">
                            View all announcements →
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    @if($canProducts)
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
        <p class="text-xs text-gray-400 mt-2">{{ $activeProducts }} active</p>
    </div>
    @endif

    @if($canOrders)
    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6 cursor-pointer hover:border-indigo-600 transition-colors" onclick="window.location='{{ route('vendor.orders.index') }}'">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-400">Total Orders</p>
                <p class="text-3xl font-bold text-white mt-2">{{ $totalOrders }}</p>
            </div>
            <svg class="w-12 h-12 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
            </svg>
        </div>
        <p class="text-xs text-gray-400 mt-2">{{ $pendingOrders }} pending</p>
    </div>
    @endif

    @if($canOrders)
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
        <p class="text-xs text-gray-400 mt-2">From paid orders</p>
    </div>
    @endif

    @if($canProducts)
    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6 cursor-pointer hover:border-yellow-600 transition-colors" onclick="window.location='{{ route('vendor.products.index') }}'">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-400">Low Stock</p>
                <p class="text-3xl font-bold text-white mt-2">{{ $lowStockProducts }}</p>
            </div>
            <svg class="w-12 h-12 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <p class="text-xs text-gray-400 mt-2">≤ 10 units remaining</p>
    </div>
    @endif
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6 cursor-pointer hover:border-green-600 transition-colors" onclick="window.location='{{ route('vendor.withdrawals.index') }}'">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-400">Available Balance</p>
                <p class="text-3xl font-bold text-white mt-2">${{ number_format($availableBalance, 2) }}</p>
            </div>
            <svg class="w-12 h-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
        </div>
        <p class="text-xs text-gray-400 mt-2">{{ $pendingWithdrawals }} pending withdrawals</p>
    </div>

    @if($canEnquiries)
    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6 cursor-pointer hover:border-blue-600 transition-colors" onclick="window.location='{{ route('vendor.enquiries.index') }}'">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-400">Pending Enquiries</p>
                <p class="text-3xl font-bold text-white mt-2">{{ $pendingEnquiries }}</p>
            </div>
            <svg class="w-12 h-12 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <p class="text-xs text-gray-400 mt-2">Awaiting your response</p>
    </div>
    @endif

    @if($canRma)
    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6 cursor-pointer hover:border-purple-600 transition-colors" onclick="window.location='{{ route('vendor.rma.index') }}'">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-400">Pending RMA</p>
                <p class="text-3xl font-bold text-white mt-2">{{ $pendingRmaRequests }}</p>
            </div>
            <svg class="w-12 h-12 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"/>
            </svg>
        </div>
        <p class="text-xs text-gray-400 mt-2">Return requests pending</p>
    </div>
    @endif

    @if($canFollowers)
    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6 cursor-pointer hover:border-pink-600 transition-colors" onclick="window.location='{{ route('vendor.followers.index') }}'">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-400">Total Followers</p>
                <p class="text-3xl font-bold text-white mt-2">{{ $totalFollowers }}</p>
            </div>
            <svg class="w-12 h-12 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
        </div>
        <p class="text-xs text-gray-400 mt-2">People following your store</p>
    </div>
    @endif
</div>

@if($canOrders)
<div class="bg-gray-800 rounded-lg border border-gray-700 p-6 mb-8">
    <h3 class="text-xl font-bold text-white mb-4">Performance Snapshot</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-gray-900 rounded-lg p-4">
            <p class="text-sm text-gray-400">Cancellation Rate</p>
            <p class="text-2xl font-bold text-white mt-2">{{ $performance['cancellation_rate'] }}%</p>
        </div>
        <div class="bg-gray-900 rounded-lg p-4">
            <p class="text-sm text-gray-400">Avg Fulfillment Days</p>
            <p class="text-2xl font-bold text-white mt-2">{{ $performance['avg_fulfillment_days'] }}</p>
        </div>
        <div class="bg-gray-900 rounded-lg p-4">
            <p class="text-sm text-gray-400">Late Shipments</p>
            <p class="text-2xl font-bold text-white mt-2">{{ $performance['late_shipments'] }}</p>
        </div>
    </div>
</div>
@endif

<div class="bg-gray-800 rounded-lg border border-gray-700 p-6 mb-8">
    <h3 class="text-xl font-bold text-white mb-4">Quick Actions</h3>
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6">
        @if($canProducts)
        <a href="{{ route('vendor.products.create') }}" class="flex flex-col items-center p-4 bg-gray-900 hover:bg-gray-700 rounded-lg transition-colors group">
            <svg class="w-8 h-8 text-indigo-500 group-hover:text-indigo-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span class="text-xs text-gray-400 group-hover:text-white text-center">Add Product</span>
        </a>
        @endif
        
        @if($canOrders && $canManualOrder)
        <a href="{{ route('vendor.orders.create') }}" class="flex flex-col items-center p-4 bg-gray-900 hover:bg-gray-700 rounded-lg transition-colors group">
            <svg class="w-8 h-8 text-blue-500 group-hover:text-blue-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            <span class="text-xs text-gray-400 group-hover:text-white text-center">Create Order</span>
        </a>
        @endif
        
        <a href="{{ route('vendor.withdrawals.create') }}" class="flex flex-col items-center p-4 bg-gray-900 hover:bg-gray-700 rounded-lg transition-colors group">
            <svg class="w-8 h-8 text-green-500 group-hover:text-green-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
            </svg>
            <span class="text-xs text-gray-400 group-hover:text-white text-center">Withdraw Funds</span>
        </a>
        
        @if($canOrders)
        <a href="{{ route('vendor.orders.index') }}" class="flex flex-col items-center p-4 bg-gray-900 hover:bg-gray-700 rounded-lg transition-colors group">
            <svg class="w-8 h-8 text-yellow-500 group-hover:text-yellow-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <span class="text-xs text-gray-400 group-hover:text-white text-center">View Orders</span>
        </a>
        @endif
        
        @if($canEnquiries)
        <a href="{{ route('vendor.enquiries.index') }}" class="flex flex-col items-center p-4 bg-gray-900 hover:bg-gray-700 rounded-lg transition-colors group">
            <svg class="w-8 h-8 text-purple-500 group-hover:text-purple-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
            </svg>
            <span class="text-xs text-gray-400 group-hover:text-white text-center">Enquiries</span>
        </a>
        @endif
        
        @if($canRma)
        <a href="{{ route('vendor.rma.index') }}" class="flex flex-col items-center p-4 bg-gray-900 hover:bg-gray-700 rounded-lg transition-colors group">
            <svg class="w-8 h-8 text-red-500 group-hover:text-red-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3"/>
            </svg>
            <span class="text-xs text-gray-400 group-hover:text-white text-center">RMA Requests</span>
        </a>
        @endif
    </div>
</div>

<div class="bg-gray-800 rounded-lg border border-gray-700 p-6 mb-8">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-xl font-bold text-white">Data Export Request</h3>
        <span class="text-xs text-gray-400">
            Status: {{ $existingRequest ? ucfirst($existingRequest->status) : 'None' }}
        </span>
    </div>
    @if($existingRequest)
        <p class="text-gray-400 mb-4">{{ $existingRequest->reason }}</p>
        @if($existingRequest->status === 'ready')
            <a href="{{ route('vendor.data-export.download', ['dataExportRequest' => $existingRequest, 'token' => $existingRequest->token]) }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg">
                Download Export
            </a>
        @endif
        @if($existingRequest->status === 'rejected')
            <p class="text-xs text-red-400 mt-2">Admin notes: {{ $existingRequest->admin_notes }}</p>
        @endif
    @else
        <p class="text-gray-400 mb-4">Submit a request and an admin will review it. You can track the status here.</p>
    @endif
    <a href="{{ route('vendor.data-export.request') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg">
        Request Export
    </a>
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
                                <img src="{{ '/storage/' . ltrim($product->featured_image ?? '', '/') }}" alt="{{ $product->name }}" class="w-12 h-12 object-cover rounded">
                            @else
                                <div class="w-12 h-12 bg-gray-700 rounded flex items-center justify-center">
                                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                            <p class="text-gray-400 text-xs">{{ $order->created_at->format('M d, Y') }}</p>
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
