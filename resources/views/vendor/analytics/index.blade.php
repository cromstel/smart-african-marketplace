@extends('vendor.layout')

@section('title', 'Analytics')
@section('header', 'Analytics')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h3 class="text-2xl font-bold text-white">Store Analytics</h3>
            <p class="text-gray-400 mt-1">Performance overview for the last {{ $days }} days</p>
        </div>
        <p class="text-xs text-gray-400">Last updated: {{ $metrics['last_updated'] }}</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4">
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-5">
            <p class="text-gray-400 text-sm">Revenue ({{ $days }}d)</p>
            <p class="text-2xl font-bold text-white mt-1">${{ number_format($metrics['revenue_30_days'], 2) }}</p>
        </div>
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-5">
            <p class="text-gray-400 text-sm">Paid Orders</p>
            <p class="text-2xl font-bold text-white mt-1">{{ $metrics['paid_orders'] }}</p>
            <p class="text-xs text-emerald-400 mt-2">{{ $metrics['conversion_rate'] }}% conversion</p>
        </div>
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-5">
            <p class="text-gray-400 text-sm">Cancellation Rate</p>
            <p class="text-2xl font-bold text-white mt-1">{{ $metrics['cancellation_rate'] }}%</p>
        </div>
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-5">
            <p class="text-gray-400 text-sm">Enquiry Response</p>
            <p class="text-2xl font-bold text-white mt-1">{{ $metrics['enquiry_response_rate'] }}%</p>
        </div>
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-5">
            <p class="text-gray-400 text-sm">New Followers</p>
            <p class="text-2xl font-bold text-white mt-1">{{ $metrics['new_followers_30_days'] }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2 bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h4 class="text-lg font-semibold text-white mb-4">Daily Orders ({{ $days }} days)</h4>
            @if($ordersSeries->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="text-gray-400 border-b border-gray-700">
                                <th class="pb-2">Date</th>
                                <th class="pb-2">Orders</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ordersSeries as $point)
                                <tr class="border-b border-gray-700/50">
                                    <td class="py-2 text-gray-300">{{ \Carbon\Carbon::parse($point['day'])->format('M d, Y') }}</td>
                                    <td class="py-2 text-white font-medium">{{ $point['total'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-400">No order activity recorded in this period.</p>
            @endif
        </div>

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h4 class="text-lg font-semibold text-white mb-4">Top Products</h4>
            @if($topProducts->isNotEmpty())
                <div class="space-y-3">
                    @foreach($topProducts as $product)
                        <div class="bg-gray-900 rounded-lg border border-gray-700 p-3">
                            <p class="text-white font-medium">{{ $product->name }}</p>
                            <div class="mt-2 text-xs text-gray-400 space-x-3">
                                <span>Sales: {{ (int) $product->sales_count }}</span>
                                <span>Views: {{ (int) $product->views_count }}</span>
                                <span>Price: ${{ number_format($product->price, 2) }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-400">No products with activity yet.</p>
            @endif
        </div>
    </div>
</div>
@endsection

