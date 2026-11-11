@extends('layouts.shop')

@section('title', 'Vendor Application Status - Smart African Market')
@section('seoDescription', 'Track your vendor verification progress on Smart African Market.')
@section('seoKeywords', 'vendor application status, vendor verification, African Market')

@section('content')
<x-hero-section
    pageIdentifier="vendor-status"
    fallbackTitle="Application Status"
    fallbackSubtitle="Track your vendor verification progress"
    fallbackImage="/images/hero-banners/referral-banner.avif"
    headingTag="h2"
    
/>

<div class="container mx-auto px-4 py-10">
    <div class="max-w-4xl mx-auto space-y-6">
        <div class="bg-gray-800 border border-gray-700 rounded-2xl p-6">
            <h2 class="text-2xl font-bold text-white">Application Overview</h2>
            <p class="text-gray-400 mt-1">Status: <span class="text-white">{{ ucfirst($vendor->status) }}</span></p>
            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-300">
                <div>
                    <p class="text-gray-400">Business Name</p>
                    <p class="text-white">{{ $vendor->business_name }}</p>
                </div>
                <div>
                    <p class="text-gray-400">Business Email</p>
                    <p class="text-white">{{ $vendor->business_email }}</p>
                </div>
                <div>
                    <p class="text-gray-400">Business Phone</p>
                    <p class="text-white">{{ $vendor->business_phone }}</p>
                </div>
                <div>
                    <p class="text-gray-400">Business Address</p>
                    <p class="text-white">{{ $vendor->business_address }}</p>
                </div>
            </div>
        </div>

        <div class="bg-gray-800 border border-gray-700 rounded-2xl p-6">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <h3 class="text-xl font-semibold text-white">Verification Documents</h3>
                @if($verification->verified_at)
                    <span class="text-sm text-gray-400">Verified: {{ $verification->verified_at->format('M d, Y') }}</span>
                @endif
            </div>

            @if(!empty($verification->documents))
                <ul class="mt-4 space-y-2 text-sm text-gray-300">
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
            @else
                <p class="text-gray-400 mt-3">No documents submitted yet.</p>
            @endif

            <form method="POST" action="{{ route('vendor.application.documents') }}" enctype="multipart/form-data" class="mt-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm text-gray-300 mb-2">Upload Documents (PDF, JPG, PNG, WebP)</label>
                    <input type="file" name="documents[]" multiple class="w-full text-gray-300">
                    <p class="text-xs text-gray-400 mt-1">Upload business registration, ID, and proof of address.</p>
                </div>
                <div>
                    <label class="block text-sm text-gray-300 mb-2" for="notes">Notes</label>
                    <textarea name="notes" id="notes" rows="3" class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white">{{ old('notes', $verification->notes) }}</textarea>
                </div>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg">
                    Submit Documents
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

