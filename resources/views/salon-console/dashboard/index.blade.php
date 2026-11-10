@extends('layouts.salon-console')

@section('title', 'Dashboard - Salon Console')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-gray-800 border border-gray-700 rounded-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-400">Total Services</p>
                <h3 class="text-2xl font-bold text-white">{{ $totalServices }}</h3>
            </div>
            <div class="w-10 h-10 bg-indigo-500/20 rounded-lg flex items-center justify-center text-indigo-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v4m0 0v4m0-4h4m-4 0H8m12 0v-4a2 2 0 10-4 0v4m0 0v4a2 2 0 104 0v-4m0 0h-4"/>
                </svg>
            </div>
        </div>
    </div>
    <div class="bg-gray-800 border border-gray-700 rounded-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-400">Total Bookings</p>
                <h3 class="text-2xl font-bold text-white">{{ $totalBookings }}</h3>
            </div>
            <div class="w-10 h-10 bg-blue-500/20 rounded-lg flex items-center justify-center text-blue-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
        </div>
    </div>
    <div class="bg-gray-800 border border-gray-700 rounded-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-400">Total Earnings</p>
                <h3 class="text-2xl font-bold text-white">${{ number_format($totalEarnings, 2) }}</h3>
            </div>
            <div class="w-10 h-10 bg-green-500/20 rounded-lg flex items-center justify-center text-green-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
    </div>
    <div class="bg-gray-800 border border-gray-700 rounded-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-400">Total Stylists</p>
                <h3 class="text-2xl font-bold text-white">{{ $totalStylists }}</h3>
            </div>
            <div class="w-10 h-10 bg-purple-500/20 rounded-lg flex items-center justify-center text-purple-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
        </div>
    </div>
</div>

<div class="bg-gray-800 border border-gray-700 rounded-lg p-6">
    <h3 class="text-lg font-semibold text-white mb-4">Welcome back, {{ auth()->user()->name }}</h3>
    <p class="text-gray-400 text-sm">
        Manage your salon services, stylists, bookings, and earnings from this console.
    </p>
</div>
@endsection
