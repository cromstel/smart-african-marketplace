<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @php
        try {
            $themePreset = \App\Models\Setting::getValue('theme_preset', 'modern-commerce');
            $brandColorFrom = \App\Models\Setting::getValue('brand_color_from', '#ea580c');
            $brandColorTo = \App\Models\Setting::getValue('brand_color_to', '#dc2626');
            $accentColor = \App\Models\Setting::getValue('theme_accent_color', '#f97316');
            $linkColor = \App\Models\Setting::getValue('theme_link_color', '#ea580c');
            $mutedText = \App\Models\Setting::getValue('theme_muted_text_color', '#6b7280');
            $containerWidth = (int) \App\Models\Setting::getValue('theme_container_width', 1600);
            $fontBody = \App\Models\Setting::getValue('theme_font_body', 'Inter');
            $fontHeading = \App\Models\Setting::getValue('theme_font_heading', 'Poppins');
            $buttonRadius = (int) \App\Models\Setting::getValue('theme_button_radius', 12);
            $surfaceStyle = \App\Models\Setting::getValue('theme_surface_style', 'soft');
            $stickyHeader = \App\Models\Setting::getValue('theme_sticky_header', true);
            $headerPreset = \App\Models\Setting::getValue('theme_header_preset', 'centered');
        } catch (\Throwable $e) {
            $themePreset = 'modern-commerce';
            $brandColorFrom = '#ea580c';
            $brandColorTo = '#dc2626';
            $accentColor = '#f97316';
            $linkColor = '#ea580c';
            $mutedText = '#6b7280';
            $containerWidth = 1600;
            $fontBody = 'Inter';
            $fontHeading = 'Poppins';
            $buttonRadius = 12;
            $surfaceStyle = 'soft';
            $stickyHeader = true;
            $headerPreset = 'centered';
        }
    @endphp
    <x-seo
        :title="$seoTitle ?? null"
        :description="$seoDescription ?? null"
        :keywords="$seoKeywords ?? null"
        :image="$seoImage ?? null"
        :canonical="$seoCanonical ?? null"
        :type="$seoType ?? 'website'"
    />

    {{--
        Controllers build Product/Article/Breadcrumb/etc. schema via
        SeoService::addStructuredData() (ShopController, BlogController,
        SalonController, AuctionController, PageController), but nothing
        ever rendered getStructuredData() -- every rich-result block was
        silently discarded. Rendered once here so every page on this layout
        picks it up, rather than duplicating the loop in each view.
    --}}
    @foreach(app(\App\Services\SeoService::class)->getStructuredData() as $schema)
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
    @endforeach

        <!-- Theme fonts (Inter / Poppins per theme settings) -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&family=poppins:400,500,600,700&display=swap" rel="stylesheet" />

    @livewireStyles
    @livewireScripts
    <link rel="preload" as="style" href="{{ \Illuminate\Support\Facades\Vite::asset('resources/css/app.css') }}">
    <link rel="prefetch" href="{{ route('home') }}">
    <link rel="prefetch" href="{{ route('shop.index') }}">
    <link rel="prefetch" href="{{ route('blog.index') }}">
    <link rel="prefetch" href="{{ route('subscriptions.index') }}">
    <link rel="prefetch" href="{{ route('contact.index') }}">
    @php
        $fontFamilies = collect([$fontBody, $fontHeading])
            ->filter()
            ->unique()
            ->map(fn ($font) => str_replace(' ', '+', trim($font)).':400,500,600,700')
            ->implode('|');
    @endphp
    @if($fontFamilies !== '')
        <link rel="preload" as="style" href="https://fonts.bunny.net/css?family={{ $fontFamilies }}&display=swap">
        <link rel="stylesheet" href="https://fonts.bunny.net/css?family={{ $fontFamilies }}&display=swap" media="print" onload="this.media='all'">
        <noscript>
            <link rel="stylesheet" href="https://fonts.bunny.net/css?family={{ $fontFamilies }}&display=swap">
        </noscript>
    @endif
    <style>
        :root {
            --theme-brand-from: {{ $brandColorFrom }};
            --theme-brand-to: {{ $brandColorTo }};
            --theme-accent: {{ $accentColor }};
            --theme-link: {{ $linkColor }};
            --theme-muted: {{ $mutedText }};
            --theme-container-width: {{ max(1024, min(1600, $containerWidth)) }}px;
            --theme-btn-radius: {{ max(0, min(28, $buttonRadius)) }}px;
            --theme-font-body: "{{ $fontBody }}", sans-serif;
            --theme-font-heading: "{{ $fontHeading }}", sans-serif;
        }
        body {
            font-family: var(--theme-font-body);
            width: 100%;
            overflow-x: clip;
        }
        html, body {
            max-width: 100%;
            overflow-x: hidden;
        }
        h1, h2, h3, h4, h5, h6 {
            font-family: var(--theme-font-heading);
        }
        *, *::before, *::after {
            box-sizing: border-box;
            min-width: 0;
        }
        img, video, canvas, svg {
            max-width: 100%;
            height: auto;
        }
        input, textarea, select, button {
            max-width: 100%;
        }
        .site-container {
            max-width: var(--theme-container-width);
            margin-left: auto;
            margin-right: auto;
            width: 100%;
        }
        .container {
            max-width: var(--theme-container-width) !important;
        }
        .theme-accent-text {
            color: var(--theme-link);
        }
        .theme-link:hover {
            color: var(--theme-link);
        }
        .theme-link:focus-visible {
            outline: 2px solid var(--theme-accent);
            outline-offset: 2px;
            border-radius: 4px;
        }
        .theme-cta {
            border-radius: var(--theme-btn-radius);
            background: linear-gradient(90deg, var(--theme-brand-from), var(--theme-brand-to));
        }
        .theme-cta:focus-visible {
            outline: 2px solid var(--theme-accent);
            outline-offset: 2px;
        }
        .theme-cta:active {
            transform: translateY(0);
            filter: brightness(0.95);
        }
        .theme-nav {
            @if($surfaceStyle === 'glass')
                backdrop-filter: saturate(180%) blur(12px);
                background-color: rgba(255,255,255,0.86);
            @elseif($surfaceStyle === 'flat')
                background-color: #ffffff;
            @else
                background-color: #ffffff;
                box-shadow: 0 1px 2px rgba(15, 23, 42, 0.05);
            @endif
        }

        @media (prefers-reduced-motion: no-preference) {
            .page-enter {
                animation: pageEnter 380ms ease-out both;
            }
        }

        @keyframes pageEnter {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Auth pages full height styles - max 100vh with scroll in content area */
        .auth-page {
            max-height: 100vh;
            overflow: hidden;
        }
        .auth-page body {
            max-height: 100vh;
            overflow: hidden;
        }
        .auth-page #main-nav {
            max-height: 100vh;
            overflow-y: auto;
        }
        .auth-page main {
            max-height: calc(100vh - 80px);
            overflow-y: auto;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .auth-full-height {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .auth-full-height main {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
    </style>
</head>
<body class="font-sans antialiased bg-gray-50 flex flex-col min-h-screen{{ $hideFooter ?? false ? ' auth-page' : '' }}" x-data="{ mobileMenuOpen: false }">
    <nav id="main-nav" 
        class="theme-nav sticky top-0 z-50 transition-all duration-300 shadow-sm"
    >
        <div class="site-container px-4">
            <div class="flex justify-between items-center h-16 md:h-20">
                <div class="flex items-center space-x-8 lg:space-x-12">
                    <a href="{{ route('home') }}" class="flex items-center space-x-2 min-w-0">
                        <img src="{{ asset(config('branding.logo')) }}" alt="{{ config('app.name') }}" class="h-10 w-10">
                        <span class="text-sm sm:text-base surface:text-lg lg:text-xl font-extrabold uppercase tracking-wide bg-clip-text text-transparent truncate max-w-[10.5rem] sm:max-w-[13rem] xl:max-w-none" style="background-image: linear-gradient(90deg, var(--theme-brand-from), var(--theme-brand-to));">{{ config('app.name') }}</span>
                    </a>
                    <div class="hidden md:flex md:space-x-3 lg:space-x-4 xl:space-x-8 min-w-0">
                        <a href="{{ route('shop.index') }}" class="theme-link text-xs sm:text-sm md:text-base text-gray-700 font-medium transition-colors {{ request()->routeIs('shop.*') ? 'theme-accent-text' : '' }}">
                            {{ __('ui.nav.shop') }}
                        </a>
                        <a href="{{ route('subscriptions.index') }}" class="theme-link text-xs sm:text-sm md:text-base text-gray-700 font-medium transition-colors {{ request()->routeIs('subscriptions.*') ? 'theme-accent-text' : '' }}">
                            {{ __('ui.nav.pricing') }}
                        </a>
                        <a href="{{ route('salon.index') }}" class="theme-link text-xs sm:text-sm md:text-base text-gray-700 font-medium transition-colors {{ request()->routeIs('salon.*') ? 'theme-accent-text' : '' }}">
                            {{ __('ui.nav.salons') }}
                        </a>
                        <a href="{{ route('blog.index') }}" class="theme-link text-xs sm:text-sm md:text-base text-gray-700 font-medium transition-colors {{ request()->routeIs('blog.*') ? 'theme-accent-text' : '' }}">
                            {{ __('ui.nav.blog') }}
                        </a>
                        <a href="{{ route('about') }}" class="theme-link text-xs sm:text-sm md:text-base text-gray-700 font-medium transition-colors">
                            {{ __('ui.nav.about') }}
                        </a>
                        <a href="{{ route('contact.index') }}" class="theme-link text-xs sm:text-sm md:text-base text-gray-700 font-medium transition-colors">
                            {{ __('ui.nav.contact') }}
                        </a>
                    </div>
                </div>

                <div class="flex items-center space-x-4 md:space-x-6 min-w-0">
                    <div class="hidden md:block min-w-0 shrink md:ps-4">
                        @livewire('live-search')
                    </div>

                    @auth
                        @php $notificationCount = auth()->user()?->unreadUserNotifications()?->count() ?? 0; @endphp
                        <a href="{{ route('wishlist.index') }}" class="hidden sm:inline-block relative text-gray-700 hover:text-orange-600" title="{{ __('ui.common.wishlist') }}">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                            @if(\App\Models\Wishlist::where('user_id', auth()->id())->count() > 0)
                                <span class="absolute -top-2 -right-2 bg-red-600 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                                    {{ \App\Models\Wishlist::where('user_id', auth()->id())->count() }}
                                </span>
                            @endif
                        </a>
                        <a href="{{ route('cart.index') }}" class="hidden sm:inline-block relative text-gray-700 hover:text-orange-600" title="{{ __('ui.common.shopping_cart') }}">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            @if(auth()->user()->carts()->first()?->items()->count() > 0)
                                <span class="absolute -top-2 -right-2 bg-orange-600 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                                    {{ auth()->user()->carts()->first()->items()->count() }}
                                </span>
                            @endif
                        </a>
                        
                        <div class="relative hidden md:block" x-data="{ accountMenuOpen: false }">
                            <button
                                type="button"
                                @click="accountMenuOpen = !accountMenuOpen"
                                @keydown.escape.window="accountMenuOpen = false"
                                :aria-expanded="accountMenuOpen.toString()"
                                aria-haspopup="true"
                                class="flex items-center space-x-2 text-xs sm:text-sm md:text-base text-gray-700 hover:text-orange-600"
                            >
                                <span class="font-medium">{{ auth()->user()->name }}</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div
                                x-show="accountMenuOpen"
                                x-transition
                                @click.away="accountMenuOpen = false"
                                class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg py-2 z-50"
                                style="display: none;"
                            >
                                @if(auth()->user()->isAdmin())
                                    <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-50">{{ __('ui.account.admin_dashboard') }}</a>
                                @endif
                                @if(auth()->user()->isVendor())
                                    <a href="{{ route('vendor.dashboard') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-50">{{ __('ui.account.vendor_dashboard') }}</a>
                                @endif
                                <a href="{{ route('dashboard') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-50">{{ __('ui.account.dashboard') }}</a>
                                <a href="{{ route('orders.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-50">{{ __('ui.account.my_orders') }}</a>
                                <a href="{{ route('account.reviews') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-50">{{ __('ui.account.my_reviews') }}</a>
                                <a href="{{ route('account.notifications') }}" class="flex items-center justify-between px-4 py-2 text-gray-700 hover:bg-gray-50">
                                    <span>{{ __('ui.account.my_notifications') }}</span>
                                    @if($notificationCount > 0)
                                        <span class="inline-flex items-center justify-center rounded-full bg-red-500 px-2 py-0.5 text-xs font-semibold text-white">
                                            {{ $notificationCount }}
                                        </span>
                                    @endif
                                </a>
                                <a href="{{ route('bookings.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-50">{{ __('ui.account.my_bookings') }}</a>
                                <a href="{{ route('loyalty.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-50">{{ __('ui.account.loyalty_points') }}</a>
                                <a href="{{ route('referrals.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-50">{{ __('ui.account.referrals') }}</a>
                                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-50">{{ __('ui.account.settings') }}</a>
                                <hr class="my-2">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-4 py-2 text-red-600 hover:bg-gray-50">{{ __('ui.account.logout') }}</button>
                                </form>
                            </div>
                        </div>
                    @else
                        <div class="hidden md:flex items-center">
                            <a href="{{ route('login') }}" class="theme-cta inline-flex items-center text-xs sm:text-sm md:text-base text-white px-6 py-2.5 font-semibold transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                </svg>
                                {{ __('ui.nav.login') }}
                            </a>
                        </div>
                    @endauth

                    <button type="button" @click="mobileMenuOpen = true" aria-label="Open mobile menu" :aria-expanded="mobileMenuOpen.toString()" class="md:hidden inline-flex items-center justify-center p-2 rounded-lg text-gray-700 hover:text-orange-600 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-orange-600 transition-all duration-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <div x-show="mobileMenuOpen" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="mobileMenuOpen = false"
         class="fixed inset-0 bg-black bg-opacity-50 z-50 md:hidden"
         style="display: none;">
    </div>

    <div x-show="mobileMenuOpen"
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="translate-x-full"
         @click.away="mobileMenuOpen = false"
         class="fixed top-0 right-0 bottom-0 w-full max-w-[88vw] sm:w-80 sm:max-w-[85vw] bg-white shadow-2xl z-50 md:hidden overflow-y-auto"
         style="display: none;">
        
        <div class="flex items-center justify-between p-4 border-b border-gray-200">
            <a href="{{ route('home') }}" class="flex items-center space-x-2 min-w-0">
                <img src="{{ asset(config('branding.logo')) }}" alt="{{ config('app.name') }}" class="h-8 w-8">
                <span class="text-sm sm:text-base surface:text-lg lg:text-xl font-extrabold uppercase tracking-wide bg-gradient-to-r from-orange-600 via-red-600 to-orange-600 bg-clip-text text-transparent truncate max-w-[10rem] sm:max-w-none">{{ config('app.name') }}</span>
            </a>
            <button type="button" @click="mobileMenuOpen = false" aria-label="Close mobile menu" class="p-2 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="p-4">
            <div class="mb-6">
                @livewire('live-search')
            </div>

            <nav class="space-y-1">
                <a href="{{ route('shop.index') }}" class="flex items-center px-4 py-3 text-xs sm:text-sm md:text-base text-gray-700 hover:bg-orange-50 hover:text-orange-600 rounded-lg font-medium transition-colors {{ request()->routeIs('shop.*') ? 'bg-orange-50 text-orange-600' : '' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    {{ __('ui.nav.shop') }}
                </a>
                <a href="{{ route('subscriptions.index') }}" class="flex items-center px-4 py-3 text-xs sm:text-sm md:text-base text-gray-700 hover:bg-orange-50 hover:text-orange-600 rounded-lg font-medium transition-colors {{ request()->routeIs('subscriptions.*') ? 'bg-orange-50 text-orange-600' : '' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V7m0 1v8m0 0v1"/>
                    </svg>
                    {{ __('ui.nav.pricing') }}
                </a>
                <a href="{{ route('salon.index') }}" class="flex items-center px-4 py-3 text-xs sm:text-sm md:text-base text-gray-700 hover:bg-orange-50 hover:text-orange-600 rounded-lg font-medium transition-colors {{ request()->routeIs('salon.*') ? 'bg-orange-50 text-orange-600' : '' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ __('ui.nav.salons') }}
                </a>
                <a href="{{ route('blog.index') }}" class="flex items-center px-4 py-3 text-xs sm:text-sm md:text-base text-gray-700 hover:bg-orange-50 hover:text-orange-600 rounded-lg font-medium transition-colors {{ request()->routeIs('blog.*') ? 'bg-orange-50 text-orange-600' : '' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                    </svg>
                    {{ __('ui.nav.blog') }}
                </a>
                <a href="{{ route('about') }}" class="flex items-center px-4 py-3 text-xs sm:text-sm md:text-base text-gray-700 hover:bg-orange-50 hover:text-orange-600 rounded-lg font-medium transition-colors">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ __('ui.nav.about') }}
                </a>
                <a href="{{ route('contact.index') }}" class="flex items-center px-4 py-3 text-xs sm:text-sm md:text-base text-gray-700 hover:bg-orange-50 hover:text-orange-600 rounded-lg font-medium transition-colors">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    {{ __('ui.nav.contact') }}
                </a>
            </nav>

            @auth
                @php $notificationCount = auth()->user()->unreadUserNotifications()->count(); @endphp
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <div class="flex items-center px-4 py-2 mb-4">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-orange-600 to-red-600 flex items-center justify-center text-white font-semibold mr-3">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">{{ auth()->user()->name }}</p>
                            <p class="text-sm text-gray-500">{{ auth()->user()->email }}</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <a href="{{ route('wishlist.index') }}" class="flex flex-col items-center px-4 py-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors">
                            <div class="relative">
                                <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                </svg>
                                @if(\App\Models\Wishlist::where('user_id', auth()->id())->count() > 0)
                                    <span class="absolute -top-2 -right-2 bg-red-600 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                                        {{ \App\Models\Wishlist::where('user_id', auth()->id())->count() }}
                                    </span>
                                @endif
                            </div>
                            <span class="text-xs text-gray-600 mt-1">{{ __('ui.common.wishlist') }}</span>
                        </a>
                        <a href="{{ route('cart.index') }}" class="flex flex-col items-center px-4 py-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors">
                            <div class="relative">
                                <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                @if(auth()->user()->carts()->first()?->items()->count() > 0)
                                    <span class="absolute -top-2 -right-2 bg-orange-600 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                                        {{ auth()->user()->carts()->first()->items()->count() }}
                                    </span>
                                @endif
                            </div>
                            <span class="text-xs text-gray-600 mt-1">{{ __('ui.common.shopping_cart') }}</span>
                        </a>
                    </div>

                    <nav class="space-y-1">
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0h6"/>
                                </svg>
                                {{ __('ui.account.admin_dashboard') }}
                            </a>
                        @endif
                        @if(auth()->user()->isVendor())
                            <a href="{{ route('vendor.dashboard') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18M3 17h18"/>
                                </svg>
                                {{ __('ui.account.vendor_dashboard') }}
                            </a>
                        @endif
                        <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                            {{ __('ui.account.dashboard') }}
                        </a>
                        <a href="{{ route('orders.index') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                            {{ __('ui.account.my_orders') }}
                        </a>
                        <a href="{{ route('account.reviews') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                            </svg>
                            {{ __('ui.account.my_reviews') }}
                        </a>
                        <a href="{{ route('account.notifications') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 00-5-5.917V4a1 1 0 10-2 0v1.083A6 6 0 006 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0a3 3 0 01-6 0"/>
                            </svg>
                            {{ __('ui.account.my_notifications') }}
                            @if($notificationCount > 0)
                                <span class="ml-auto rounded-full bg-red-500 px-2 py-0.5 text-xs font-semibold text-white">
                                    {{ $notificationCount }}
                                </span>
                            @endif
                        </a>
                        <a href="{{ route('bookings.index') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            {{ __('ui.account.my_bookings') }}
                        </a>
                        <a href="{{ route('loyalty.index') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ __('ui.account.loyalty_points') }}
                        </a>
                        <a href="{{ route('referrals.index') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            {{ __('ui.account.referrals') }}
                        </a>
                        <a href="{{ route('profile.edit') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            {{ __('ui.account.settings') }}
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex items-center w-full px-4 py-3 text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                {{ __('ui.account.logout') }}
                            </button>
                        </form>
                    </nav>
                </div>
            @else
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <a href="{{ route('login') }}" class="theme-cta flex items-center justify-center text-xs sm:text-sm md:text-base text-white px-6 py-2.5 font-semibold transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                        </svg>
                        {{ __('ui.nav.login') }}
                    </a>
                </div>
            @endauth
        </div>
    </div>

    @php
        $routeName = request()->route()?->getName();
        $pageKey = $pageIdentifier ?? match ($routeName) {
            'home' => 'home',
            'shop.index' => 'shop',
            'blog.index' => 'blog-index',
            'blog.show' => 'blog-post',
            'salon.index' => 'salon-index',
            'vendor.guide' => 'vendor-guide',
            'about' => 'about',
            'contact.index' => 'contact',
            default => ($routeName === 'pages.show' && isset($page)) ? 'page-'.$page->slug : ($routeName ? 'page-'.$routeName : 'page-unknown'),
        };
    @endphp

    @php $suppressHeaderWidgets = $suppressHeaderWidgets ?? false; @endphp
    @unless($suppressHeaderWidgets)
        @include('components.widgets-renderer', ['page' => $pageKey, 'region' => 'header'])
    @endunless

    <main class="flex-grow page-enter{{ $hideFooter ?? false ? ' auth-full-height' : '' }}">

        {{-- Breadcrumbs shown by default - set $hideBreadcrumbs = true in page to hide --}}
        @unless($hideBreadcrumbs ?? false)
            <x-breadcrumbs :items="$breadcrumbs ?? null" :hide-breadcrumbs="$hideBreadcrumbs ?? false" />
        @endunless

        @if(session('success'))
            <div class="site-container px-4 sm:px-6 lg:px-8 mt-4">
                <div class="bg-green-900 border border-green-700 text-green-100 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="site-container px-4 sm:px-6 lg:px-8 mt-4">
                <div class="bg-red-900 border border-red-700 text-red-100 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            </div>
        @endif

        @include('components.widgets-renderer', ['page' => $pageKey, 'region' => 'main'])

        @yield('content')
    </main>

    @include('components.widgets-renderer', ['page' => $pageKey, 'region' => 'footer'])
    @if(!($hideFooter ?? false))
        @include('components.site-footer')
    @endif
    @include('components.scroll-to-top')

    @php
        $chatbotEnabled = \App\Models\Setting::getValue('chatbot_enabled', true);
        $chatbotShowOnHome = \App\Models\Setting::getValue('chatbot_show_on_home', true);
        $chatbotShowOnShopPages = \App\Models\Setting::getValue('chatbot_show_on_shop_pages', true);
        $isHomeRoute = request()->routeIs('home');
        $shouldShowChatbot = $chatbotEnabled && (($isHomeRoute && $chatbotShowOnHome) || (! $isHomeRoute && $chatbotShowOnShopPages));
    @endphp
    @if($shouldShowChatbot)
        @include('components.chatbot-widget')
    @endif

    <script>
        // Enhanced sticky header with scroll effect
        // Using requestAnimationFrame for smoother scroll handling
        let ticking = false;
        
        function updateHeaderState() {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            const nav = document.getElementById('main-nav');
            
            if (nav) {
                // Directly manipulate classes instead of using Alpine.js internal API
                if (scrollTop > 20) {
                    nav.classList.add('shadow-lg', 'bg-white/95', 'backdrop-blur-md');
                    nav.classList.remove('shadow-sm');
                } else {
                    nav.classList.remove('shadow-lg', 'bg-white/95', 'backdrop-blur-md');
                    nav.classList.add('shadow-sm');
                }
            }
            
            ticking = false;
        }
        
        window.addEventListener('scroll', function() {
            if (!ticking) {
                window.requestAnimationFrame(updateHeaderState);
                ticking = true;
            }
        }, { passive: true });
        
        // Initial check
        updateHeaderState();
    </script>
    @include('components.password-toggle-script')
    @stack('scripts')
</body>
</html>

