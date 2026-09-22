@extends('admin.layout')

@section('title', 'Vendor Verification')
@section('header', 'Vendor Verification')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.vendor-verifications.index') }}" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
        ← Back to Verifications
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-gray-800 border border-gray-700 rounded-lg p-6">
            <h3 class="text-lg font-bold text-white mb-4">Vendor Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-300">
                <div>
                    <p class="text-gray-400">Business Name</p>
                    <p class="text-white">{{ $verification->vendor?->business_name ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-gray-400">Contact Email</p>
                    <p class="text-white">{{ $verification->vendor?->business_email ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-gray-400">Contact Phone</p>
                    <p class="text-white">{{ $verification->vendor?->business_phone ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-gray-400">Status</p>
                    <p class="text-white">{{ ucfirst($verification->status) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-gray-800 border border-gray-700 rounded-lg p-6">
            <h3 class="text-lg font-bold text-white mb-4">Documents</h3>
            @if(!empty($verification->documents))
                <ul class="space-y-2">
                    @foreach($verification->documents as $index => $doc)
                        @php
                            $docName = is_array($doc) ? ($doc['original_name'] ?? basename($doc['path'] ?? '')) : basename($doc);
                            $docSize = is_array($doc) && isset($doc['size']) ? number_format($doc['size'] / 1024, 1).' KB' : '';
                        @endphp
                        <li class="flex items-center justify-between bg-gray-900 border border-gray-700 rounded px-3 py-2">
                            <div>
                                <p class="text-white text-sm">{{ $docName }}</p>
                                <p class="text-xs text-gray-400">{{ $docSize }}</p>
                            </div>
                            @if(is_array($doc) && isset($doc['path']))
                                <a href="{{ route('admin.vendor-verifications.document', [$verification, $index]) }}"
                                   class="text-indigo-400 hover:text-indigo-300 text-sm">
                                    Download
                                </a>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-gray-400">No documents uploaded.</p>
            @endif
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-gray-800 border border-gray-700 rounded-lg p-6">
            <h3 class="text-lg font-bold text-white mb-4">Admin Actions</h3>
            @if($verification->status === 'pending')
                <form method="POST" action="{{ route('admin.vendor-verifications.approve', $verification) }}" class="mb-3">
                    @csrf
                    <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
                        Approve Verification
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.vendor-verifications.reject', $verification) }}" class="space-y-3">
                    @csrf
                    <label class="block text-sm text-gray-300">Rejection Notes (optional)</label>
                    <textarea name="notes" rows="3" class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white">{{ old('notes', $verification->notes) }}</textarea>
                    <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg">
                        Reject Verification
                    </button>
                </form>
            @else
                {{--
                    Once a verification is approved/rejected, the server
                    (VendorVerificationController::approve()/reject()) no
                    longer accepts another decision on it -- it only becomes
                    actionable again once the vendor resubmits documents,
                    which resets it to 'pending'. Hiding the buttons here
                    keeps the admin from clicking something that will now
                    just bounce back with "already been reviewed."
                --}}
                <p class="text-sm text-gray-400">
                    This verification has already been {{ $verification->status }}. It will become actionable again if the vendor resubmits documents.
                </p>
            @endif
        </div>

        <div class="bg-gray-800 border border-gray-700 rounded-lg p-6 text-sm text-gray-300">
            <h3 class="text-lg font-bold text-white mb-4">Audit Info</h3>
            <p class="text-gray-400">Submitted IP</p>
            <p class="text-white mb-2">{{ $verification->submitted_ip ?? 'N/A' }}</p>
            <p class="text-gray-400">User Agent</p>
            <p class="text-white mb-2">{{ $verification->submitted_user_agent ?? 'N/A' }}</p>
            <p class="text-gray-400">Reviewed By</p>
            <p class="text-white mb-2">{{ $verification->reviewer?->email ?? 'N/A' }}</p>
            <p class="text-gray-400">Reviewed At</p>
            <p class="text-white">{{ $verification->reviewed_at?->format('M d, Y g:i A') ?? 'N/A' }}</p>
        </div>
    </div>
</div>
@endsection

