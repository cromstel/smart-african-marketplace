@extends('layouts.salon-console')

@section('title', 'Services - Salon Console')

@section('content')
<div class="flex flex-col lg:flex-row">
    <div class="lg:w-1/2">
        <h2 class="text-lg font-semibold text-white mb-6">Manage Services</h2>
    </div>
    <div class="lg:w-1/2 flex justify-end">
        <a href="{{ route('salon.services.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded text-sm font-medium transition-colors">
            Add Service
        </a>
    </div>
</div>

<div class="bg-gray-800 border border-gray-700 rounded-lg p-6 mt-6">
    <table class="w-full text-left">
        <thead>
            <tr class="border-b border-gray-700">
                <th class="py-3 px-4 text-gray-400 text-sm font-medium">Service Name</th>
                <th class="py-3 px-4 text-gray-400 text-sm font-medium">Price</th>
                <th class="py-3 px-4 text-gray-400 text-sm font-medium">Duration</th>
                <th class="py-3 px-4 text-gray-400 text-sm font-medium">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($services as $service)
            <tr class="border-b border-gray-700">
                <td class="py-3 px-4 text-white">{{ $service->name }}</td>
                <td class="py-3 px-4 text-white">${{ number_format($service->price, 2) }}</td>
                <td class="py-3 px-4 text-white">{{ $service->duration }} min</td>
                <td class="py-3 px-4">
                    <a href="{{ route('salon.services.edit', $service) }}" class="text-indigo-400 hover:text-indigo-300 text-sm">Edit</a>
                    <form method="POST" action="{{ route('salon.services.destroy', $service) }}" class="inline" onsubmit="return confirm('Are you sure?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-400 hover:text-red-300 text-sm">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="py-4 text-center text-gray-400">No services found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
