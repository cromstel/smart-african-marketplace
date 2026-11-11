@extends('layouts.shop')

@section('content')
<div class="min-h-screen bg-gray-50 py-6 lg:py-12">
    <div class="container mx-auto px-4">
        <div class="mb-8">
            <h2 class="text-2xl lg:text-3xl font-bold text-gray-900">Welcome back, {{ auth()->user()->firstname ?? auth()->user()->name }}!</h2>
            <p class="text-gray-600 mt-1">Manage your orders, wishlist, and account settings</p>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6 mb-8">
            <a href="{{ route('orders.index') }}" class="bg-white rounded-lg shadow-md p-6 hover:shadow-xl transition transform hover:-translate-y-1">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Orders</h3>
                    <div class="bg-orange-100 p-3 rounded-full">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-orange-600">{{ auth()->user()->orders()->count() }}</p>
                <p class="text-sm text-gray-600 mt-2">View all orders</p>
            </a>

            <a href="{{ route('wishlist.index') }}" class="bg-white rounded-lg shadow-md p-6 hover:shadow-xl transition transform hover:-translate-y-1">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Wishlist</h3>
                    <div class="bg-red-100 p-3 rounded-full">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-red-600">{{ auth()->user()->wishlists()->count() }}</p>
                <p class="text-sm text-gray-600 mt-2">Saved items</p>
            </a>

            <a href="{{ route('loyalty.index') }}" class="bg-white rounded-lg shadow-md p-6 hover:shadow-xl transition transform hover:-translate-y-1">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Loyalty Points</h3>
                    <div class="bg-green-100 p-3 rounded-full">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-green-600">{{ number_format(auth()->user()->loyalty_points_balance ?? 0) }}</p>
                <p class="text-sm text-gray-600 mt-2">Available points</p>
            </a>

            <a href="{{ route('bookings.index') }}" class="bg-white rounded-lg shadow-md p-6 hover:shadow-xl transition transform hover:-translate-y-1">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Bookings</h3>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-blue-600">{{ auth()->user()->bookings()->count() }}</p>
                <p class="text-sm text-gray-600 mt-2">Salon bookings</p>
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold mb-6 text-gray-900 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    Quick Actions
                </h2>
                <div class="space-y-3">
                    <a href="{{ route('shop.index') }}" class="flex items-center space-x-3 px-4 py-3 bg-gradient-to-r from-orange-600 to-red-600 hover:from-orange-700 hover:to-red-700 text-white rounded-lg font-semibold transition transform hover:scale-105">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <span>Continue Shopping</span>
                    </a>
                    <a href="{{ route('salon.index') }}" class="flex items-center space-x-3 px-4 py-3 border-2 border-orange-600 text-orange-600 hover:bg-orange-50 rounded-lg font-semibold transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span>Book Salon Service</span>
                    </a>
                    <a href="{{ route('referrals.index') }}" class="flex items-center space-x-3 px-4 py-3 border-2 border-gray-300 text-gray-700 hover:bg-gray-50 rounded-lg font-semibold transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <span>Invite Friends</span>
                    </a>
                    <a href="{{ route('profile.edit') }}" class="flex items-center space-x-3 px-4 py-3 border-2 border-gray-300 text-gray-700 hover:bg-gray-50 rounded-lg font-semibold transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span>Account Settings</span>
                    </a>
                </div>
            </div>

            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold mb-6 text-gray-900 flex items-center justify-between">
                        <span class="flex items-center">
                            <svg class="w-6 h-6 mr-2 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            Recent Orders
                        </span>
                        <a href="{{ route('orders.index') }}" class="text-sm text-orange-600 hover:text-orange-700 font-semibold">View All →</a>
                    </h2>
                    @php
                        $recentOrders = auth()->user()->orders()->latest()->take(5)->get();
                    @endphp
                    
                    @if($recentOrders->count() > 0)
                        <div class="space-y-3">
                            @foreach($recentOrders as $order)
                                <a href="{{ route('orders.show', $order) }}" class="block p-4 bg-gray-50 hover:bg-gray-100 rounded-lg transition border border-gray-200">
                                    <div class="flex justify-between items-start mb-2">
                                        <div>
                                            <p class="font-bold text-gray-900">Order #{{ $order->order_number }}</p>
                                            <p class="text-sm text-gray-600 mt-1">
                                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                                {{ $order->created_at->format('M d, Y') }}
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <p class="font-bold text-lg text-gray-900">${{ number_format($order->total, 2) }}</p>
                                            <span class="text-xs px-3 py-1 rounded-full font-semibold {{ $order->status === 'delivered' ? 'bg-green-100 text-green-800' : ($order->status === 'processing' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800') }}">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ $order->items->count() }} {{ $order->items->count() == 1 ? 'item' : 'items' }}
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                            <p class="text-gray-600 mb-4">No orders yet. Start shopping!</p>
                            <a href="{{ route('shop.index') }}" class="inline-block px-6 py-3 bg-gradient-to-r from-orange-600 to-red-600 hover:from-orange-700 hover:to-red-700 text-white rounded-lg font-semibold transition">
                                Browse Products
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="bg-gradient-to-br from-orange-50 to-red-50 rounded-lg shadow-md p-6 border border-orange-200">
                <h3 class="text-lg font-bold text-gray-900 mb-3 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Account Summary
                </h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between py-2 border-b border-orange-200">
                        <span class="text-gray-600">Member Since</span>
                        <span class="font-semibold text-gray-900">{{ auth()->user()->created_at->format('M Y') }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-orange-200">
                        <span class="text-gray-600">Total Orders</span>
                        <span class="font-semibold text-gray-900">{{ auth()->user()->orders()->count() }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-orange-200">
                        <span class="text-gray-600">Total Spent</span>
                        <span class="font-semibold text-gray-900">${{ number_format(auth()->user()->orders()->where('payment_status', 'paid')->sum('total'), 2) }}</span>
                    </div>
                    <div class="flex justify-between py-2">
                        <span class="text-gray-600">Referral Code</span>
                        <span class="font-semibold text-orange-600">{{ auth()->user()->referral_code ?? 'Not Set' }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Upcoming Bookings
                </h3>
                @php
                    // Exclude cancelled/completed bookings -- a future-dated
                    // booking the customer already cancelled (or one that's
                    // already been completed) is not "upcoming". This
                    // previously showed cancelled bookings here, which was
                    // confusing right after cancelling one.
                    $upcomingBookings = auth()->user()->bookings()
                        ->whereNotIn('status', ['cancelled', 'completed'])
                        ->where('booking_date', '>=', now()->toDateString())
                        ->orderBy('booking_date')
                        ->take(3)
                        ->get();
                @endphp
                @if($upcomingBookings->count() > 0)
                    <div class="space-y-2">
                        @foreach($upcomingBookings as $booking)
                            <div class="p-3 bg-blue-50 rounded border border-blue-200">
                                <p class="font-semibold text-sm text-gray-900">{{ $booking->salonService->name }}</p>
                                <p class="text-xs text-gray-600 mt-1">{{ $booking->booking_date->format('M d, Y') }} at {{ \Carbon\Carbon::parse($booking->booking_time)->format('g:i A') }}</p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-sm text-center py-4">No upcoming bookings</p>
                @endif
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                    Wishlist Preview
                </h3>
                @php
                    $wishlistItems = auth()->user()->wishlists()->with('product')->take(3)->get();
                @endphp
                @if($wishlistItems->count() > 0)
                    <div class="space-y-2">
                        @foreach($wishlistItems as $item)
                            <div class="flex items-center space-x-3 p-2 bg-gray-50 rounded">
                                @if($item->product->featured_image)
                                    <img src="{{ '/storage/' . ltrim($item->product->featured_image ?? '', '/') }}" alt="{{ $item->product->name }}" class="w-12 h-12 object-cover rounded">
                                @else
                                    <div class="w-12 h-12 bg-gray-200 rounded"></div>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 truncate">{{ $item->product->name }}</p>
                                    <p class="text-xs text-gray-600">${{ number_format((float) $item->product->price, 2) }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <a href="{{ route('wishlist.index') }}" class="block mt-3 text-sm text-orange-600 hover:text-orange-700 font-semibold text-center">View All →</a>
                @else
                    <p class="text-gray-500 text-sm text-center py-4">Your wishlist is empty</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

