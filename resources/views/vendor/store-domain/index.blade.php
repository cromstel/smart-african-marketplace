@extends('vendor.layout')

@section('title', 'Store Domain')
@section('header', 'Store Domain')

@section('content')
<div class="max-w-3xl space-y-6">
    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-white">Store URL Options</h3>
        <p class="text-sm text-gray-400 mt-1">Your store is always available on your platform subdomain. Enterprise vendors can also map a custom domain.</p>
    </div>

    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6 space-y-4">
        <div>
            <p class="text-sm text-gray-400">Default subdomain URL</p>
            <a href="{{ $subdomain_url }}" target="_blank" class="text-indigo-400 hover:text-indigo-300 break-all">{{ $subdomain_url }}</a>
        </div>

        <div class="border-t border-gray-700 pt-4">
            <p class="text-sm text-gray-400">Custom domain</p>
            <form action="{{ route('vendor.store-domain.update') }}" method="POST" class="mt-3 space-y-3">
                @csrf
                @method('PUT')
                <input
                    type="text"
                    name="custom_domain"
                    value="{{ old('custom_domain', $vendor->custom_domain) }}"
                    placeholder="store.yourcompany.com"
                    class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                >
                @error('custom_domain')
                    <p class="text-red-400 text-xs">{{ $message }}</p>
                @enderror
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg">Save Domain</button>
            </form>
        </div>

        @if($vendor->custom_domain)
            <div class="border-t border-gray-700 pt-4 space-y-3">
                <p class="text-sm text-gray-400">DNS target</p>
                <code class="text-xs text-gray-300">{{ $domain_target }}</code>
                <div>
                    @if($vendor->custom_domain_verified_at)
                        <p class="text-green-400 text-sm">Verified on {{ $vendor->custom_domain_verified_at->format('M d, Y H:i') }}</p>
                        <a href="{{ $vendor->storefrontUrl() }}" target="_blank" class="text-indigo-400 hover:text-indigo-300 break-all">{{ $vendor->storefrontUrl() }}</a>
                    @else
                        <p class="text-yellow-400 text-sm">Not verified yet</p>
                        <form action="{{ route('vendor.store-domain.verify') }}" method="POST" class="mt-2">
                            @csrf
                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">Verify DNS</button>
                        </form>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
@endsection


