@extends('layouts.salon-console')

@section('title', 'Earnings - Salon Console')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    <div class="bg-gray-800 border border-gray-700 rounded-lg p-6">
        <p class="text-sm text-gray-400">Total Earnings</p>
        <h3 class="text-2xl font-bold text-white">${{ number_format($totalEarnings, 2) }}</h3>
    </div>
    <div class="bg-gray-800 border border-gray-700 rounded-lg p-6">
        <p class="text-sm text-gray-400">Pending Payouts</p>
        <h3 class="text-2xl font-bold text-white">${{ number_format(0, 2) }}</h3>
    </div>
    <div class="bg-gray-800 border border-gray-700 rounded-lg p-6">
        <p class="text-sm text-gray-400">Completed Services</p>
        <h3 class="text-2xl font-bold text-white">{{ count($earnings) }}</h3>
    </div>
</div>

<div class="bg-gray-800 border border-gray-700 rounded-lg p-6">
    <table class="w-full text-left">
        <thead>
            <tr class="border-b border-gray-700">
                <th class="py-3 px-4 text-gray-400 text-sm font-medium">Date</th>
                <th class="py-3 px-4 text-gray-400 text-sm font-medium">Service</th>
                <th class="py-3 px-4 text-gray-400 text-sm font-medium">Customer</th>
                <th class="py-3 px-4 text-gray-400 text-sm font-medium">Amount</th>
                <th class="py-3 px-4 text-gray-400 text-sm font-medium">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($earnings as $earning)
            <tr class="border-b border-gray-700">
                <td class="py-3 px-4 text-white">{{ $earning->date }}</td>
                <td class="py-3 px-4 text-white">{{ $earning->service }}</td>
                <td class="py-3 px-4 text-white">{{ $earning->customer }}</td>
                <td class="py-3 px-4 text-white">${{ number_format($earning->amount, 2) }}</td>
                <td class="py-3 px-4 text-white">
                    <span class="px-2 py-1 bg-green-900/30 text-green-400 rounded text-xs">Paid</span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="py-4 text-center text-gray-400">No earnings found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
