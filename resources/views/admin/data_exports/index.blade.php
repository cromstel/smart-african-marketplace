@extends('admin.layout')

@section('title', 'Data Export Requests')
@section('header', 'Data Export Requests')

@section('content')
<div class="space-y-4">
    <form method="GET" class="grid grid-cols-1 lg:grid-cols-3 gap-3">
        <select name="status" class="bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-white">
            <option value="">All statuses</option>
            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="ready" {{ request('status') === 'ready' ? 'selected' : '' }}>Ready</option>
            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
        </select>
        <input type="text" name="vendor_id" value="{{ request('vendor_id') }}" placeholder="Vendor ID" class="bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-white">
        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg">Filter</button>
    </form>

    <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-700">
            <thead class="bg-gray-900">
                <tr>
                    <th class="px-4 py-3 text-left text-xs uppercase text-gray-400">Vendor</th>
                    <th class="px-4 py-3 text-left text-xs uppercase text-gray-400">Reason</th>
                    <th class="px-4 py-3 text-left text-xs uppercase text-gray-400">Status</th>
                    <th class="px-4 py-3 text-left text-xs uppercase text-gray-400">Requested At</th>
                    <th class="px-4 py-3 text-right text-xs uppercase text-gray-400">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                @forelse($exportRequests as $request)
                    <tr>
                        <td class="px-4 py-3 text-white">
                            {{ $request->vendor?->business_name ?? $request->requester->name }}
                        </td>
                        <td class="px-4 py-3 text-gray-300">{{ $request->reason }}</td>
                        <td class="px-4 py-3">
                            <span class="text-xs px-2 py-1 rounded {{ $request->status === 'pending' ? 'bg-yellow-900 text-yellow-300' : ($request->status === 'ready' ? 'bg-green-900 text-green-300' : 'bg-red-900 text-red-300') }}">
                                {{ ucwords($request->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-400">{{ $request->created_at->format('Y-m-d H:i') }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.data-exports.show', $request) }}" class="text-indigo-400 hover:text-indigo-300 text-sm">Manage</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-gray-400">No requests yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $exportRequests->links() }}
</div>
@endsection

