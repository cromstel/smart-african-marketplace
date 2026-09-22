@extends('admin.layout')

@push('styles')
<style>
.status-badge-completed { background-color: #14532d; color: #bbf7d0; }
.status-badge-failed { background-color: #7f1d1d; color: #fecaca; }
.status-badge-pending { background-color: #713f12; color: #fde047; }
.status-badge-processing { background-color: #1e3a8a; color: #bfdbfe; }
</style>
@endpush

@section('title', 'Import Products')
@section('header', 'Import Products')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Import Form -->
    <div class="lg:col-span-2">
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-white mb-4">Upload CSV File</h3>
            
            <form action="{{ route('admin.products.import.process') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                
                <!-- File Upload -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Select CSV File</label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-600 border-dashed rounded-lg hover:border-gray-500 transition-colors">
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-gray-400">
                                <label for="file-upload" class="relative cursor-pointer bg-gray-700 rounded-md font-medium text-indigo-400 hover:text-indigo-300 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500 px-2">
                                    <span>Upload a file</span>
                                    <input id="file-upload" name="file" type="file" accept=".csv,.txt,.xlsx" class="sr-only" required>
                                </label>
                                <p class="pl-1">or drag and drop</p>
                            </div>
                            <p class="text-xs text-gray-400">CSV, TXT or XLSX up to 10MB</p>
                        </div>
                    </div>
                    @error('file')
                        <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- File Info Display -->
                <div id="file-info" class="hidden bg-gray-700 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <svg class="h-8 w-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-white" id="file-name"></p>
                                <p class="text-xs text-gray-400" id="file-size"></p>
                            </div>
                        </div>
                        <button type="button" onclick="removeFile()" class="text-gray-400 hover:text-red-400" aria-label="Remove selected file">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Update Existing -->
                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input id="update_existing" name="update_existing" type="checkbox" value="1" class="h-4 w-4 text-indigo-600 bg-gray-700 border-gray-600 rounded focus:ring-indigo-500">
                    </div>
                    <div class="ml-3 text-sm">
                        <label for="update_existing" class="font-medium text-gray-300">Update existing products</label>
                        <p class="text-gray-400">If a product with the same SKU exists, it will be updated instead of created</p>
                    </div>
                </div>

                <!-- Default Category -->
                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-300 mb-2">Default Category (Optional)</label>
                    <select id="category_id" name="category_id" class="bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Select a category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-400">Products without a category in the CSV will use this category</p>
                </div>

                <!-- Default Vendor -->
                <div>
                    <label for="vendor_id" class="block text-sm font-medium text-gray-300 mb-2">Default Vendor (Optional)</label>
                    <select id="vendor_id" name="vendor_id" class="bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Select a vendor</option>
                        @foreach($vendors as $vendor)
                            <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-400">Products without a vendor in the CSV will use this vendor</p>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg font-medium transition-colors flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        Import Products
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <!-- CSV Format Guide -->
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-white mb-4">CSV Format Guide</h3>
            <div class="space-y-3">
                <p class="text-sm text-gray-400">Required columns:</p>
                <ul class="text-sm text-gray-300 space-y-1">
                    <li class="flex items-center">
                        <span class="text-red-400 mr-2">*</span> name
                    </li>
                    <li class="flex items-center">
                        <span class="text-red-400 mr-2">*</span> price
                    </li>
                    <li class="flex items-center">
                        <span class="text-red-400 mr-2">*</span> stock_quantity
                    </li>
                </ul>
                <p class="text-sm text-gray-400 pt-2">Optional columns:</p>
                <ul class="text-sm text-gray-300 space-y-1">
                    <li>sku, description, short_description</li>
                    <li>compare_price, cost_price, weight</li>
                    <li>category, vendor, origin_country</li>
                    <li>is_featured, is_active</li>
                </ul>
            </div>
            <div class="mt-4 pt-4 border-t border-gray-700">
                <a href="#" onclick="downloadSample()" class="text-indigo-400 hover:text-indigo-300 text-sm flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Download Sample CSV
                </a>
            </div>
        </div>

        <!-- Recent Imports -->
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-white mb-4">Recent Imports</h3>
            @if($recentImports->count() > 0)
                <div class="space-y-3">
                    @foreach($recentImports as $import)
                        <div class="flex items-center justify-between py-2 border-b border-gray-700 last:border-0">
                            <div>
                                <p class="text-sm text-white truncate max-w-[150px]">{{ $import->file_name }}</p>
                                <p class="text-xs text-gray-400">{{ $import->created_at->format('M d, Y H:i') }}</p>
                            </div>
                            <span class="px-2 py-1 text-xs rounded-full status-badge-{{ $import->status }}">
                                {{ ucfirst($import->status) }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-400">No recent imports</p>
            @endif
        </div>
    </div>
</div>

@if(session('warning'))
    <div class="mt-6 bg-yellow-900 border border-yellow-700 text-yellow-100 px-4 py-3 rounded">
        <div class="flex">
            <svg class="flex-shrink-0 h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            {{ session('warning') }}
        </div>
    </div>
@endif

@if(session('error'))
    <div class="mt-6 bg-red-900 border border-red-700 text-red-100 px-4 py-3 rounded">
        <div class="flex">
            <svg class="flex-shrink-0 h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ session('error') }}
        </div>
    </div>
@endif
@endsection

@push('scripts')
<script>
    const fileInput = document.getElementById('file-upload');
    const fileInfo = document.getElementById('file-info');
    const fileName = document.getElementById('file-name');
    const fileSize = document.getElementById('file-size');

    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            fileName.textContent = file.name;
            fileSize.textContent = formatFileSize(file.size);
            fileInfo.classList.remove('hidden');
        }
    });

    function removeFile() {
        fileInput.value = '';
        fileInfo.classList.add('hidden');
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    function downloadSample() {
        const csvContent = 'sku,name,description,short_description,price,compare_price,cost_price,stock_quantity,low_stock_threshold,weight,origin_country,category,vendor,is_featured,is_active\nPRD-00001,Sample Product 1,This is a sample product description,Sample short description,29.99,39.99,15.00,100,10,0.5,Ghana,Electronics,false,true\nPRD-00002,Sample Product 2,Another sample product,Short desc,49.99,,25.00,50,5,0.3,Nigeria,Clothing,true,true';
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'sample_products.csv';
        link.click();
    }
</script>
@endpush

