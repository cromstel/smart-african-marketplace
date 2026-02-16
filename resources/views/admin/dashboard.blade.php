@extends('admin.layout')

@section('title', 'Admin Dashboard')
@section('header', 'Dashboard')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-6">
    <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-sm">Total Revenue</p>
                <p class="text-3xl font-bold text-white mt-1">${{ number_format(\App\Models\Order::where('payment_status', 'paid')->sum('total'), 2) }}</p>
            </div>
            <div class="bg-blue-600 rounded-full p-3">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        <p class="text-gray-400 text-xs mt-4">
            {{ \App\Models\Order::where('payment_status', 'paid')->count() }} paid orders
        </p>
    </div>

    <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-sm">Total Products</p>
                <p class="text-3xl font-bold text-white mt-1">{{ \App\Models\Product::count() }}</p>
            </div>
            <div class="bg-indigo-600 rounded-full p-3">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
        </div>
        <a href="{{ route('admin.products.index') }}" class="text-indigo-400 text-sm mt-4 inline-block hover:text-indigo-300">
            View all →
        </a>
    </div>

    <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-sm">Total Orders</p>
                <p class="text-3xl font-bold text-white mt-1">{{ \App\Models\Order::count() }}</p>
            </div>
            <div class="bg-green-600 rounded-full p-3">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
        </div>
        <a href="{{ route('admin.orders.index') }}" class="text-green-400 text-sm mt-4 inline-block hover:text-green-300">
            View all →
        </a>
    </div>

    <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-sm">Total Vendors</p>
                <p class="text-3xl font-bold text-white mt-1">{{ \App\Models\Vendor::count() }}</p>
            </div>
            <div class="bg-yellow-600 rounded-full p-3">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
        </div>
        <a href="{{ route('admin.vendors.index') }}" class="text-yellow-400 text-sm mt-4 inline-block hover:text-yellow-300">
            View all →
        </a>
    </div>

    <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-sm">Categories</p>
                <p class="text-3xl font-bold text-white mt-1">{{ \App\Models\Category::count() }}</p>
            </div>
            <div class="bg-purple-600 rounded-full p-3">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
            </div>
        </div>
        <a href="{{ route('admin.categories.index') }}" class="text-purple-400 text-sm mt-4 inline-block hover:text-purple-300">
            View all →
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <div class="bg-gray-800 rounded-lg border border-gray-700">
        <div class="p-6 border-b border-gray-700">
            <h3 class="text-lg font-semibold text-white">Recent Orders</h3>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                @forelse(\App\Models\Order::with('user')->latest()->take(5)->get() as $order)
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-white font-medium">{{ $order->order_number }}</p>
                            <p class="text-gray-400 text-sm">{{ $order->user->name }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-green-400 font-semibold">${{ number_format($order->total, 2) }}</p>
                            <span class="px-2 py-1 text-xs rounded {{ $order->status === 'delivered' ? 'bg-green-900 text-green-300' : 'bg-yellow-900 text-yellow-300' }}">
                                {{ ucfirst($order->status) }}
                            </span>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-400">No orders yet</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="bg-gray-800 rounded-lg border border-gray-700">
        <div class="p-6 border-b border-gray-700">
            <h3 class="text-lg font-semibold text-white">Low Stock Products</h3>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                @forelse(\App\Models\Product::whereColumn('stock_quantity', '<=', 'low_stock_threshold')->take(5)->get() as $product)
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-white font-medium">{{ $product->name }}</p>
                            <p class="text-gray-400 text-sm">{{ $product->category->name }}</p>
                        </div>
                        <span class="px-2 py-1 text-xs rounded bg-red-900 text-red-300">
                            {{ $product->stock_quantity }} left
                        </span>
                    </div>
                @empty
                    <p class="text-gray-400">All products stocked well</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="bg-gray-800 rounded-lg border border-gray-700">
        <div class="p-6 border-b border-gray-700">
            <h3 class="text-lg font-semibold text-white">Top Selling Products</h3>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                @forelse(\App\Models\Product::orderBy('sales_count', 'desc')->take(5)->get() as $product)
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-white font-medium">{{ $product->name }}</p>
                            <p class="text-gray-400 text-sm">${{ number_format($product->price, 2) }}</p>
                        </div>
                        <span class="px-2 py-1 text-xs rounded bg-indigo-900 text-indigo-300">
                            {{ $product->sales_count }} sold
                        </span>
                    </div>
                @empty
                    <p class="text-gray-400">No sales yet</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-gray-800 rounded-lg border border-gray-700">
        <div class="p-6 border-b border-gray-700">
            <h3 class="text-lg font-semibold text-white">Pending Vendors</h3>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                @forelse(\App\Models\Vendor::where('status', 'pending')->latest()->take(5)->get() as $vendor)
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-white font-medium">{{ $vendor->business_name }}</p>
                            <p class="text-gray-400 text-sm">{{ $vendor->business_email }}</p>
                        </div>
                        <span class="px-2 py-1 text-xs rounded bg-yellow-900 text-yellow-300">Pending</span>
                    </div>
                @empty
                    <p class="text-gray-400">No pending vendors</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="bg-gray-800 rounded-lg border border-gray-700">
        <div class="p-6 border-b border-gray-700">
            <h3 class="text-lg font-semibold text-white">Recent Customers</h3>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                @forelse(\App\Models\User::where('role', 'customer')->latest()->take(5)->get() as $user)
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-white font-medium">{{ $user->name }}</p>
                            <p class="text-gray-400 text-sm">{{ $user->email }}</p>
                        </div>
                        <span class="text-gray-400 text-xs">
                            {{ $user->created_at->diffForHumans() }}
                        </span>
                    </div>
                @empty
                    <p class="text-gray-400">No customers yet</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-white">Revenue Trend</h3>
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

<div class="bg-gray-800 rounded-lg border border-gray-700 p-6 mb-6">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-white">Export Reports</h3>
    </div>
    <div class="flex gap-4">
        <a href="{{ route('admin.analytics.export', ['type' => 'orders']) }}" 
           class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm">
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

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
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

let revenueChart;

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

document.addEventListener('DOMContentLoaded', function() {
    loadRevenueChart();
    loadTopProductsChart();
    
    document.getElementById('revenueDays').addEventListener('change', function() {
        loadRevenueChart(this.value);
    });
});
</script>
@endsection
