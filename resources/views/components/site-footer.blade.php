@php
    $containerWidth = max(1024, min(1600, (int) \App\Models\Setting::getValue('theme_container_width', 1600)));
@endphp

<!-- Main Footer Content -->
<footer class="relative bg-gray-900 text-white mt-auto">
    <div class="fixed left-3 sm:left-4 bottom-24 sm:bottom-20 z-[9999] pointer-events-auto" style="position: fixed; left: 1rem; bottom: 6rem; z-index: 9999;">
        @include('components.language-switcher', ['placement' => 'footer'])
    </div>

    <div class="mx-auto px-4 py-12" style="max-width: {{ $containerWidth }}px;">
        <div class="grid md:grid-cols-5 gap-8">
            <div class="md:col-span-2">
                <h4 class="text-xl font-bold mb-4 flex items-center space-x-2">
                    <a href="{{ route('home') }}" aria-label="{{ config('app.name', 'Smart African Marketplace') }} - Go to homepage">
                        <img src="{{ asset(config('branding.logo')) }}" alt="{{ __('ui.brand.name') }}" class="h-8 w-8">
                    </a>
                    <span class="uppercase tracking-wide">{{ __('ui.brand.name') }}</span>
                </h4>
                <p class="text-gray-400 mb-4">
                    {{ __('ui.brand.tagline') }}
                </p>
                <!-- Mobile App QR Code -->
                <div class="flex items-center space-x-4 p-4 bg-gray-800 rounded-lg">
                    <div class="flex-shrink-0">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data={{ urlencode(route('mobile.index')) }}&bgcolor=ffffff&color=000000" 
                             alt="{{ __('ui.footer.qr_alt') }}" 
                             class="w-24 h-24 rounded-lg">
                    </div>
                    <div>
                        <h6 class="font-semibold text-white mb-1">{{ __('ui.footer.download_app') }}</h6>
                        <p class="text-xs text-gray-400 mb-2">{{ __('ui.footer.scan_qr') }}</p>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('mobile.index') }}" class="text-xs bg-orange-600 hover:bg-orange-700 text-white px-3 py-1.5 rounded transition">
                                {{ __('ui.footer.download_android') }}
                            </a>
                            <a href="{{ route('mobile.index') }}" class="text-xs bg-gray-700 hover:bg-gray-600 text-white px-3 py-1.5 rounded transition">
                                {{ __('ui.footer.download_ios') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div>
                <h5 class="font-semibold mb-4">{{ __('ui.footer.quick_links') }}</h5>
                <ul class="space-y-2 text-gray-400">
                    <li><a href="{{ route('shop.index') }}" class="hover:text-white">{{ __('ui.footer.shop') }}</a></li>
                    <li><a href="{{ route('salon.index') }}" class="hover:text-white">{{ __('ui.footer.salon_services') }}</a></li>
                    <li><a href="{{ route('blog.index') }}" class="hover:text-white">{{ __('ui.footer.blog') }}</a></li>
                    <li><a href="{{ route('about') }}" class="hover:text-white">{{ __('ui.footer.about_us') }}</a></li>
                </ul>
            </div>
            <div>
                <h5 class="font-semibold mb-4">{{ __('ui.footer.customer_service') }}</h5>
                <ul class="space-y-2 text-gray-400">
                    <li><a href="{{ route('contact.index') }}" class="hover:text-white">{{ __('ui.footer.contact_us') }}</a></li>
                    <li><a href="{{ route('faq') }}" class="hover:text-white">{{ __('ui.footer.faqs') }}</a></li>
                    <li><a href="{{ route('terms') }}" class="hover:text-white">{{ __('ui.footer.terms') }}</a></li>
                    <li><a href="{{ route('privacy') }}" class="hover:text-white">{{ __('ui.footer.privacy') }}</a></li>
                </ul>
            </div>
            <div>
                <h5 class="font-semibold mb-4">{{ __('ui.footer.newsletter') }}</h5>
                <form action="{{ route('newsletter.subscribe') }}" method="POST" class="space-y-2">
                    @csrf
                    <label for="newsletter-email" class="sr-only">{{ __('ui.footer.email_placeholder') }}</label>
                    <input type="email" id="newsletter-email" name="email" autocomplete="email" placeholder="{{ __('ui.footer.email_placeholder') }}" required
                        class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                    <button type="submit" class="w-full bg-gradient-to-r from-orange-600 to-red-600 hover:from-orange-700 hover:to-red-700 text-white px-4 py-2 rounded-lg font-semibold transition">
                        {{ __('ui.footer.subscribe') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</footer>

<!-- Sticky Bottom Bar -->
<div class="bg-gray-900 text-white border-t border-gray-800 sticky bottom-0 z-40">
    <div class="mx-auto px-4 py-3 flex flex-col gap-1 text-left sm:flex-row sm:items-center sm:justify-between" style="max-width: {{ $containerWidth }}px;">
        <p class="text-gray-400 text-xs sm:text-sm md:text-base">&copy; 2025-{{ date('Y') }} {{ __('ui.brand.name') }}. {{ __('ui.footer.rights') }}</p>
        <div class="flex items-center justify-between sm:justify-end gap-4">
            <p class="text-gray-400 text-xs sm:text-sm md:text-base sm:text-right">
                {{ __('ui.footer.powered_by') }}
                <a href="https://cromstelit.com" target="_blank" rel="noopener" class="underline hover:text-white">CromStel IT Solutions</a>
            </p>
        </div>
    </div>
</div>

