@extends('admin.layout')

@section('title', 'Review Export Request')
@section('header', 'Review Export Request')

@section('content')
<div class="space-y-4">
    <div class="bg-gray-800 border border-gray-700 rounded-xl p-6 space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-xl font-semibold text-white">{{ $dataExportRequest->vendor?->business_name ?? $dataExportRequest->requester->name }}</h3>
                <p class="text-sm text-gray-400">Requested {{ $dataExportRequest->created_at->diffForHumans() }}</p>
            </div>
            <span class="px-3 py-1 rounded text-xs uppercase {{ $dataExportRequest->status === 'pending' ? 'bg-yellow-900 text-yellow-300' : ($dataExportRequest->status === 'ready' ? 'bg-green-900 text-green-300' : 'bg-red-900 text-red-300') }}">
                {{ ucfirst($dataExportRequest->status) }}
            </span>
        </div>

        <p class="text-gray-300">{{ $dataExportRequest->reason }}</p>
        @if($dataExportRequest->admin_notes)
            <p class="text-sm text-gray-400">Admin notes: {{ $dataExportRequest->admin_notes }}</p>
        @endif

        @if($dataExportRequest->status === 'ready')
            <a href="{{ route('admin.data-exports.download', ['dataExportRequest' => $dataExportRequest, 'token' => $dataExportRequest->token]) }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg" target="_blank">
                Download Archive
            </a>
        @endif

        <div class="pt-4 border-t border-gray-700">
            <form method="POST" action="{{ route('admin.data-exports.approve', $dataExportRequest) }}" class="inline-flex items-center gap-3">
                @csrf
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg">Approve</button>
            </form>
            <form method="POST" action="{{ route('admin.data-exports.reject', $dataExportRequest) }}" class="inline-flex items-center gap-3">
                @csrf
                <div>
                    <textarea name="admin_notes" rows="2" required class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2" placeholder="Rejection reason"></textarea>
                </div>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg">Reject</button>
            </form>
        </div>
    </div>
</div>
@endsection

