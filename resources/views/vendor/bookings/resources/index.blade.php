@extends('vendor.layout')

@section('title', 'Rental Resources')
@section('header', 'Rental Resources')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div class="flex items-center gap-3">
        <a href="{{ route('vendor.booking-resources.index') }}"
           class="px-4 py-2 rounded {{ !request('status') ? 'bg-indigo-600 text-white' : 'bg-gray-800 text-gray-300 hover:bg-gray-700' }}">
            All
        </a>
        <a href="{{ route('vendor.booking-resources.index', ['status' => 'active']) }}"
           class="px-4 py-2 rounded {{ request('status') === 'active' ? 'bg-indigo-600 text-white' : 'bg-gray-800 text-gray-300 hover:bg-gray-700' }}">
            Active <span class="ml-1">({{ $statusCounts['active'] }})</span>
        </a>
        <a href="{{ route('vendor.booking-resources.index', ['status' => 'inactive']) }}"
           class="px-4 py-2 rounded {{ request('status') === 'inactive' ? 'bg-indigo-600 text-white' : 'bg-gray-800 text-gray-300 hover:bg-gray-700' }}">
            Inactive <span class="ml-1">({{ $statusCounts['inactive'] }})</span>
        </a>
    </div>
    <a href="{{ route('vendor.booking-resources.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-semibold text-center">
        + New Resource
    </a>
</div>

<form method="GET" action="{{ route('vendor.booking-resources.index') }}" class="mb-6 flex flex-col sm:flex-row gap-3">
    <input type="hidden" name="status" value="{{ request('status') }}">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or description..."
           class="flex-1 bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-white placeholder-gray-500 focus:outline-none focus:border-indigo-500">
    <select name="type" class="bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-indigo-500">
        <option value="">All Types</option>
        @foreach(['equipment' => 'Equipment', 'vehicle' => 'Vehicle', 'property' => 'Property', 'facility' => 'Facility', 'other' => 'Other'] as $value => $label)
            <option value="{{ $value }}" {{ request('type') === $value ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
    </select>
    <button type="submit" class="bg-gray-700 hover:bg-gray-600 text-white px-6 py-2 rounded-lg font-semibold">Filter</button>
</form>

<div class="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-900">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Resource</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Type</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Pricing</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Availability</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-700">
            @forelse($resources as $resource)
                <tr class="hover:bg-gray-700">
                    <td class="px-6 py-4">
                        <div class="flex items-center space-x-3">
                            @if($resource->image)
                                <img src="{{ '/storage/' . ltrim($resource->image, '/') }}" alt="{{ $resource->name }}" class="w-10 h-10 object-cover rounded">
                            @else
                                <div class="w-10 h-10 bg-gray-700 rounded flex items-center justify-center">
                                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>
                                </div>
                            @endif
                            <div>
                                <span class="text-gray-200 text-sm font-medium">{{ Str::limit($resource->name, 30) }}</span>
                                @if($resource->sku)
                                    <p class="text-gray-500 text-xs">SKU: {{ $resource->sku }}</p>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-300 text-sm">
                        {{ ucfirst($resource->resource_type) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-300 text-sm">
                        {{ $resource->price_label }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-300 text-sm">
                        {{ $resource->quantity_available }} / {{ $resource->quantity_total }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs rounded {{ $resource->is_active ? 'bg-green-900 text-green-300' : 'bg-gray-700 text-gray-400' }}">
                            {{ $resource->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm space-x-3">
                        <a href="{{ route('vendor.booking-resources.show', $resource) }}" class="text-indigo-400 hover:text-indigo-300">View</a>
                        <a href="{{ route('vendor.booking-resources.edit', $resource) }}" class="text-indigo-400 hover:text-indigo-300">Edit</a>
                        <form method="POST" action="{{ route('vendor.booking-resources.toggle', $resource) }}" class="inline">
                            @csrf
                            <button type="submit" class="text-yellow-400 hover:text-yellow-300">
                                {{ $resource->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>
                        <form method="POST" action="{{ route('vendor.booking-resources.destroy', $resource) }}" class="inline" onsubmit="return confirm('Delete this resource? This cannot be undone.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-red-300">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center justify-center space-y-3">
                            <svg class="w-16 h-16 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                            <p class="text-gray-400">No rental resources yet</p>
                            <a href="{{ route('vendor.booking-resources.create') }}" class="text-indigo-400 hover:text-indigo-300 text-sm">Add your first resource</a>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($resources->hasPages())
    <div class="mt-6">
        {{ $resources->appends(request()->query())->links() }}
    </div>
@endif
@endsection
