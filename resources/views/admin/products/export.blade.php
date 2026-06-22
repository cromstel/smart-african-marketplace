@extends('admin.layout')

@push('styles')
<style>
.status-badge-completed { background-color: #14532d; color: #bbf7d0; }
.status-badge-failed { background-color: #7f1d1d; color: #fecaca; }
.status-badge-pending { background-color: #713f12; color: #fde047; }
.status-badge-processing { background-color: #1e3a8a; color: #bfdbfe; }
</style>
@endpush

@section('title', 'Export Products')
@section('header', 'Export Products')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Export Form -->
    <div class="lg:col-span-2">
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-white mb-4">Export Products</h3>
            
            <form action="{{ route('admin.products.export.process') }}" method="POST" class="space-y-6">
                @csrf

                <!-- Export Format -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Export Format</label>
                    <div class="grid grid-cols-2 gap-4">
                        <label class="relative flex items-center p-4 border-2 border-indigo-500 rounded-lg cursor-pointer bg-gray-700">
                            <input type="radio" name="format" value="csv" checked class="sr-only">
                            <div class="flex-1">
                                <div class="flex items-center">
                                    <svg class="h-8 w-8 text-indigo-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <span class="font-medium text-white">CSV</span>
                                </div>
                                <p class="text-xs text-gray-400 mt-1">Comma-separated values</p>
                            </div>
                            <div class="absolute top-2 right-2">
                                <svg class="h-5 w-5 text-indigo-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Filters -->
                <div class="border-t border-gray-700 pt-6">
                    <h4 class="text-sm font-medium text-gray-300 mb-4">Filter Products</h4>
                    
                    <!-- Category Filter -->
                    <div class="mb-4">
                        <label for="category_id" class="block text-sm font-medium text-gray-300 mb-2">Category</label>
                        <select id="category_id" name="category_id" class="bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Vendor Filter -->
                    <div class="mb-4">
                        <label for="vendor_id" class="block text-sm font-medium text-gray-300 mb-2">Vendor</label>
                        <select id="vendor_id" name="vendor_id" class="bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">All Vendors</option>
                            @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Stock Status Filter -->
                    <div class="mb-4">
                        <label for="status" class="block text-sm font-medium text-gray-300 mb-2">Stock Status</label>
                        <select id="status" name="status" class="bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="all">All Products</option>
                            <option value="in_stock">In Stock</option>
                            <option value="out_of_stock">Out of Stock</option>
                            <option value="low_stock">Low Stock</option>
                        </select>
                    </div>

                    <!-- Active Status -->
                    <div class="flex items-start mb-4">
                        <div class="flex items-center h-5">
                            <input id="is_active" name="is_active" type="checkbox" value="1" checked class="h-4 w-4 text-indigo-600 bg-gray-700 border-gray-600 rounded focus:ring-indigo-500">
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="is_active" class="font-medium text-gray-300">Active products only</label>
                            <p class="text-gray-500">Export only active products</p>
                        </div>
                    </div>
                </div>

                <!-- Column Selection -->
                <div class="border-t border-gray-700 pt-6">
                    <h4 class="text-sm font-medium text-gray-300 mb-4">Select Columns to Export</h4>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        @php
                        $columns = [
                            'id' => 'ID',
                            'sku' => 'SKU',
                            'name' => 'Name',
                            'slug' => 'Slug',
                            'description' => 'Description',
                            'short_description' => 'Short Description',
                            'price' => 'Price',
                            'compare_price' => 'Compare Price',
                            'cost_price' => 'Cost Price',
                            'stock_quantity' => 'Stock Quantity',
                            'low_stock_threshold' => 'Low Stock Threshold',
                            'stock_status' => 'Stock Status',
                            'weight' => 'Weight',
                            'origin_country' => 'Origin Country',
                            'is_featured' => 'Featured',
                            'is_active' => 'Active',
                            'views_count' => 'Views Count',
                            'sales_count' => 'Sales Count',
                            'average_rating' => 'Average Rating',
                            'reviews_count' => 'Reviews Count',
                            'category' => 'Category',
                            'vendor' => 'Vendor',
                            'created_at' => 'Created At',
                            'updated_at' => 'Updated At',
                        ];
                        @endphp
                        @foreach($columns as $value => $label)
                            <label class="flex items-center space-x-2">
                                <input type="checkbox" name="columns[]" value="{{ $value }}" checked 
                                    class="rounded bg-gray-700 border-gray-600 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm text-gray-300">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end pt-4">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg font-medium transition-colors flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Export Products
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <!-- Export Info -->
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-white mb-4">Export Information</h3>
            <div class="space-y-3">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-400">Total Products:</span>
                    <span class="text-white font-medium">{{ \App\Models\Product::count() }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-400">Active Products:</span>
                    <span class="text-white font-medium">{{ \App\Models\Product::where('is_active', true)->count() }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-400">Out of Stock:</span>
                    <span class="text-white font-medium">{{ \App\Models\Product::where('stock_quantity', '<=', 0)->count() }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-400">Low Stock:</span>
                    <span class="text-white font-medium">{{ \App\Models\Product::whereColumn('stock_quantity', '<=', 'low_stock_threshold')->count() }}</span>
                </div>
            </div>
        </div>

        <!-- Recent Exports -->
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-white mb-4">Recent Exports</h3>
            @if($recentExports->count() > 0)
                <div class="space-y-3">
                    @foreach($recentExports as $export)
                        <div class="flex items-center justify-between py-2 border-b border-gray-700 last:border-0">
                            <div>
                                <p class="text-sm text-white truncate max-w-[150px]">{{ $export->file_name }}</p>
                                <p class="text-xs text-gray-500">{{ $export->created_at->format('M d, Y H:i') }}</p>
                            </div>
                            <span class="px-2 py-1 text-xs rounded-full status-badge-{{ $export->status }}">
                                {{ ucfirst($export->status) }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-500">No recent exports</p>
            @endif
        </div>

        <!-- Tips -->
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-white mb-4">Tips</h3>
            <ul class="text-sm text-gray-400 space-y-2">
                <li class="flex items-start">
                    <svg class="h-5 w-5 text-indigo-400 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Use filters to export specific products
                </li>
                <li class="flex items-start">
                    <svg class="h-5 w-5 text-indigo-400 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Select specific columns to reduce file size
                </li>
                <li class="flex items-start">
                    <svg class="h-5 w-5 text-indigo-400 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    CSV files can be opened in Excel
                </li>
            </ul>
        </div>
    </div>
</div>
@endsection

