@extends('admin.layout')

@section('title', 'Salon Services')
@section('header', 'Salon Service Management')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h3 class="text-2xl font-bold text-white">All Salon Services</h3>
        <p class="text-gray-400 mt-1">Manage salon services and appointments</p>
    </div>
    <a href="{{ route('admin.salon-services.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
        </svg>
        Add Service
    </a>
</div>

<div class="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden">
    <table class="min-w-full divide-y divide-gray-700">
        <thead class="bg-gray-900">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Service</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Category</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Price</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Duration</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Owner</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-700">
            @forelse($services as $service)
                <tr class="hover:bg-gray-750">
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            @if($service->image)
                                <img src="{{ '/storage/' . ltrim($service->image ?? '', '/') }}" alt="{{ $service->name }}" class="w-16 h-12 object-cover rounded mr-3">
                            @else
                                <div class="w-16 h-12 bg-gray-700 rounded mr-3 flex items-center justify-center">
                                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            @endif
                            <div>
                                <div class="text-sm font-medium text-white">{{ $service->name }}</div>
                                <div class="text-xs text-gray-400">{{ Str::limit($service->description, 40) }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        {{-- Prefer the dedicated salon service category over the generic
                             product category -- see SalonService::salonCategory(). --}}
                        <span class="text-sm text-gray-300">{{ $service->salonCategory->name ?? $service->category->name ?? 'N/A' }}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-white">
                        ${{ number_format($service->price, 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                        {{ $service->duration_minutes }} min
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                        {{ $service->user->name ?? 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($service->is_active)
                            <span class="px-2 py-1 text-xs rounded bg-green-900 text-green-300">Active</span>
                        @else
                            <span class="px-2 py-1 text-xs rounded bg-red-900 text-red-300">Inactive</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="{{ route('admin.salon-services.edit', $service) }}" class="text-indigo-400 hover:text-indigo-300 mr-3">Edit</a>
                        <form action="{{ route('admin.salon-services.destroy', $service) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this service?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-red-300">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-gray-400">
                        No salon services found. Create your first service to get started.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($services->hasPages())
    <div class="mt-6">
        {{ $services->links() }}
    </div>
@endif
@endsection

