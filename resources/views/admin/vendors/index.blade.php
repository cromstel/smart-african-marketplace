@extends('admin.layout')

@section('title', 'Vendors')
@section('header', 'Vendors Management')

@section('content')
@if(session('success'))
    <div class="mb-6 bg-green-900 border border-green-700 text-green-200 px-6 py-4 rounded-lg">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="mb-6 bg-red-900 border border-red-700 text-red-200 px-6 py-4 rounded-lg">
        {{ session('error') }}
    </div>
@endif

<div class="mb-6 flex justify-between items-center">
    <div>
        <h3 class="text-2xl font-bold text-white">All Vendors</h3>
        <p class="text-gray-400 mt-1">Manage marketplace vendors</p>
    </div>
    <a href="{{ route('admin.vendors.create') }}" class="bg-gradient-to-r from-orange-600 to-red-600 text-white px-6 py-3 rounded-lg font-semibold hover:from-orange-700 hover:to-red-700 transition inline-flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Create Vendor
    </a>
</div>

<div class="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden">
    <table class="min-w-full divide-y divide-gray-700">
        <thead class="bg-gray-900">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Business</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Contact</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Commission</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Joined</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-700">
            @forelse($vendors as $vendor)
                <tr class="hover:bg-gray-750">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-white">{{ $vendor->business_name }}</div>
                        <div class="text-sm text-gray-400">{{ $vendor->user->name }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                        <div>{{ $vendor->business_email }}</div>
                        <div class="text-gray-400">{{ $vendor->business_phone }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-white">
                        {{ number_format($vendor->commission_rate, 2) }}%
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($vendor->status === 'approved')
                            <span class="px-2 py-1 text-xs rounded bg-green-900 text-green-300">Approved</span>
                        @elseif($vendor->status === 'pending')
                            <span class="px-2 py-1 text-xs rounded bg-yellow-900 text-yellow-300">Pending</span>
                        @elseif($vendor->status === 'suspended')
                            <span class="px-2 py-1 text-xs rounded bg-red-900 text-red-300">Suspended</span>
                        @else
                            <span class="px-2 py-1 text-xs rounded bg-gray-700 text-gray-400">{{ ucfirst($vendor->status) }}</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                        {{ $vendor->created_at->format('M d, Y') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="{{ route('admin.vendors.show', $vendor) }}" class="text-indigo-400 hover:text-indigo-300 mr-3">View</a>
                        <a href="{{ route('admin.vendors.edit', $vendor) }}" class="text-blue-400 hover:text-blue-300 mr-3">Edit</a>
                        <form action="{{ route('admin.vendors.destroy', $vendor) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this vendor? This will also delete the associated user account.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-red-300">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                        No vendors yet
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">
    {{ $vendors->links() }}
</div>
@endsection

