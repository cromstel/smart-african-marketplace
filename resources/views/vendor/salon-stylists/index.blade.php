@extends('vendor.layout')

@section('title', 'My Stylists')
@section('header', 'Stylist Management')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h3 class="text-2xl font-bold text-white">My Stylists</h3>
        <p class="text-gray-400 mt-1">Manage your salon stylists</p>
    </div>
    <a href="{{ route('vendor.salon-stylists.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
        </svg>
        Add Stylist
    </a>
</div>

<div class="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden">
    <table class="min-w-full divide-y divide-gray-700">
        <thead class="bg-gray-900">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Stylist</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Salon</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Experience</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-700">
            @forelse($stylists as $stylist)
                <tr class="hover:bg-gray-750">
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-white">{{ $stylist->name }}</div>
                        <div class="text-xs text-gray-400">{{ $stylist->email ?? 'No email' }}</div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-300">
                        {{ $stylist->salon->name ?? 'N/A' }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-300">
                        {{ $stylist->years_experience ? $stylist->years_experience.' years' : 'N/A' }}
                    </td>
                    <td class="px-6 py-4">
                        @if($stylist->is_active)
                            <span class="px-2 py-1 text-xs rounded bg-green-900 text-green-300">Active</span>
                        @else
                            <span class="px-2 py-1 text-xs rounded bg-red-900 text-red-300">Inactive</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right text-sm font-medium">
                        <a href="{{ route('vendor.salon-stylists.edit', $stylist) }}" class="text-indigo-400 hover:text-indigo-300 mr-3">Edit</a>
                        <form action="{{ route('vendor.salon-stylists.destroy', $stylist) }}" method="POST" class="inline" onsubmit="return confirm('Delete this stylist?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-red-300">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-gray-400">
                        No stylists found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($stylists->hasPages())
    <div class="mt-6">
        {{ $stylists->links() }}
    </div>
@endif
@endsection

