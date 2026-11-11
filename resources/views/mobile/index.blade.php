@extends('layouts.shop')

@section('content')
@php
    try {
        $rawContainerWidth = (int) \App\Models\Setting::getValue('theme_container_width', 1600);
    } catch (\Throwable $e) {
        $rawContainerWidth = 1600;
    }
    $containerWidth = max(1024, min(1600, $rawContainerWidth));
@endphp

<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100" style="max-width: <?php echo e($containerWidth); ?>px; margin: 0 auto;">
    <div class="py-16 px-4">
        <div class="text-center mb-12">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl mb-6 bg-gradient-to-br from-orange-500 to-red-600 shadow-lg">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
            </div>
            <h1 class="text-4xl font-bold text-gray-900 mb-4">{{ __('ui.mobile.app_name') }}</h1>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">{{ __('ui.mobile.subtitle') }}</p>
        </div>

        @php
            $androidFirst = in_array('android', $preferredOrder ?? ['android', 'ios']);
        @endphp

        <div class="grid md:grid-cols-2 gap-8 max-w-4xl mx-auto">
            @if($androidFirst)
            <!-- Android Card -->
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300 border border-gray-100">
                <div class="bg-gradient-to-r from-green-500 to-green-600 p-6">
                    <div class="flex items-center space-x-4">
                        <div class="w-14 h-14 bg-white rounded-xl flex items-center justify-center">
                            <svg class="w-8 h-8 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17.523 2.047a.5.5 0 00-.612.516l-1.026 4.636L9.622 5.404a.5.5 0 00-.484-.32L3.6 6.64A.5.5 0 003 7.118v9.405c0 .299.17.572.423.69l6.555 3.042a.5.5 0 00.484.32l5.543-1.205a.5.5 0 00.423-.69l.623-4.597.378-2.171a.5.5 0 00-.1-.347l-2.322-4.31zM15.5 9.5l-2.45 4.1-3.14-1.47L15.5 9.5z"/>
                            </svg>
                        </div>
                        <div class="text-white">
                            <h2 class="text-xl font-bold">Android</h2>
                            <p class="text-green-100 text-sm">v{{ $androidVersion }} ({{ $androidBuild }})</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <ul class="space-y-3 mb-6">
                        <li class="flex items-start text-gray-600">
                            <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>{{ __('ui.mobile.full_access') }}</span>
                        </li>
                        <li class="flex items-start text-gray-600">
                            <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>{{ __('ui.mobile.offline_support') }}</span>
                        </li>
                        <li class="flex items-start text-gray-600">
                            <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>{{ __('ui.mobile.push_notifications') }}</span>
                        </li>
                    </ul>
                    <a href="{{ $androidUrl }}" class="block w-full bg-gradient-to-r from-orange-600 to-red-600 hover:from-orange-700 hover:to-red-700 text-white text-center py-3 rounded-lg font-semibold transition-all shadow-md hover:shadow-lg">
                        {{ __('ui.mobile.download_android') }}
                    </a>
                    @if($androidMinOs)
                        <p class="text-xs text-gray-500 text-center mt-3">{{ __('ui.mobile.min_android') }} {{ $androidMinOs }}+</p>
                    @endif
                </div>
            </div>

            <!-- iOS Card -->
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300 border border-gray-100">
                <div class="bg-gradient-to-r from-gray-800 to-gray-900 p-6">
                    <div class="flex items-center space-x-4">
                        <div class="w-14 h-14 bg-white rounded-xl flex items-center justify-center">
                            <svg class="w-8 h-8 text-gray-800" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/>
                            </svg>
                        </div>
                        <div class="text-white">
                            <h2 class="text-xl font-bold">iOS</h2>
                            <p class="text-gray-300 text-sm">v{{ $iosVersion }} ({{ $iosBuild }})</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <ul class="space-y-3 mb-6">
                        <li class="flex items-start text-gray-600">
                            <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>{{ __('ui.mobile.full_access') }}</span>
                        </li>
                        <li class="flex items-start text-gray-600">
                            <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>{{ __('ui.mobile.secure') }}</span>
                        </li>
                        <li class="flex items-start text-gray-600">
                            <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>{{ __('ui.mobile.push_notifications') }}</span>
                        </li>
                    </ul>
                    <a href="{{ $iosAppStoreUrl }}" class="block w-full bg-gradient-to-r from-gray-700 to-gray-900 hover:from-gray-800 hover:to-black text-white text-center py-3 rounded-lg font-semibold transition-all shadow-md hover:shadow-lg">
                        {{ __('ui.mobile.download_ios') }}
                    </a>
                    @if($iosMinOs)
                        <p class="text-xs text-gray-500 text-center mt-3">{{ __('ui.mobile.min_ios') }} {{ $iosMinOs }}+</p>
                    @endif
                </div>
            </div>
            @else
            <!-- iOS Card First -->
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300 border border-gray-100">
                <div class="bg-gradient-to-r from-gray-800 to-gray-900 p-6">
                    <div class="flex items-center space-x-4">
                        <div class="w-14 h-14 bg-white rounded-xl flex items-center justify-center">
                            <svg class="w-8 h-8 text-gray-800" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/>
                            </svg>
                        </div>
                        <div class="text-white">
                            <h2 class="text-xl font-bold">iOS</h2>
                            <p class="text-gray-300 text-sm">v{{ $iosVersion }} ({{ $iosBuild }})</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <ul class="space-y-3 mb-6">
                        <li class="flex items-start text-gray-600">
                            <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>{{ __('ui.mobile.full_access') }}</span>
                        </li>
                        <li class="flex items-start text-gray-600">
                            <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>{{ __('ui.mobile.secure') }}</span>
                        </li>
                        <li class="flex items-start text-gray-600">
                            <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>{{ __('ui.mobile.push_notifications') }}</span>
                        </li>
                    </ul>
                    <a href="{{ $iosAppStoreUrl }}" class="block w-full bg-gradient-to-r from-gray-700 to-gray-900 hover:from-gray-800 hover:to-black text-white text-center py-3 rounded-lg font-semibold transition-all shadow-md hover:shadow-lg">
                        {{ __('ui.mobile.download_ios') }}
                    </a>
                    @if($iosMinOs)
                        <p class="text-xs text-gray-500 text-center mt-3">{{ __('ui.mobile.min_ios') }} {{ $iosMinOs }}+</p>
                    @endif
                </div>
            </div>

            <!-- Android Card -->
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300 border border-gray-100">
                <div class="bg-gradient-to-r from-green-500 to-green-600 p-6">
                    <div class="flex items-center space-x-4">
                        <div class="w-14 h-14 bg-white rounded-xl flex items-center justify-center">
                            <svg class="w-8 h-8 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17.523 2.047a.5.5 0 00-.612.516l-1.026 4.636L9.622 5.404a.5.5 0 00-.484-.32L3.6 6.64A.5.5 0 003 7.118v9.405c0 .299.17.572.423.69l6.555 3.042a.5.5 0 00.484.32l5.543-1.205a.5.5 0 00.423-.69l.623-4.597.378-2.171a.5.5 0 00-.1-.347l-2.322-4.31zM15.5 9.5l-2.45 4.1-3.14-1.47L15.5 9.5z"/>
                            </svg>
                        </div>
                        <div class="text-white">
                            <h2 class="text-xl font-bold">Android</h2>
                            <p class="text-green-100 text-sm">v{{ $androidVersion }} ({{ $androidBuild }})</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <ul class="space-y-3 mb-6">
                        <li class="flex items-start text-gray-600">
                            <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>{{ __('ui.mobile.full_access') }}</span>
                        </li>
                        <li class="flex items-start text-gray-600">
                            <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>{{ __('ui.mobile.offline_support') }}</span>
                        </li>
                        <li class="flex items-start text-gray-600">
                            <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>{{ __('ui.mobile.push_notifications') }}</span>
                        </li>
                    </ul>
                    <a href="{{ $androidUrl }}" class="block w-full bg-gradient-to-r from-orange-600 to-red-600 hover:from-orange-700 hover:to-red-700 text-white text-center py-3 rounded-lg font-semibold transition-all shadow-md hover:shadow-lg">
                        {{ __('ui.mobile.download_android') }}
                    </a>
                    @if($androidMinOs)
                        <p class="text-xs text-gray-500 text-center mt-3">{{ __('ui.mobile.min_android') }} {{ $androidMinOs }}+</p>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Links Section -->
        <div class="mt-12 text-center space-y-4">
            <div class="flex flex-wrap justify-center gap-6">
                <a href="{{ $changelogUrl }}" class="text-orange-600 hover:text-orange-700 font-medium">
                    {{ __('ui.mobile.changelog') }}
                </a>
                <span class="text-gray-300">|</span>
                <a href="{{ $installInstructionsUrl }}" class="text-orange-600 hover:text-orange-700 font-medium">
                    {{ __('ui.mobile.install_guide') }}
                </a>
                <span class="text-gray-300">|</span>
                <a href="{{ $contactSupportUrl }}" class="text-orange-600 hover:text-orange-700 font-medium">
                    {{ __('ui.mobile.contact_support') }}
                </a>
            </div>
            <p class="text-sm text-gray-500">{{ __('ui.mobile.scan_qr') }}</p>
        </div>
    </div>
</div>
@endsection
