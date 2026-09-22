@extends('vendor.layout')

@section('title', 'Verification')

@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-bold text-white">Vendor Verification</h2>
    <p class="text-gray-400 mt-1">Submit documents for verification and track approval status.</p>
</div>

<div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <p class="text-gray-400">Status: <span class="text-white">{{ ucfirst($verification->status) }}</span></p>
        @if($verification->verified_at)
            <p class="text-sm text-gray-400">Verified: {{ $verification->verified_at->format('M d, Y') }}</p>
        @endif
    </div>

    @if(!empty($verification->documents))
        <div class="mt-4">
            <h3 class="text-sm font-semibold text-gray-300 mb-2">Submitted Documents</h3>
            <ul class="space-y-2 text-sm text-gray-400">
                @foreach($verification->documents as $doc)
                    @php
                        $docName = is_array($doc) ? ($doc['original_name'] ?? basename($doc['path'] ?? '')) : basename($doc);
                        $docSize = is_array($doc) && isset($doc['size']) ? number_format($doc['size'] / 1024, 1).' KB' : '';
                    @endphp
                    <li class="flex items-center justify-between bg-gray-900 border border-gray-700 rounded px-3 py-2">
                        <span>{{ $docName }}</span>
                        <span class="text-xs text-gray-400">{{ $docSize }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('vendor.verification.store') }}" enctype="multipart/form-data" class="mt-4 space-y-4">
        @csrf
        <div>
            <label class="block text-sm text-gray-300 mb-2">Documents (PDF, JPG, PNG, WebP)</label>
            <input type="file" name="documents[]" multiple class="w-full text-gray-300">
            <p class="text-xs text-gray-400 mt-1">Upload business registration, ID, and proof of address.</p>
        </div>
        <div>
            <label class="block text-sm text-gray-300 mb-2" for="notes">Notes</label>
            <textarea name="notes" id="notes" rows="3" class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white">{{ old('notes', $verification->notes) }}</textarea>
        </div>
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded">Submit</button>
    </form>
</div>
@endsection

