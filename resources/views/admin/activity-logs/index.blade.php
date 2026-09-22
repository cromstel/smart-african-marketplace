@extends('admin.layout')

@section('title', 'Activity Logs')
@section('header', 'Activity Logs')

@section('content')
<div class="bg-gray-800 rounded-lg border border-gray-700">
    <div class="px-4 py-3 border-b border-gray-700 text-white font-semibold">Activity Logs</div>
    <div class="divide-y divide-gray-700">
        @forelse($logs as $log)
            <div class="px-4 py-3">
                <div class="text-gray-300">{{ $log->action }}</div>
                <div class="text-xs text-gray-400">{{ $log->actor?->email ?? 'system' }} · {{ $log->created_at->format('M d, Y H:i') }}</div>
            </div>
        @empty
            <div class="px-4 py-6 text-gray-400">No activity logged.</div>
        @endforelse
    </div>
</div>

<div class="mt-6">
    {{ $logs->links() }}
</div>
@endsection

