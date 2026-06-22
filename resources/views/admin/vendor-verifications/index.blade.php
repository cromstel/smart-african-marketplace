@extends('admin.layout')

@section('title', 'Vendor Verifications')
@section('header', 'Vendor Verifications')

@section('content')
<div class="mb-6">
    <h3 class="text-2xl font-bold text-white">Vendor Verification Queue</h3>
    <p class="text-gray-400 mt-1">Review and approve vendor verification documents.</p>
</div>

<div class="mb-6 bg-gray-800 rounded-lg border border-gray-700 p-4">
    <form method="GET" class="flex flex-wrap gap-4">
        <div class="flex-1 min-w-[220px]">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Search vendor or user..."
                   class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
        </div>
        <div>
            <select name="status" class="bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                <option value="">All Statuses</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
        </div>
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg">
            Filter
        </button>
        @if(request()->hasAny(['search', 'status']))
            <a href="{{ route('admin.vendor-verifications.index') }}" class="bg-gray-700 hover:bg-gray-600 text-white px-6 py-2 rounded-lg">
                Clear
            </a>
        @endif
    </form>
</div>

<div class="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden">
    <table class="min-w-full divide-y divide-gray-700">
        <thead class="bg-gray-900">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Vendor</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">User</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Submitted</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-700">
            @forelse($verifications as $verification)
                <tr class="hover:bg-gray-750">
                    <td class="px-6 py-4 text-sm text-white">{{ $verification->vendor?->business_name ?? 'N/A' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-300">{{ $verification->vendor?->user?->email ?? 'N/A' }}</td>
                    <td class="px-6 py-4 text-sm">
                        @php
                            $statusColors = [
                                'pending' => 'bg-yellow-900 text-yellow-300',
                                'approved' => 'bg-green-900 text-green-300',
                                'rejected' => 'bg-red-900 text-red-300',
                            ];
                        @endphp
                        <span class="px-2 py-1 text-xs rounded {{ $statusColors[$verification->status] ?? 'bg-gray-700 text-gray-300' }}">
                            {{ ucfirst($verification->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-300">{{ $verification->created_at?->format('M d, Y') }}</td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('admin.vendor-verifications.show', $verification) }}" class="text-indigo-400 hover:text-indigo-300">
                            View
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-gray-400">No verification records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($verifications->hasPages())
    <div class="mt-6">
        {{ $verifications->links() }}
    </div>
@endif
@endsection

