@extends('admin.layout')

@section('title', 'Configuration Audit Log')
@section('header', 'Configuration Audit Log')

@section('content')
<div class="max-w-6xl">
    <div class="mb-6 flex items-center justify-between">
        <p class="text-gray-400">View all configuration changes and modifications.</p>
        <a href="{{ route('admin.settings.index') }}" class="text-indigo-400 hover:text-indigo-300">
            &larr; Back to Settings
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-gray-800 rounded-lg border border-gray-700 p-4 mb-6">
        <form method="GET" action="{{ route('admin.settings.audit') }}" class="flex gap-4 items-end">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-300 mb-2">Filter by Setting Key</label>
                <input type="text" name="key" value="{{ request('key') }}" 
                       class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                       placeholder="e.g., stripe_secret_key">
            </div>
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-300 mb-2">Filter by User</label>
                <select name="user_id" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                    <option value="">All Users</option>
                    @foreach(\App\Models\User::where('is_admin', true)->get() as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg">
                Filter
            </button>
            <a href="{{ route('admin.settings.audit') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                Reset
            </a>
        </form>
    </div>

    <!-- Audit Log Table -->
    <div class="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-900">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Timestamp</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Setting Key</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Action</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Old Value</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">New Value</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">IP Address</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                @forelse($audits as $audit)
                <tr class="hover:bg-gray-750">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                        {{ $audit->created_at->format('Y-m-d H:i:s') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                        {{ $audit->user?->name ?? 'System' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-white font-mono">
                        {{ $audit->setting_key }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @switch($audit->action)
                            @case('created')
                                <span class="px-2 py-1 text-xs font-medium bg-green-900 text-green-300 rounded">Created</span>
                                @break
                            @case('updated')
                                <span class="px-2 py-1 text-xs font-medium bg-blue-900 text-blue-300 rounded">Updated</span>
                                @break
                            @case('deleted')
                                <span class="px-2 py-1 text-xs font-medium bg-red-900 text-red-300 rounded">Deleted</span>
                                @break
                            @case('exported')
                                <span class="px-2 py-1 text-xs font-medium bg-purple-900 text-purple-300 rounded">Exported</span>
                                @break
                            @case('imported')
                                <span class="px-2 py-1 text-xs font-medium bg-yellow-900 text-yellow-300 rounded">Imported</span>
                                @break
                            @default
                                <span class="px-2 py-1 text-xs font-medium bg-gray-700 text-gray-300 rounded">{{ $audit->action }}</span>
                        @endswitch
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-400 font-mono max-w-xs truncate" title="{{ $audit->old_value }}">
                        {{ $audit->getMaskedOldValue() }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-400 font-mono max-w-xs truncate" title="{{ $audit->new_value }}">
                        {{ $audit->getMaskedNewValue() }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                        {{ $audit->ip_address ?? 'N/A' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-gray-400">
                        No audit logs found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $audits->links() }}
    </div>
</div>
@endsection
