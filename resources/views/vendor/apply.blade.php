@extends('layouts.shop')

@section('title', 'Vendor Application')

@section('content')
<x-hero-section
    pageIdentifier="vendor-apply"
    fallbackTitle="Become a Vendor"
    fallbackSubtitle="Apply to sell on African Market"
    fallbackImage="/images/vendor-apply-banner.avif"
    headingTag="h2"
    :animated="true"
/>

<div class="container mx-auto px-4 py-10">
    <div class="max-w-3xl mx-auto bg-gray-800 border border-gray-700 rounded-2xl p-8">
        <h2 class="text-2xl font-bold text-white mb-2">Vendor Application</h2>
        <p class="text-gray-400 mb-6">Complete the form below to start your vendor application.</p>

        <form method="POST" action="{{ route('vendor.apply.store') }}" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm text-gray-300 mb-2">Business Name *</label>
                <input type="text" name="business_name" value="{{ old('business_name') }}" required
                       class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white">
                @error('business_name') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-300 mb-2">Business Email *</label>
                    <input type="email" name="business_email" value="{{ old('business_email') }}" required
                           class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white">
                    @error('business_email') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm text-gray-300 mb-2">Business Phone *</label>
                    <input type="text" name="business_phone" value="{{ old('business_phone') }}" required
                           class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white">
                    @error('business_phone') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm text-gray-300 mb-2">Business Address *</label>
                <input type="text" name="business_address" value="{{ old('business_address') }}" required
                       class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white">
                @error('business_address') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-300 mb-2">Business Registration #</label>
                    <input type="text" name="business_registration_number" value="{{ old('business_registration_number') }}"
                           class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white">
                </div>
                <div>
                    <label class="block text-sm text-gray-300 mb-2">Tax ID</label>
                    <input type="text" name="tax_id" value="{{ old('tax_id') }}"
                           class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white">
                </div>
            </div>

            <div>
                <label class="block text-sm text-gray-300 mb-2">Business Description</label>
                <textarea name="description" rows="4"
                          class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white">{{ old('description') }}</textarea>
            </div>

            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-semibold">
                Submit Application
            </button>
        </form>
    </div>
</div>
@endsection

