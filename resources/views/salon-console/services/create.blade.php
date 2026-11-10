@extends('layouts.salon-console')

@section('title', 'Create Service - Salon Console')

@section('content')
<div class="max-w-lg mx-auto">
    <h2 class="text-lg font-semibold text-white mb-6">Add New Service</h2>

    <form method="POST" action="{{ route('salon.services.store') }}" class="bg-gray-800 border border-gray-700 rounded-lg p-6">
        @csrf

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-300 mb-2">Service Name</label>
            <input type="text" name="name" required
                   class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-300 mb-2">Description</label>
            <textarea name="description" rows="4" required
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
                <input type="number" name="duration" min="15" required
                       class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
            </div>
        </div>

        <div class="flex justify-end space-x-3">
            <a href="{{ route('salon.services.index') }}" class="px-4 py-2 text-gray-300 hover:bg-gray-700 rounded text-sm">Cancel</a>
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded text-sm font-medium transition-colors">
                Create Service
            </button>
        </div>
    </form>
</div>
@endsection
