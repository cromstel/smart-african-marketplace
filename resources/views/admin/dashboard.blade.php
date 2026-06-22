@extends('admin.layout')

@section('title', 'Admin Dashboard')
@section('header', 'Dashboard')

@section('content')
{{-- Welcome Section --}}
<div class="mb-8">
    <h2 class="text-2xl font-bold text-white mb-2">Welcome back, Admin!</h2>
    <p class="text-gray-400">Here's an overview of your platform's performance</p>
</div>

{{-- Main Stats Section --}}
<div class="mb-10">
    <h3 class="text-xl font-semibold text-white mb-6 flex items-center">
        <svg class="w-6 h-6 mr-3 text-brand-from" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
        </svg>
        Key Metrics
    </h3>
    <div class="flex flex-wrap gap-6">
        <div class="bg-gray-800 rounded-xl p-6 border border-gray-700 hover:border-blue-600 hover:shadow-lg transition-all w-full sm:w-[calc(50%-1.5rem)] lg:w-[calc(25%-1.5rem)]">
            <div class="flex items-center justify-between mb-6">
                <div class="bg-blue-600 rounded-xl p-4 shadow-lg">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-gray-400 text-base font-normal mb-3">Total Revenue</p>
            <p class="text-3xl font-semibold text-white mb-3">${{ number_format($stats['totalRevenue'] ?? 0, 2) }}</p>
            <p class="text-gray-500 text-sm">{{ $stats['paidOrders'] ?? 0 }} paid orders</p>
        </div>

        <div class="bg-gray-800 rounded-xl p-6 border border-gray-700 hover:border-green-600 hover:shadow-lg transition-all w-full sm:w-[calc(50%-1.5rem)] lg:w-[calc(25%-1.5rem)]">
            <div class="flex items-center justify-between mb-6">
                <div class="bg-green-600 rounded-xl p-4 shadow-lg">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
            </div>
            <p class="text-gray-400 text-base font-normal mb-3">Total Orders</p>
            <p class="text-3xl font-semibold text-white mb-3">{{ $stats['totalOrders'] ?? 0 }}</p>
            <a href="{{ route('admin.orders.index') }}" class="text-green-400 text-sm hover:text-green-300 font-medium inline-flex items-center">
                View all <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

        <div class="bg-gray-800 rounded-xl p-6 border border-gray-700 hover:border-brand-from hover:shadow-lg transition-all w-full sm:w-[calc(50%-1.5rem)] lg:w-[calc(25%-1.5rem)]">
            <div class="flex items-center justify-between mb-6">
                <div class="bg-brand-from rounded-xl p-4 shadow-lg">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
            </div>
            <p class="text-gray-400 text-base font-normal mb-3">Total Products</p>
            <p class="text-3xl font-semibold text-white mb-3">{{ $stats['totalProducts'] ?? 0 }}</p>
            <a href="{{ route('admin.products.index') }}" class="text-brand-from text-sm hover:text-orange-300 font-medium inline-flex items-center">
                View all <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

        <div class="bg-gray-800 rounded-xl p-6 border border-gray-700 hover:border-yellow-600 hover:shadow-lg transition-all w-full sm:w-[calc(50%-1.5rem)] lg:w-[calc(25%-1.5rem)]">
            <div class="flex items-center justify-between mb-6">
                <div class="bg-yellow-600 rounded-xl p-4 shadow-lg">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
            </div>
            <p class="text-gray-400 text-base font-normal mb-3">Total Vendors</p>
            <p class="text-3xl font-semibold text-white mb-3">{{ $stats['totalVendors'] ?? 0 }}</p>
            <a href="{{ route('admin.vendors.index') }}" class="text-yellow-400 text-sm hover:text-yellow-300 font-medium inline-flex items-center">
                View all <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
        <div class="bg-gray-800 rounded-xl p-6 border border-gray-700 hover:border-purple-600 hover:shadow-lg transition-all w-full sm:w-[calc(50%-1.5rem)] lg:w-[calc(25%-1.5rem)]">
            <div class="flex items-center justify-between mb-6">
                <div class="bg-purple-600 rounded-xl p-4 shadow-lg">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10m-12 9h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
            <p class="text-gray-400 text-base font-normal mb-3">Today's Bookings</p>
            <p class="text-3xl font-semibold text-white mb-3">{{ $stats['bookingsToday'] ?? 0 }}</p>
            <a href="{{ route('admin.bookings.index') }}" class="text-purple-400 text-sm hover:text-purple-300 font-medium inline-flex items-center">
                View bookings <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
        <div class="bg-gray-800 rounded-xl p-6 border border-gray-700 hover:border-indigo-600 hover:shadow-lg transition-all w-full sm:w-[calc(50%-1.5rem)] lg:w-[calc(25%-1.5rem)]">
            <div class="flex items-center justify-between mb-6">
                <div class="bg-indigo-600 rounded-xl p-4 shadow-lg">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-gray-400 text-base font-normal mb-3">Upcoming (24h)</p>
            <p class="text-3xl font-semibold text-white mb-3">{{ $stats['upcomingBookings'] ?? 0 }}</p>
            <p class="text-gray-500 text-sm">Pending + Confirmed</p>
        </div>
        <div class="bg-gray-800 rounded-xl p-6 border border-gray-700 hover:border-red-600 hover:shadow-lg transition-all w-full sm:w-[calc(50%-1.5rem)] lg:w-[calc(25%-1.5rem)]">
            <div class="flex items-center justify-between mb-6">
                <div class="bg-red-600 rounded-xl p-4 shadow-lg">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-1.414 1.414M6.343 17.657l-1.414 1.414M5.636 5.636l1.414 1.414M17.657 17.657l1.414 1.414M12 8v4m0 4h.01M12 2a10 10 0 100 20 10 10 0 000-20z"/>
                    </svg>
                </div>
            </div>
            <p class="text-gray-400 text-base font-normal mb-3">Cancellations Today</p>
            <p class="text-3xl font-semibold text-white mb-3">{{ $stats['cancellationsToday'] ?? 0 }}</p>
            <p class="text-gray-500 text-sm">Updated today</p>
        </div>
    </div>
</div>

{{-- Secondary Stats Section --}}
<div class="mb-10">
    <h3 class="text-xl font-semibold text-white mb-6 flex items-center">
        <svg class="w-6 h-6 mr-3 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
        </svg>
        Platform Overview
    </h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-gradient-to-br from-purple-900 to-purple-800 rounded-xl p-6 border border-purple-700 hover:shadow-lg transition-all">
            <div class="flex items-center justify-between mb-6">
                <div class="bg-purple-600 bg-opacity-50 rounded-xl p-4 shadow-md">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                </div>
            </div>
            <p class="text-purple-200 text-base font-medium mb-3">Categories</p>
            <p class="text-3xl font-semibold text-white mb-3">{{ $stats['totalCategories'] ?? 0 }}</p>
            <a href="{{ route('admin.categories.index') }}" class="text-purple-300 text-sm hover:text-purple-200 font-medium inline-flex items-center">
                Manage <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

        <div class="bg-gradient-to-br from-blue-900 to-blue-800 rounded-xl p-6 border border-blue-700 hover:shadow-lg transition-all">
            <div class="flex items-center justify-between mb-6">
                <div class="bg-blue-600 bg-opacity-50 rounded-xl p-4 shadow-md">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
            </div>
            <p class="text-blue-200 text-base font-medium mb-3">Blog Posts</p>
            <p class="text-3xl font-semibold text-white mb-3">{{ $stats['totalBlogPosts'] ?? 0 }}</p>
            <a href="{{ route('admin.blog.index') }}" class="text-blue-300 text-sm hover:text-blue-200 font-medium inline-flex items-center">
                Manage <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

        <div class="bg-gradient-to-br from-amber-900 to-amber-800 rounded-xl p-6 border border-amber-700 hover:shadow-lg transition-all">
            <div class="flex items-center justify-between mb-6">
                <div class="bg-amber-600 bg-opacity-50 rounded-xl p-4 shadow-md">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                    </svg>
                </div>
            </div>
            <p class="text-amber-200 text-base font-medium mb-3">Loyalty Points</p>
            <p class="text-3xl font-semibold text-white mb-3">{{ number_format($stats['totalLoyaltyPoints'] ?? 0) }}</p>
            <a href="{{ route('admin.loyalty.index') }}" class="text-amber-300 text-sm hover:text-amber-200 font-medium inline-flex items-center">
                Manage <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

        <div class="bg-gradient-to-br from-pink-900 to-pink-800 rounded-xl p-6 border border-pink-700 hover:shadow-lg transition-all">
            <div class="flex items-center justify-between mb-6">
                <div class="bg-pink-600 bg-opacity-50 rounded-xl p-4 shadow-md">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-pink-200 text-base font-medium mb-3">Referrals</p>
            <p class="text-3xl font-semibold text-white mb-3">{{ $stats['totalReferrals'] ?? 0 }}</p>
            <a href="{{ route('admin.referrals.index') }}" class="text-pink-300 text-sm hover:text-pink-200 font-medium inline-flex items-center">
                Manage <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
    </div>
</div>

{{-- Data Widgets Section --}}
<div class="mb-10">
    <h3 class="text-xl font-semibold text-white mb-6 flex items-center">
        <svg class="w-6 h-6 mr-3 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
        </svg>
        Recent Activity
    </h3>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-gray-800 rounded-xl border border-gray-700 hover:border-green-600 transition-colors">
            <div class="p-6 border-b border-gray-700">
                <h4 class="text-lg font-semibold text-white flex items-center">
                    <svg class="w-5 h-5 mr-3 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Recent Orders
                </h4>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @forelse($recentOrders ?? [] as $order)
                        <div class="flex items-center justify-between p-4 bg-gray-900 rounded-lg hover:bg-gray-750 transition">
                            <div class="flex-1 min-w-0">
                                <p class="text-white font-medium text-base truncate">{{ $order->order_number }}</p>
                                <p class="text-gray-400 text-sm truncate mt-1">{{ $order->user->name ?? 'Guest' }}</p>
                            </div>
                            <div class="text-right ml-4">
                                <p class="text-green-400 font-semibold text-base">${{ number_format($order->total, 2) }}</p>
                                <span class="px-3 py-1 text-xs rounded-full {{ $order->status === 'delivered' ? 'bg-green-900 text-green-300' : 'bg-yellow-900 text-yellow-300' }} mt-1 inline-block">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <p class="text-gray-500 text-base">No orders yet</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="bg-gray-800 rounded-xl border border-gray-700 hover:border-red-600 transition-colors">
            <div class="p-6 border-b border-gray-700">
                <h4 class="text-lg font-semibold text-white flex items-center">
                    <svg class="w-5 h-5 mr-3 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    Low Stock Alert
                </h4>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @forelse($lowStockProducts ?? [] as $product)
                        <div class="flex items-center justify-between p-4 bg-gray-900 rounded-lg hover:bg-gray-750 transition">
                            <div class="flex-1 min-w-0">
                                <p class="text-white font-medium text-base truncate">{{ optional($product)->name ?? 'Unknown Product' }}</p>
                                <p class="text-gray-400 text-sm truncate mt-1">{{ optional(optional($product)->category)->name ?? 'N/A' }}</p>
                            </div>
                            <span class="px-3 py-1.5 text-sm rounded-full bg-red-900 text-red-300 font-semibold whitespace-nowrap ml-4">
                                {{ optional($product)->stock_quantity ?? 0 }} left
                            </span>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <svg class="w-16 h-16 mx-auto text-green-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-gray-500 text-base">All products well stocked</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="bg-gray-800 rounded-xl border border-gray-700">
            <div class="p-6 border-b border-gray-700">
                <h4 class="text-base font-semibold text-white flex items-center">
                    <svg class="w-4 h-4 mr-2 text-brand-from" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                    Top Selling
                </h4>
            </div>
            <div class="p-6">
                <div class="space-y-3">
                    @forelse($topSellingProducts ?? [] as $product)
                        <div class="flex items-center justify-between p-3 bg-gray-900 rounded-lg hover:bg-gray-750 transition">
                            <div class="flex-1 min-w-0">
                                <p class="text-white font-medium text-sm truncate">{{ optional($product)->name ?? 'Unknown Product' }}</p>
                                <p class="text-gray-400 text-xs">${{ number_format(optional($product)->price ?? 0, 2) }}</p>
                            </div>
                            <span class="px-3 py-1 text-xs rounded-full bg-orange-900 text-orange-300 font-semibold whitespace-nowrap ml-4">
                                    {{ optional($product)->sales_count ?? 0 }} sold
                                </span>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <p class="text-gray-500 text-sm">No sales yet</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Management Section --}}
<div>
    <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
        <svg class="w-5 h-5 mr-2 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
        </svg>
        User Management
    </h3>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-gray-800 rounded-xl border border-gray-700">
            <div class="p-6 border-b border-gray-700">
                <h4 class="text-base font-semibold text-white flex items-center">
                    <svg class="w-4 h-4 mr-2 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    Pending Vendors
                </h4>
            </div>
            <div class="p-6">
                <div class="space-y-3">
                    @forelse($pendingVendors ?? [] as $vendor)
                        <div class="flex items-center justify-between p-3 bg-gray-900 rounded-lg hover:bg-gray-750 transition">
                            <div class="flex-1 min-w-0">
                                <p class="text-white font-medium text-sm truncate">{{ optional($vendor)->business_name ?? 'Unknown Vendor' }}</p>
                                <p class="text-gray-400 text-xs truncate">{{ optional($vendor)->business_email ?? 'N/A' }}</p>
                            </div>
                            <span class="px-3 py-1 text-xs rounded-full bg-yellow-900 text-yellow-300 font-semibold whitespace-nowrap ml-4">Pending</span>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <p class="text-gray-500 text-sm">No pending vendors</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="bg-gray-800 rounded-xl border border-gray-700">
            <div class="p-6 border-b border-gray-700">
                <h4 class="text-base font-semibold text-white flex items-center">
                    <svg class="w-4 h-4 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    Recent Customers
                </h4>
            </div>
            <div class="p-6">
                <div class="space-y-3">
                    @forelse($newCustomers ?? [] as $user)
                        <div class="flex items-center justify-between p-3 bg-gray-900 rounded-lg hover:bg-gray-750 transition">
                            <div class="flex-1 min-w-0">
                                <p class="text-white font-medium text-sm truncate">{{ optional($user)->name ?? 'Unknown User' }}</p>
                                <p class="text-gray-400 text-xs truncate">{{ optional($user)->email ?? 'N/A' }}</p>
                            </div>
                            <span class="text-gray-500 text-xs whitespace-nowrap ml-4">
                                {{ optional(optional($user)->created_at)->diffForHumans() ?? 'N/A' }}
                            </span>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <p class="text-gray-500 text-sm">No customers yet</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-white">Revenue Trend</h3>
            <label for="revenueDays" class="sr-only">Revenue time range</label>
            <select id="revenueDays" class="bg-gray-700 border border-gray-600 text-white text-sm rounded-lg px-3 py-1">
                <option value="7">Last 7 days</option>
                <option value="30" selected>Last 30 days</option>
                <option value="90">Last 90 days</option>
            </select>
        </div>
        <canvas id="revenueChart" height="100"></canvas>
    </div>

    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-white mb-4">Top 5 Products</h3>
        <canvas id="topProductsChart" height="100"></canvas>
    </div>
</div>

<div class="space-y-6 mb-6">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <div class="space-y-3">
                <h3 class="text-lg font-semibold text-white">Category Distribution</h3>
                <div class="relative h-40 w-full">
                    <canvas id="categoryDistributionChart" class="h-full w-full"></canvas>
                </div>
            </div>
        </div>

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <div class="space-y-3">
                <h3 class="text-lg font-semibold text-white">Traffic Source Analytics</h3>
                <div class="relative h-40 w-full">
                    <canvas id="trafficSourcesChart" class="h-full w-full"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-white mb-4">Customer Analytics</h3>
        <canvas id="customerAnalyticsChart" height="100"></canvas>
    </div>

    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-white">Export Reports</h3>
        </div>
        <div class="flex gap-4">
            <a href="{{ route('admin.analytics.export', ['type' => 'orders']) }}" 
               class="bg-brand-from hover:bg-brand-from text-white px-4 py-2 rounded-lg text-sm hover:opacity-90">
                Export Orders (CSV)
            </a>
            <a href="{{ route('admin.analytics.export', ['type' => 'products']) }}" 
               class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm">
                Export Products (CSV)
            </a>
            <a href="{{ route('admin.analytics.export', ['type' => 'revenue']) }}" 
               class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg text-sm">
                Export Revenue (CSV)
            </a>
        </div>
    </div>
</div>

<script src="{{ asset('vendor/chart.js/chart.umd.min.js') }}"></script>
<script>
const chartColors = {
    primary: '#4f46e5',
    success: '#10b981',
    warning: '#f59e0b',
    danger: '#ef4444',
    info: '#3b82f6',
    grid: '#374151',
    text: '#9ca3af'
};

const chartDefaults = {
    responsive: true,
    maintainAspectRatio: true,
    plugins: {
        legend: {
            labels: {
                color: chartColors.text
            }
        }
    },
    scales: {
        x: {
            grid: {
                color: chartColors.grid
            },
            ticks: {
                color: chartColors.text
            }
        },
        y: {
            grid: {
                color: chartColors.grid
            },
            ticks: {
                color: chartColors.text
            }
        }
    }
};

const categoryChartOptions = {
    ...chartDefaults,
    maintainAspectRatio: false,
    aspectRatio: 1,
    layout: {
        padding: 18
    },
    plugins: {
        ...chartDefaults.plugins,
        legend: {
            ...chartDefaults.plugins.legend,
            position: 'bottom',
            labels: {
                ...chartDefaults.plugins.legend.labels,
                padding: 12
            }
        }
    }
};

let revenueChart;
let categoryDistributionChart;
let customerAnalyticsChart;
let trafficSourcesChart;

function loadRevenueChart(days = 30) {
    fetch(`{{ route('admin.analytics.revenue') }}?days=${days}`)
        .then(response => response.json())
        .then(data => {
            const ctx = document.getElementById('revenueChart').getContext('2d');
            
            if (revenueChart) {
                revenueChart.destroy();
            }
            
            revenueChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Revenue ($)',
                        data: data.data,
                        borderColor: chartColors.primary,
                        backgroundColor: chartColors.primary + '33',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: chartDefaults
            });
        });
}

function loadTopProductsChart() {
    fetch('{{ route('admin.analytics.top-products') }}')
        .then(response => response.json())
        .then(data => {
            const ctx = document.getElementById('topProductsChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Units Sold',
                        data: data.data,
                        backgroundColor: [
                            chartColors.primary,
                            chartColors.success,
                            chartColors.warning,
                            chartColors.danger,
                            chartColors.info
                        ]
                    }]
                },
                options: {
                    ...chartDefaults,
                    indexAxis: 'y'
                }
            });
        });
}

function loadCategoryDistributionChart() {
    fetch('{{ route('admin.analytics.categories') }}')
        .then(response => response.json())
        .then(data => {
            const ctx = document.getElementById('categoryDistributionChart').getContext('2d');

            if (categoryDistributionChart) {
                categoryDistributionChart.destroy();
            }

            categoryDistributionChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: data.labels,
                    datasets: [{
                        data: data.data,
                        backgroundColor: [
                            chartColors.primary,
                            chartColors.success,
                            chartColors.warning,
                            chartColors.danger,
                            chartColors.info,
                            '#8b5cf6',
                            '#14b8a6',
                            '#f97316',
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                color: chartColors.text
                            }
                        }
                    }
                },
                options: categoryChartOptions
            });
        });
}

function loadCustomerAnalyticsChart(days = 30) {
    fetch(`{{ route('admin.analytics.customers') }}?days=${days}`)
        .then(response => response.json())
        .then(data => {
            const ctx = document.getElementById('customerAnalyticsChart').getContext('2d');

            if (customerAnalyticsChart) {
                customerAnalyticsChart.destroy();
            }

            customerAnalyticsChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [
                        {
                            label: 'New Customers',
                            data: data.new_customers,
                            borderColor: chartColors.success,
                            backgroundColor: chartColors.success + '33',
                            tension: 0.35,
                            fill: true
                        },
                        {
                            label: 'Active Customers',
                            data: data.active_customers,
                            borderColor: chartColors.info,
                            backgroundColor: chartColors.info + '33',
                            tension: 0.35,
                            fill: false
                        }
                    ]
                },
                options: chartDefaults
            });
        });
}

function loadTrafficSourcesChart(days = 30) {
    fetch(`{{ route('admin.analytics.traffic-sources') }}?days=${days}`)
        .then(response => response.json())
        .then(data => {
            const ctx = document.getElementById('trafficSourcesChart').getContext('2d');

            if (trafficSourcesChart) {
                trafficSourcesChart.destroy();
            }

            trafficSourcesChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: data.labels,
                    datasets: [{
                        data: data.data,
                        backgroundColor: [
                            chartColors.primary,
                            chartColors.success,
                            chartColors.warning,
                            chartColors.danger,
                            chartColors.info,
                            '#8b5cf6',
                            '#14b8a6',
                            '#f97316',
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                color: chartColors.text
                            }
                        }
                    }
                }
            });
        });
}

document.addEventListener('DOMContentLoaded', function() {
    loadRevenueChart();
    loadTopProductsChart();
    loadCategoryDistributionChart();
    loadCustomerAnalyticsChart();
    loadTrafficSourcesChart();
    
    document.getElementById('revenueDays').addEventListener('change', function() {
        loadRevenueChart(this.value);
        loadCustomerAnalyticsChart(this.value);
        loadTrafficSourcesChart(this.value);
    });
});
</script>
@endsection

