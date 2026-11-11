@extends('vendor.layout')

@section('title', 'My Products')
@section('header', 'My Products')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
    <div>
        <h3 class="text-2xl font-bold text-white">My Products</h3>
        <p class="text-gray-400 mt-1">Manage product catalog, taxonomy, and inventory with bulk actions</p>
    </div>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('vendor.inventory.index') }}" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-3 rounded-lg inline-flex items-center transition text-sm">
            Inventory
        </a>
        <a href="{{ route('vendor.products.create') }}" class="bg-orange-600 hover:bg-orange-700 text-white px-6 py-3 rounded-lg inline-flex items-center transition">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Add Product
        </a>
    </div>
</div>

@livewire('vendor.bulk-product-actions')
@endsection

