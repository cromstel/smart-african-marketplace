@extends('admin.layout')

@section('title', 'Vendors')
@section('header', 'Vendors Management')

@section('content')
<div class="mb-6">
    <h3 class="text-2xl font-bold text-white">All Vendors</h3>
    <p class="text-gray-400 mt-1">Manage marketplace vendors</p>
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
                        <a href="{{ route('admin.vendors.edit', $vendor) }}" class="text-blue-400 hover:text-blue-300">Edit</a>
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
