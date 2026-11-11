@extends('vendor.layout')

@section('title', 'Add Service - Vendor Dashboard')
@section('seoDescription', 'Add a new service to your vendor profile on Smart African Market')

@section('content')
<div class="max-w-lg mx-auto">
    <h2 class="text-lg font-semibold text-white mb-6">Add New Service</h2>

    <form method="POST" action="{{ route('vendor.services.store') }}" enctype="multipart/form-data" class="bg-gray-800 border border-gray-700 rounded-lg p-6">
        @csrf

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-300 mb-2">Service Category</label>
            <select name="vendor_service_category_id" required
                class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                <option value="">Select a category</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }} ({{ ucfirst($category->gender_target ?? 'unisex') }})</option>
                @endforeach
            </select>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-300 mb-2">Service Name</label>
            <input type="text" name="name" required
                class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-300 mb-2">Description</label>
            <textarea name="description" rows="4"
                class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"></textarea>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Price ($)</label>
                <input type="number" name="price" step="0.01" min="0" required
                    class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Duration (minutes)</label>
                <input type="number" name="duration_minutes" min="15" max="480" value="60" required
                    class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
            </div>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-300 mb-2">Service Image</label>
            <input type="file" name="image" accept="image/*"
                class="w-full bg-gray-700 border border-gray-600 text-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
        </div>

        <div class="grid grid-cols-2 gap-4 mb-6">
            <div>
                <label class="flex items-center space-x-2 text-sm">
                    <input type="checkbox" name="is_featured" value="1" class="rounded text-indigo-600 focus:ring-indigo-500">
                    Featured Service
                </label>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Status</label>
                <select name="status" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                    <option value="available">Available</option>
                    <option value="booked">Booked</option>
                    <option value="unavailable">Unavailable</option>
                </select>
            </div>
        </div>

        <div class="flex justify-end space-x-3">
            <a href="{{ route('vendor.services.index') }}" class="px-4 py-2 text-gray-300 hover:bg-gray-700 rounded text-sm">Cancel</a>
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded text-sm font-medium transition-colors">
                Create Service
            </button>
        </div>
    </form>
</div>
@endsection
