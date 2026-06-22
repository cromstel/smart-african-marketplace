@extends('admin.layout')

@section('title', 'Salon')
@section('header', 'Salon Management')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h3 class="text-2xl font-bold text-white">All Salons</h3>
        <p class="text-gray-400 mt-1">Manage salon profiles and availability</p>
    </div>
    <a href="{{ route('admin.salon.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
        </svg>
        Add Salon
    </a>
</div>

<div class="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden">
    <table class="min-w-full divide-y divide-gray-700">
        <thead class="bg-gray-900">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Salon</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Owner</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Location</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-700">
            @forelse($salons as $salon)
                <tr class="hover:bg-gray-750">
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-white">{{ $salon->name }}</div>
                        <div class="text-xs text-gray-400">{{ Str::limit($salon->description, 40) }}</div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-300">
                        {{ $salon->user->name ?? 'N/A' }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-300">
                        {{ $salon->city ?? 'N/A' }}{{ $salon->state ? ', '.$salon->state : '' }}
                    </td>
                    <td class="px-6 py-4">
                        @if($salon->is_active)
                            <span class="px-2 py-1 text-xs rounded bg-green-900 text-green-300">Active</span>
                        @else
                            <span class="px-2 py-1 text-xs rounded bg-red-900 text-red-300">Inactive</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right text-sm font-medium">
                        <a href="{{ route('admin.salon.edit', $salon) }}" class="text-indigo-400 hover:text-indigo-300 mr-3">Edit</a>
                        <form action="{{ route('admin.salon.destroy', $salon) }}" method="POST" class="inline" onsubmit="return confirm('Delete this salon?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-red-300">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-gray-400">
                        No salons found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($salons->hasPages())
    <div class="mt-6">
        {{ $salons->links() }}
    </div>
@endif
@endsection

