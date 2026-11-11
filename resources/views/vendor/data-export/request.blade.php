@extends('vendor.layout')

@section('title', 'Request Data Export')
@section('header', 'Request Data Export')

@section('content')
<div class="max-w-3xl space-y-6">
    <div class="bg-gray-800 border border-gray-700 rounded-lg p-6 space-y-4">
        @if($existing)
            <div class="text-sm text-gray-300">
                Your current request is <strong class="text-white">{{ ucfirst($existing->status) }}</strong>.
                @if($existing->status === 'ready')
                    <a href="{{ route('admin.data-exports.download', ['dataExportRequest' => $existing, 'token' => $existing->token]) }}" class="text-indigo-400 hover:text-indigo-300">Download it</a>
                @elseif($existing->status === 'rejected')
                    <p class="text-xs text-gray-400">Reason: {{ $existing->admin_notes }}</p>
                @endif
            </div>
        @else
            <p class="text-sm text-gray-400">Submit a request and an admin will review it within 24 hours.</p>
        @endif

        <form method="POST" action="{{ route('vendor.data-export.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm text-gray-300 mb-2">Reason for export</label>
                <input type="text" name="reason" value="{{ old('reason') }}" required class="w-full bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-white">
            </div>
            <div>
                <label class="block text-sm text-gray-300 mb-2">Additional notes</label>
                <textarea name="notes" rows="3" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-white">{{ old('notes') }}</textarea>
            </div>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg" {{ $existing ? 'disabled' : '' }}>
                {{ $existing ? 'Request Pending' : 'Submit Request' }}
            </button>
        </form>
    </div>
</div>
@endsection

