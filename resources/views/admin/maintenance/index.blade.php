@extends('admin.layout')

@section('title', 'Maintenance Mode')
@section('header', 'Maintenance Mode Settings')

@section('content')
<div class="max-w-4xl">
    <!-- Current Status Card -->
    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6 mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-full flex items-center justify-center {{ $isActive ? 'bg-red-900/50' : 'bg-green-900/50' }}">
                    @if($isActive)
                        <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    @else
                        <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    @endif
                </div>
                <div>
                    <h2 class="text-xl font-bold text-white">
                        {{ $isActive ? 'Maintenance Mode Active' : 'Site is Live' }}
                    </h2>
                    <p class="text-gray-400">
                        @if($isActive)
                            The site is currently showing a maintenance page to visitors.
                        @else
                            The site is accessible to all visitors.
                        @endif
                    </p>
                </div>
            </div>
            
            <!-- Quick Toggle -->
            <form method="POST" action="{{ route('admin.maintenance.toggle') }}">
                @csrf
                <button type="submit" 
                        class="px-6 py-3 rounded-lg font-medium transition-colors {{ $isActive ? 'bg-green-600 hover:bg-green-700 text-white' : 'bg-red-600 hover:bg-red-700 text-white' }}">
                    {{ $isActive ? 'Disable Now' : 'Enable Now' }}
                </button>
            </form>
        </div>
    </div>

    <!-- Configuration Form -->
    <form method="POST" action="{{ route('admin.maintenance.update') }}" class="space-y-6">
        @csrf
        
        <!-- Maintenance Message -->
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-white mb-4">Maintenance Message</h3>
            <p class="text-sm text-gray-400 mb-4">This message will be displayed to visitors when maintenance mode is active.</p>
            
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Title</label>
                <input type="text" name="title" value="{{ $config['title'] }}"
                       class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                       placeholder="We'll be back soon!">
            </div>
            
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-300 mb-2">Message</label>
                <textarea name="message" rows="4"
                          class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                          placeholder="We're currently performing scheduled maintenance. We'll be back shortly.">{{ $config['message'] }}</textarea>
            </div>
        </div>

        <!-- Retry Configuration -->
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-white mb-4">Retry Configuration</h3>
            <p class="text-sm text-gray-400 mb-4">The Retry-After header tells browsers when to retry the request.</p>
            
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">
                    Retry After (seconds)
                </label>
                <input type="number" name="retry" value="{{ $config['retry'] }}" min="1" max="3600"
                       class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                       placeholder="60">
                <p class="text-xs text-gray-500 mt-1">
                    Recommended: 60-300 seconds. Maximum: 3600 (1 hour).
                </p>
            </div>
        </div>

        <!-- Access Control -->
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-white mb-4">Access Control</h3>
            <p class="text-sm text-gray-400 mb-4">Allow specific IP addresses or emails to bypass maintenance mode.</p>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-300 mb-2">
                    Allowed IP Addresses
                    <span class="text-gray-500">(one per line)</span>
                </label>
                <textarea name="allowed_ips" rows="3"
                          class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 font-mono text-sm"
                          placeholder="127.0.0.1
192.168.1.1">{{ is_array($config['allowed_ips']) ? implode("\n", $config['allowed_ips']) : '' }}</textarea>
                <p class="text-xs text-gray-500 mt-1">
                    Enter IP addresses (IPv4 or IPv6) that can access the site during maintenance.
                </p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">
                    Allowed Emails
                    <span class="text-gray-500">(one per line)</span>
                </label>
                <textarea name="allowed_emails" rows="3"
                          class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 font-mono text-sm"
                          placeholder="admin@example.com
support@example.com">{{ is_array($config['allowed_emails']) ? implode("\n", $config['allowed_emails']) : '' }}</textarea>
                <p class="text-xs text-gray-500 mt-1">
                    Users with these emails can bypass maintenance mode.
                </p>
            </div>
        </div>

        <!-- Admin Bypass Info -->
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-white mb-4">Administrator Access</h3>
            <div class="flex items-start gap-3">
                <svg class="w-6 h-6 text-blue-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="text-gray-300">
                    <p class="mb-2"><strong class="text-white">Administrators always have access</strong> during maintenance mode.</p>
                    <p class="text-sm text-gray-400">Admin users, vendor users, and authenticated users with appropriate permissions can always access the site regardless of maintenance mode status.</p>
                </div>
            </div>
        </div>

        <!-- Artisan Commands Info -->
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-white mb-4">Command Line Usage</h3>
            <p class="text-sm text-gray-400 mb-4">You can also manage maintenance mode from the command line:</p>
            
            <div class="bg-gray-900 rounded-lg p-4 space-y-2 font-mono text-sm">
                <div class="flex items-center gap-2">
                    <span class="text-green-400">#</span>
                    <span class="text-gray-400">Enable maintenance mode:</span>
                </div>
                <div class="pl-4 text-blue-400">php artisan maintenance:enable</div>
                
                <div class="flex items-center gap-2 mt-3">
                    <span class="text-green-400">#</span>
                    <span class="text-gray-400">Disable maintenance mode:</span>
                </div>
                <div class="pl-4 text-blue-400">php artisan maintenance:disable</div>
                
                <div class="flex items-center gap-2 mt-3">
                    <span class="text-green-400">#</span>
                    <span class="text-gray-400">Check maintenance status:</span>
                </div>
                <div class="pl-4 text-blue-400">php artisan maintenance:status</div>
            </div>
        </div>

        <div class="flex justify-end gap-4">
            <button type="submit" class="bg-brand-from hover:bg-brand-from hover:opacity-90 text-white px-6 py-3 rounded-lg font-medium">
                Save Configuration
            </button>
        </div>
    </form>
</div>
@endsection
