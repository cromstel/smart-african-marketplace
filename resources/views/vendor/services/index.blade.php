@extends('vendor.layout')

@section('title', 'My Services - Vendor Dashboard')
@section('seoDescription', 'Manage your vendor services on Smart African Market')

@section('content')
<div class="flex flex-col lg:flex-row">
    <div class="lg:w-1/2">
        <h2 class="text-lg font-semibold text-white mb-6">My Services</h2>
    </div>
    <div class="lg:w-1/2 flex justify-end mt-4 lg:mt-0">
        <a href="{{ route('vendor.services.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded text-sm font-medium transition-colors">
            Add Service
        </a>
    </div>
</div>

<div class="bg-gray-800 border border-gray-700 rounded-lg p-6 mt-6">
    <table class="w-full text-left">
        <thead>
            <tr class="border-b border-gray-700">
                <th class="py-3 px-4 text-gray-400 text-sm font-medium">Service Name</th>
                <th class="py-3 px-4 text-gray-400 text-sm font-medium">Category</th>
                <th class="py-3 px-4 text-gray-400 text-sm font-medium">Price</th>
                <th class="py-3 px-4 text-gray-400 text-sm font-medium">Status</th>
                <th class="py-3 px-4 text-gray-400 text-sm font-medium">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($services as $service)
            <tr class="border-b border-gray-700">
                <td class="py-3 px-4 text-white">{{ $service->name }}</td>
                <td class="py-3 px-4 text-white">{{ $service->category->name }}</td>
                <td class="py-3 px-4 text-white">${{ number_format($service->price, 2) }}</td>
                <td class="py-3 px-4 text-white">
                    <span class="px-2 py-1 rounded text-xs font-medium
                        @if($service->status === 'available') bg-green-900/30 text-green-400
                        @elseif($service->status === 'booked') bg-yellow-900/30 text-yellow-400
                        @else bg-red-900/30 text-red-400 @endif">
                        {{ ucfirst($service->status) }}
                    </span>
                </td>
                <td class="py-3 px-4">
                    <a href="{{ route('vendor.services.edit', $service) }}" class="text-indigo-400 hover:text-indigo-300 text-sm mr-3">Edit</a>
                    <form method="POST" action="{{ route('vendor.services.destroy', $service) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this service?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-400 hover:text-red-300 text-sm">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="py-4 text-center text-gray-400">No services found. Add your first service to start accepting bookings.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
