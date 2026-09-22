<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <x-seo
        :title="trim($__env->yieldContent('title', 'Vendor Dashboard')).' - '.config('app.name', 'Smart African Market')"
        :description="$seoDescription ?? 'Vendor dashboard for managing products, orders, subscriptions, and storefront performance.'"
        :keywords="$seoKeywords ?? 'vendor dashboard, marketplace vendor, product management'"
        :image="$seoImage ?? null"
        :canonical="$seoCanonical ?? null"
        :type="$seoType ?? 'website'"
    />
    @php
        try {
            $containerWidth = max(1024, min(1600, (int) \App\Models\Setting::getValue('theme_container_width', 1600)));
        } catch (\Exception $e) {
            $containerWidth = 1600;
        }
    @endphp
    <style>
        :root {
            --theme-container-width: {{ $containerWidth }}px;
        }
        html, body {
            max-width: 100%;
            overflow-x: hidden;
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
        .app-page-container {
            max-width: var(--theme-container-width);
            margin-left: auto;
            margin-right: auto;
            width: 100%;
        }
        .no-scrollbar {
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        .no-scrollbar::-webkit-scrollbar {
            width: 0;
            height: 0;
        }
        .hover\:bg-gray-750:hover {
            background-color: rgba(55, 65, 81, 0.5);
        }
        #sidebar {
            transition-property: width, transform !important;
            transition-duration: 200ms, 300ms !important;
            transition-timing-function: ease-in-out !important;
            will-change: width, transform;
            overflow-x: hidden;
        }
        #sidebar-collapse-toggle svg {
            transition: transform 200ms ease-in-out;
        }
        body.sidebar-collapsed #sidebar-collapse-toggle svg {
            transform: rotate(180deg);
        }
        @media (min-width: 1024px) {
            #sidebar {
                position: sticky !important;
                top: 0 !important;
                height: 100vh !important;
            }
            #sidebar .sidebar-brand-text,
            #sidebar .sidebar-label {
                overflow: hidden;
                white-space: nowrap;
                max-width: 20rem;
                opacity: 1;
                transition: max-width 200ms ease-in-out, opacity 150ms ease-in-out, margin-left 200ms ease-in-out;
            }
            #sidebar .sidebar-label {
                display: inline-block;
            }
            body.sidebar-collapsed #sidebar {
                width: 5rem !important;
            }
            body.sidebar-collapsed #sidebar .sidebar-brand-text,
            body.sidebar-collapsed #sidebar .sidebar-label {
                max-width: 0 !important;
                opacity: 0 !important;
                margin-left: 0 !important;
            }
            body.sidebar-collapsed #sidebar .submenu-indicator {
                display: none !important;
            }
            body.sidebar-collapsed #sidebar nav {
                padding: 0.5rem !important;
            }
            body.sidebar-collapsed #sidebar nav a,
            body.sidebar-collapsed #sidebar nav button {
                justify-content: center !important;
                padding-left: 0.75rem !important;
                padding-right: 0.75rem !important;
            }
        }
    </style>
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    
    @stack('styles')
</head>
<body class="font-sans antialiased bg-gray-900 text-gray-100">
    <button id="sidebar-toggle" class="lg:hidden fixed top-4 left-4 z-50 p-2 bg-gray-800 text-white rounded-lg">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
    </button>

    <div class="min-h-screen flex flex-col lg:flex-row">
        <aside id="sidebar" class="fixed lg:sticky lg:top-0 lg:h-screen lg:overflow-hidden inset-y-0 left-0 z-40 w-72 bg-gray-800 border-r border-gray-700 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out flex-shrink-0">
            <div class="px-4 py-6 border-b border-gray-700">
                <div class="flex items-center space-x-2">
                    <a href="{{ route('home') }}" class="flex items-center space-x-2 min-w-0" aria-label="{{ config('app.name', 'Smart African Market') }} - Go to homepage">
                        <img src="{{ asset(config('branding.logo')) }}" alt="{{ config('app.name', 'Smart African Market') }}" class="h-8 w-8">
                        <div class="sidebar-brand-text min-w-0">
                            <h2 class="text-sm font-bold uppercase tracking-wide leading-tight text-white">Smart <span class="text-indigo-400">African</span></h2>
                            <p class="text-xs text-gray-400 tracking-wide">VENDOR PORTAL</p>
                        </div>
                    </a>
                    <button id="sidebar-collapse-toggle" type="button" class="hidden lg:inline-flex ml-auto items-center justify-center w-9 h-9 rounded-lg border border-gray-700 bg-gray-800 text-gray-200 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-600" aria-label="Toggle sidebar" title="Collapse sidebar">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l-7 7 7 7M15 5h2m-2 14h2"/>
                        </svg>
                    </button>
                </div>
            </div>
            <nav class="p-4 space-y-1 overflow-x-visible no-scrollbar h-[calc(100vh-120px)] flex flex-col pb-0">
                @php
                    try {
                        $canProducts = auth()->user()->hasModuleAccess('multiple_product_category');
                        $canBrands = auth()->user()->hasModuleAccess('product_brands_sync');
                        $canOrders = auth()->user()->hasModuleAccess('order_management');
                        $canEnquiries = auth()->user()->hasModuleAccess('product_enquiry');
                        $canRma = auth()->user()->hasModuleAccess('vendor_rma_request');
                        $canFollowers = auth()->user()->hasModuleAccess('follow_store');
                        $canTheme = auth()->user()->hasModuleAccess('vendor_independent_store');
                        $canAnnouncements = auth()->user()->hasModuleAccess('vendor_dashboard_announcements');
                        $showProductsMenu = $canProducts || $canBrands;
                        $effectiveSubscription = auth()->user()->effectiveSubscription();
                        $canStoreDomain = $effectiveSubscription && $effectiveSubscription->isActive() && optional($effectiveSubscription->plan)->slug === 'enterprise';
                    } catch (\Exception $e) {
                        $canProducts = false;
                        $canBrands = false;
                        $canOrders = false;
                        $canEnquiries = false;
                        $canRma = false;
                        $canFollowers = false;
                        $canTheme = false;
                        $canAnnouncements = false;
                        $showProductsMenu = false;
                        $effectiveSubscription = null;
                        $canStoreDomain = false;
                    }

                    $vendorSubmenuActive = [
                        'products' => request()->routeIs('vendor.products.*', 'vendor.reviews.*', 'vendor.attributes.*', 'vendor.variations.*', 'vendor.inventory.*', 'vendor.brands.*'),
                        'salon' => request()->routeIs('vendor.salon.*', 'vendor.salon-stylists.*', 'vendor.salon-services.*'),
                        'bookings' => request()->routeIs('vendor.bookings.*', 'vendor.settings.availability.*', 'vendor.booking-resources.*'),
                        'subscription' => request()->routeIs('vendor.subscription.*'),
                        'finance' => request()->routeIs('vendor.wallet.*', 'vendor.payouts.*', 'vendor.settings.payouts.*', 'vendor.verification.*', 'vendor.refunds.*'),
                    ];
                @endphp
                <a href="{{ route('vendor.dashboard') }}" class="flex items-center space-x-2 px-4 py-2 rounded {{ request()->routeIs('vendor.dashboard') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span class="sidebar-label">Dashboard</span>
                </a>
                <a href="{{ route('vendor.analytics.index') }}" class="flex items-center space-x-2 px-4 py-2 rounded {{ request()->routeIs('vendor.analytics.*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6m3 6V7m3 10v-4m-9 8h12a2 2 0 002-2V5a2 2 0 00-2-2H6a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    <span class="sidebar-label">Analytics</span>
                </a>

                @if($showProductsMenu)
                <div class="relative group space-y-1">
                    <button type="button" data-flyout-target="products-menu" aria-expanded="false" class="w-full flex items-center px-4 py-2 rounded {{ $vendorSubmenuActive['products'] ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700' }}">
                        <div class="flex items-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                            <span class="sidebar-label">Products Management</span>
                        </div>
                        <span aria-hidden="true" class="submenu-indicator ml-auto w-2.5 h-2.5 border-t border-r border-gray-500 rotate-45 opacity-60 group-hover:opacity-90"></span>
                    </button>
                    <div id="products-menu" data-active="{{ $vendorSubmenuActive['products'] ? '1' : '0' }}" class="hidden ml-4 lg:ml-0 space-y-1 bg-gray-800 border border-gray-700 rounded-lg shadow-2xl p-2 lg:fixed lg:w-80 lg:z-[9999] lg:max-h-[70vh] lg:overflow-y-auto no-scrollbar">
                        @if($canProducts)
                        <a href="{{ route('vendor.products.index') }}" class="flex items-center space-x-2 px-4 py-2 rounded text-sm {{ request()->routeIs('vendor.products.*') ? 'bg-indigo-600 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-gray-300' }}">
                            <span>All Products</span>
                        </a>
                        <a href="{{ route('vendor.products.create') }}" class="flex items-center space-x-2 px-4 py-2 rounded text-sm {{ request()->routeIs('vendor.products.create') ? 'bg-indigo-600 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-gray-300' }}">
                            <span>Add Product</span>
                        </a>
                        <a href="{{ route('vendor.reviews.index') }}" class="flex items-center space-x-2 px-4 py-2 rounded text-sm {{ request()->routeIs('vendor.reviews.*') ? 'bg-indigo-600 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-gray-300' }}">
                            <span>Reviews</span>
                        </a>
                        <a href="{{ route('vendor.attributes.index') }}" class="flex items-center space-x-2 px-4 py-2 rounded text-sm {{ request()->routeIs('vendor.attributes.*') ? 'bg-indigo-600 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-gray-300' }}">
                            <span>Attributes</span>
                        </a>
                        <a href="{{ route('vendor.variations.index') }}" class="flex items-center space-x-2 px-4 py-2 rounded text-sm {{ request()->routeIs('vendor.variations.*') ? 'bg-indigo-600 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-gray-300' }}">
                            <span>Variations</span>
                        </a>
                        <a href="{{ route('vendor.inventory.index') }}" class="flex items-center space-x-2 px-4 py-2 rounded text-sm {{ request()->routeIs('vendor.inventory.*') ? 'bg-indigo-600 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-gray-300' }}">
                            <span>Inventory</span>
                        </a>
                        @endif
                        @if($canBrands)
                        <a href="{{ route('vendor.brands.index') }}" class="flex items-center space-x-2 px-4 py-2 rounded text-sm {{ request()->routeIs('vendor.brands.*') ? 'bg-indigo-600 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-gray-300' }}">
                            <span>Brands</span>
                        </a>
                        @endif
                    </div>
                </div>
                @endif

                <div class="relative group space-y-1">
                    <button type="button" data-flyout-target="salon-menu" aria-expanded="false" class="w-full flex items-center px-4 py-2 rounded {{ $vendorSubmenuActive['salon'] ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700' }}">
                        <div class="flex items-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="sidebar-label">Salon Services</span>
                        </div>
                        <span aria-hidden="true" class="submenu-indicator ml-auto w-2.5 h-2.5 border-t border-r border-gray-500 rotate-45 opacity-60 group-hover:opacity-90"></span>
                    </button>
                    <div id="salon-menu" data-active="{{ $vendorSubmenuActive['salon'] ? '1' : '0' }}" class="hidden ml-4 lg:ml-0 space-y-1 bg-gray-800 border border-gray-700 rounded-lg shadow-2xl p-2 lg:fixed lg:w-80 lg:z-[9999] lg:max-h-[70vh] lg:overflow-y-auto no-scrollbar">
                        <a href="{{ route('vendor.salon.index') }}" class="flex items-center space-x-2 px-4 py-2 rounded text-sm {{ request()->routeIs('vendor.salon.*') ? 'bg-indigo-600 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-gray-300' }}">
                            <span>Salon</span>
                        </a>
                        <a href="{{ route('vendor.salon-stylists.index') }}" class="flex items-center space-x-2 px-4 py-2 rounded text-sm {{ request()->routeIs('vendor.salon-stylists.*') ? 'bg-indigo-600 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-gray-300' }}">
                            <span>Stylists</span>
                        </a>
                        <a href="{{ route('vendor.salon-services.index') }}" class="flex items-center space-x-2 px-4 py-2 rounded text-sm {{ request()->routeIs('vendor.salon-services.*') ? 'bg-indigo-600 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-gray-300' }}">
                            <span>Services</span>
                        </a>
                    </div>
                </div>

                <div class="relative group space-y-1">
                    <button type="button" data-flyout-target="bookings-menu" aria-expanded="false" class="w-full flex items-center px-4 py-2 rounded {{ $vendorSubmenuActive['bookings'] ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700' }}">
                        <div class="flex items-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10m-12 9h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            <span class="sidebar-label">Bookings</span>
                        </div>
                        <span aria-hidden="true" class="submenu-indicator ml-auto w-2.5 h-2.5 border-t border-r border-gray-500 rotate-45 opacity-60 group-hover:opacity-90"></span>
                    </button>
                    <div id="bookings-menu" data-active="{{ $vendorSubmenuActive['bookings'] ? '1' : '0' }}" class="hidden ml-4 lg:ml-0 space-y-1 bg-gray-800 border border-gray-700 rounded-lg shadow-2xl p-2 lg:fixed lg:w-80 lg:z-[9999] lg:max-h-[70vh] lg:overflow-y-auto no-scrollbar">
                        <a href="{{ route('vendor.bookings.index') }}" class="flex items-center space-x-2 px-4 py-2 rounded text-sm {{ request()->routeIs('vendor.bookings.*') ? 'bg-indigo-600 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-gray-300' }}">
                            <span>Bookings</span>
                        </a>
                        <a href="{{ route('vendor.settings.availability.index') }}" class="flex items-center space-x-2 px-4 py-2 rounded text-sm {{ request()->routeIs('vendor.settings.availability.*') ? 'bg-indigo-600 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-gray-300' }}">
                            <span>Availability</span>
                        </a>
                        <a href="{{ route('vendor.booking-resources.index') }}" class="flex items-center space-x-2 px-4 py-2 rounded text-sm {{ request()->routeIs('vendor.booking-resources.*') ? 'bg-indigo-600 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-gray-300' }}">
                            <span>Resources</span>
                        </a>
                    </div>
                </div>

                <div class="relative group space-y-1">
                    <button type="button" data-flyout-target="subscription-menu" aria-expanded="false" class="w-full flex items-center px-4 py-2 rounded {{ $vendorSubmenuActive['subscription'] ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700' }}">
                        <div class="flex items-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                            <span class="sidebar-label">Subscription</span>
                        </div>
                        <span aria-hidden="true" class="submenu-indicator ml-auto w-2.5 h-2.5 border-t border-r border-gray-500 rotate-45 opacity-60 group-hover:opacity-90"></span>
                    </button>
                    <div id="subscription-menu" data-active="{{ $vendorSubmenuActive['subscription'] ? '1' : '0' }}" class="hidden ml-4 lg:ml-0 space-y-1 bg-gray-800 border border-gray-700 rounded-lg shadow-2xl p-2 lg:fixed lg:w-80 lg:z-[9999] lg:max-h-[70vh] lg:overflow-y-auto no-scrollbar">
                        <a href="{{ route('vendor.subscription.index') }}" class="flex items-center space-x-2 px-4 py-2 rounded text-sm {{ request()->routeIs('vendor.subscription.*') && !request()->routeIs('vendor.subscription.upgrade') ? 'bg-indigo-600 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-gray-300' }}">
                            <span>My Subscription</span>
                        </a>
                        <a href="{{ route('vendor.subscription.upgrade') }}" class="flex items-center space-x-2 px-4 py-2 rounded text-sm {{ request()->routeIs('vendor.subscription.upgrade') ? 'bg-indigo-600 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-gray-300' }}">
                            <span>Upgrade Plan</span>
                        </a>
                    </div>
                </div>

                @if($canOrders)
                <a href="{{ route('vendor.orders.index') }}" class="flex items-center space-x-2 px-4 py-2 rounded {{ request()->routeIs('vendor.orders.*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    <span class="sidebar-label">Orders</span>
                </a>
                @endif
                <a href="{{ route('vendor.coupons.index') }}" class="flex items-center space-x-2 px-4 py-2 rounded {{ request()->routeIs('vendor.coupons.*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    <span class="sidebar-label">Coupons</span>
                </a>

                <a href="{{ route('vendor.withdrawals.index') }}" class="flex items-center space-x-2 px-4 py-2 rounded {{ request()->routeIs('vendor.withdrawals.*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="sidebar-label">Withdrawals</span>
                </a>
                <div class="relative group space-y-1">
                    <button type="button" data-flyout-target="finance-menu" aria-expanded="false" class="w-full flex items-center px-4 py-2 rounded {{ $vendorSubmenuActive['finance'] ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700' }}">
                        <div class="flex items-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="sidebar-label">Finance & Settings</span>
                        </div>
                        <span aria-hidden="true" class="submenu-indicator ml-auto w-2.5 h-2.5 border-t border-r border-gray-500 rotate-45 opacity-60 group-hover:opacity-90"></span>
                    </button>
                    <div id="finance-menu" data-active="{{ $vendorSubmenuActive['finance'] ? '1' : '0' }}" class="hidden ml-4 lg:ml-0 space-y-1 bg-gray-800 border border-gray-700 rounded-lg shadow-2xl p-2 lg:fixed lg:w-80 lg:z-[9999] lg:max-h-[70vh] lg:overflow-y-auto no-scrollbar">
                        <a href="{{ route('vendor.wallet.index') }}" class="flex items-center space-x-2 px-4 py-2 rounded text-sm {{ request()->routeIs('vendor.wallet.*') ? 'bg-indigo-600 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-gray-300' }}">
                            <span>Wallet</span>
                        </a>
                        <a href="{{ route('vendor.payouts.index') }}" class="flex items-center space-x-2 px-4 py-2 rounded text-sm {{ request()->routeIs('vendor.payouts.*') ? 'bg-indigo-600 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-gray-300' }}">
                            <span>Payouts</span>
                        </a>
                        <a href="{{ route('vendor.settings.payouts.edit') }}" class="flex items-center space-x-2 px-4 py-2 rounded text-sm {{ request()->routeIs('vendor.settings.payouts.*') ? 'bg-indigo-600 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-gray-300' }}">
                            <span>Payout Settings</span>
                        </a>
                        <a href="{{ route('vendor.verification.index') }}" class="flex items-center space-x-2 px-4 py-2 rounded text-sm {{ request()->routeIs('vendor.verification.*') ? 'bg-indigo-600 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-gray-300' }}">
                            <span>Verification</span>
                        </a>
                        <a href="{{ route('vendor.refunds.index') }}" class="flex items-center space-x-2 px-4 py-2 rounded text-sm {{ request()->routeIs('vendor.refunds.*') ? 'bg-indigo-600 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-gray-300' }}">
                            <span>Refund Requests</span>
                        </a>
                    </div>
                </div>

                @if($canEnquiries)
                <a href="{{ route('vendor.enquiries.index') }}" class="flex items-center space-x-2 px-4 py-2 rounded {{ request()->routeIs('vendor.enquiries.*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="sidebar-label">Product Enquiries</span>
                </a>
                @endif

                @if($canRma)
                <a href="{{ route('vendor.rma.index') }}" class="flex items-center space-x-2 px-4 py-2 rounded {{ request()->routeIs('vendor.rma.*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"/>
                    </svg>
                    <span class="sidebar-label">RMA Requests</span>
                </a>
                @endif

                @if($canFollowers)
                <a href="{{ route('vendor.followers.index') }}" class="flex items-center space-x-2 px-4 py-2 rounded {{ request()->routeIs('vendor.followers.*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span class="sidebar-label">Followers</span>
                </a>
                @endif

                @if($canTheme)
                <a href="{{ route('vendor.theme.index') }}" class="flex items-center space-x-2 px-4 py-2 rounded {{ request()->routeIs('vendor.theme.*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                    </svg>
                    <span class="sidebar-label">Store Theme</span>
                </a>
                @endif

                @if($canStoreDomain)
                <a href="{{ route('vendor.store-domain.index') }}" class="flex items-center space-x-2 px-4 py-2 rounded {{ request()->routeIs('vendor.store-domain.*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.6 9h16.8M3.6 15h16.8M12 3c2.5 2.4 3.9 5.6 3.9 9s-1.4 6.6-3.9 9m0-18c-2.5 2.4-3.9 5.6-3.9 9s1.4 6.6 3.9 9"/>
                    </svg>
                    <span class="sidebar-label">Store Domain</span>
                </a>
                @endif

                @if($canAnnouncements)
                <a href="{{ route('vendor.announcements.index') }}" class="flex items-center space-x-2 px-4 py-2 rounded {{ request()->routeIs('vendor.announcements.*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                    </svg>
                    <span class="sidebar-label">Announcements</span>
                </a>
                @endif

                <hr class="my-4 border-gray-700">

                <a href="{{ route('home') }}" class="flex items-center space-x-2 px-4 py-2 rounded text-gray-300 hover:bg-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span class="sidebar-label">Visit Site</span>
                </a>
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-2 px-4 py-2 rounded text-gray-300 hover:bg-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span class="sidebar-label">User Dashboard</span>
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center space-x-2 px-4 py-2 rounded text-gray-300 hover:bg-gray-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        <span class="sidebar-label">Logout</span>
                    </button>
                </form>
                <button type="button" class="border-t border-gray-700 mt-auto py-1 px-4 w-full text-left text-xs text-gray-500 sticky bottom-0 bg-gray-800 hover:bg-gray-700">
                    <span class="sidebar-label">Version {{ config('app.version') }}</span>
                </button>
            </nav>
        </aside>

        <main class="flex-1 flex flex-col min-w-0 w-full">
            <header class="sticky top-0 z-30 bg-gray-800 border-b border-gray-700 flex-shrink-0">
                <div class="app-page-container px-4 lg:px-6 py-4 flex items-center justify-between">
                    <h2 class="text-lg lg:text-xl font-semibold text-white ml-12 lg:ml-0">@yield('header', 'Dashboard')</h2>
                    <div class="relative flex items-center space-x-4">
                        <span class="text-xs lg:text-sm text-gray-400 hidden sm:inline">{{ auth()->user()->name }}</span>
                        @php
                            $vendorAvatarValue = auth()->user()->avatar;
                            $vendorAvatarUrl = $vendorAvatarValue
                                ? (str_starts_with($vendorAvatarValue, 'http') ? $vendorAvatarValue : asset('storage/' . ltrim($vendorAvatarValue, '/')))
                                : null;
                        @endphp
                        <button id="vendor-profile-trigger" type="button" class="flex items-center bg-gray-700 border border-gray-600 rounded-full px-2 py-1 text-sm text-white hover:border-gray-500 focus:outline-none" aria-expanded="false" aria-haspopup="menu">
                            <span class="sr-only">Open profile menu</span>
                            <span class="w-6 h-6 rounded-full bg-gray-800 overflow-hidden flex items-center justify-center">
                                @if ($vendorAvatarUrl)
                                    <img src="{{ $vendorAvatarUrl }}" alt="{{ auth()->user()->name }} avatar" class="w-full h-full object-cover">
                                @else
                                    <span class="font-semibold text-xs">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                                @endif
                            </span>
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div id="vendor-profile-menu" class="absolute right-0 top-full z-40 w-56 rounded-lg bg-gray-800 border border-gray-700 shadow-lg text-left text-sm text-gray-200 hidden">
                            <a href="{{ route('profile.edit') }}#profile-summary" class="block px-4 py-2 hover:bg-gray-700">View Profile</a>
                            <a href="{{ route('profile.edit') }}#profile-info" class="block px-4 py-2 hover:bg-gray-700">Edit Profile</a>
                            <a href="{{ route('profile.edit') }}#profile-photo" class="block px-4 py-2 hover:bg-gray-700">Update Profile Picture</a>
                            <a href="{{ route('profile.edit') }}#password" class="block px-4 py-2 hover:bg-gray-700">Change Password</a>
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 hover:bg-gray-700">Account Settings</a>
                            <a href="{{ route('vendor.settings.payouts.edit') }}" class="block px-4 py-2 hover:bg-gray-700">Payout Settings</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 hover:bg-gray-700">Logout</button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <div class="flex-1 overflow-y-auto p-4 lg:p-6">
                <div class="app-page-container">
                    @if(session('success'))
                        <div class="mb-4 bg-green-900 border border-green-700 text-green-100 px-4 py-3 rounded">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="mb-4 bg-red-900 border border-red-700 text-red-100 px-4 py-3 rounded">
                            {{ session('error') }}
                        </div>
                    @endif

                    @yield('content')
                </div>
            </div>
            @include('components.portal-footer')
        </main>
    </div>

    @include('components.scroll-to-top')

    <script>
        const sidebarToggle = document.getElementById('sidebar-toggle');
        const sidebar = document.getElementById('sidebar');

        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('-translate-x-full');
        });

        document.addEventListener('click', (e) => {
            if (window.innerWidth < 1024 && !sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                sidebar.classList.add('-translate-x-full');
            }
        });

        const sidebarCollapseToggle = document.getElementById('sidebar-collapse-toggle');
        const SIDEBAR_COLLAPSED_KEY = 'vendor_sidebar_collapsed';
        const LEGACY_SIDEBAR_COLLAPSED_KEY = 'sidebar_collapsed';
        const isDesktopCollapse = () => window.matchMedia('(min-width: 1024px)').matches;

        const updateCollapseButton = (collapsed) => {
            if (!sidebarCollapseToggle) return;
            sidebarCollapseToggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
            sidebarCollapseToggle.setAttribute('title', collapsed ? 'Expand sidebar' : 'Collapse sidebar');
        };

        const resetSubmenuPanel = (panel) => {
            if (!panel) return;
            panel.classList.add('hidden');
            panel.style.left = '';
            panel.style.top = '';
            panel.style.visibility = '';
            panel.style.position = '';
        };

        const hideAllFlyoutPanels = () => {
            document.querySelectorAll('#sidebar [data-flyout-target]').forEach((button) => {
                const submenuId = button.getAttribute('data-flyout-target');
                const panel = submenuId ? document.getElementById(submenuId) : null;
                resetSubmenuPanel(panel);
            });
        };

        const setSubmenuExpanded = (submenu, expanded) => {
            if (!submenu?.id) return;
            const trigger = document.querySelector(`#sidebar [data-flyout-target="${submenu.id}"]`);
            trigger?.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        };

        const closeMobileSubmenus = (keepId = null) => {
            if (window.innerWidth >= 1024) {
                return;
            }
            document.querySelectorAll('#sidebar [data-flyout-target]').forEach((button) => {
                const submenuId = button.getAttribute('data-flyout-target');
                const panel = submenuId ? document.getElementById(submenuId) : null;
                if (!panel || (keepId && panel.id === keepId)) {
                    return;
                }
                panel.classList.add('hidden');
                setSubmenuExpanded(panel, false);
            });
        };

        const syncSubmenuExpandedStates = () => {
            document.querySelectorAll('#sidebar [data-flyout-target]').forEach((button) => {
                const submenuId = button.getAttribute('data-flyout-target');
                const panel = submenuId ? document.getElementById(submenuId) : null;
                if (!panel) {
                    button.setAttribute('aria-expanded', 'false');
                    return;
                }
                setSubmenuExpanded(panel, !panel.classList.contains('hidden'));
            });
        };

        const openFirstActiveSubmenuOnMobile = () => {
            if (window.innerWidth >= 1024) {
                return;
            }
            const activeSubmenu = document.querySelector('#sidebar [data-flyout-target] + [data-active="1"]');
            if (!activeSubmenu) {
                return;
            }
            closeMobileSubmenus(activeSubmenu.id);
            activeSubmenu.classList.remove('hidden');
            setSubmenuExpanded(activeSubmenu, true);
        };

        const applyCollapsedState = () => {
            if (!isDesktopCollapse()) {
                document.body.classList.remove('sidebar-collapsed');
                updateCollapseButton(false);
                return;
            }
            let stored = localStorage.getItem(SIDEBAR_COLLAPSED_KEY);
            if (stored === null) {
                const legacy = localStorage.getItem(LEGACY_SIDEBAR_COLLAPSED_KEY);
                if (legacy !== null) {
                    stored = legacy;
                    localStorage.setItem(SIDEBAR_COLLAPSED_KEY, legacy);
                    localStorage.removeItem(LEGACY_SIDEBAR_COLLAPSED_KEY);
                }
            }
            const collapsed = stored === '1';
            document.body.classList.toggle('sidebar-collapsed', collapsed);
            updateCollapseButton(collapsed);
        };

        applyCollapsedState();
        if (isDesktopCollapse()) {
            hideAllFlyoutPanels();
        }
        syncSubmenuExpandedStates();
        openFirstActiveSubmenuOnMobile();

        sidebarCollapseToggle?.addEventListener('click', () => {
            if (!isDesktopCollapse()) return;
            const nowCollapsed = !document.body.classList.contains('sidebar-collapsed');
            document.body.classList.toggle('sidebar-collapsed', nowCollapsed);
            localStorage.setItem(SIDEBAR_COLLAPSED_KEY, nowCollapsed ? '1' : '0');
            updateCollapseButton(nowCollapsed);
            hideAllFlyoutPanels();
        });

        window.addEventListener('resize', () => {
            applyCollapsedState();
            if (isDesktopCollapse()) {
                hideAllFlyoutPanels();
            }
            syncSubmenuExpandedStates();
            openFirstActiveSubmenuOnMobile();
        });

        function toggleMenu(menuId, event) {
            if (event) {
                event.stopPropagation();
            }
            const menu = document.getElementById(menuId);
            const button = event && event.currentTarget;
            
            if (!menu) return;
            
            if (window.innerWidth >= 1024) {
                // Desktop: use flyout positioning
                menu.classList.toggle('hidden');
                if (!menu.classList.contains('hidden') && button) {
                    VendorUI.positionFlyout(button, menu);
                }
            } else {
                // Mobile: simple inline toggle
                closeMobileSubmenus(menuId);
                menu.classList.toggle('hidden');
            }
            setSubmenuExpanded(menu, !menu.classList.contains('hidden'));
        }

        (() => {
            const isDesktop = () => window.matchMedia('(min-width: 1024px)').matches;

            const nav = document.querySelector('#sidebar nav');
            const flyoutButtons = Array.from(document.querySelectorAll('[data-flyout-target]'));

            let openSubmenu = null;
            let openButton = null;
            let closeTimer = null;
            let lastDesktop = isDesktop();
            let lockedOpen = false;

            const clearCloseTimer = () => {
                if (closeTimer) {
                    clearTimeout(closeTimer);
                    closeTimer = null;
                }
            };

            const resetSubmenu = (submenu) => {
                if (!submenu) return;
                submenu.classList.add('hidden');
                submenu.style.left = '';
                submenu.style.top = '';
                submenu.style.visibility = '';
                submenu.style.position = '';
                setSubmenuExpanded(submenu, false);
            };

            const hideAllSubmenus = () => {
                flyoutButtons.forEach((button) => {
                    const submenuId = button.getAttribute('data-flyout-target');
                    const submenu = submenuId ? document.getElementById(submenuId) : null;
                    resetSubmenu(submenu);
                });
            };

            const closeFlyout = () => {
                clearCloseTimer();
                if (!openSubmenu) return;
                resetSubmenu(openSubmenu);
                openSubmenu = null;
                openButton = null;
                lockedOpen = false;
            };

            const positionFlyout = (button, submenu) => {
                if (!button || !submenu) return;
                submenu.style.position = 'fixed';

                submenu.style.visibility = 'hidden';
                submenu.style.left = '0px';
                submenu.style.top = '0px';
                submenu.classList.remove('hidden');

                const rect = button.getBoundingClientRect();
                const submenuWidth = submenu.offsetWidth || 320;
                const submenuHeight = submenu.offsetHeight || 400;
                const gap = 8;

                let left = rect.right + gap;
                if (left + submenuWidth > window.innerWidth - 8) {
                    left = rect.left - submenuWidth - gap;
                }
                left = Math.max(8, Math.min(left, window.innerWidth - submenuWidth - 8));

                const maxFlyoutHeight = Math.min(submenuHeight, Math.floor(window.innerHeight * 0.7));
                let top = rect.top;
                top = Math.max(8, Math.min(top, window.innerHeight - maxFlyoutHeight - 8));

                submenu.style.left = `${Math.round(left)}px`;
                submenu.style.top = `${Math.round(top)}px`;
                submenu.style.visibility = 'visible';
            };

            const openFlyout = (button, submenu) => {
                if (!isDesktop()) return;
                clearCloseTimer();
                lockedOpen = false;

                if (openSubmenu && openSubmenu !== submenu) {
                    closeFlyout();
                }

                openSubmenu = submenu;
                openButton = button;
                submenu.classList.remove('hidden');
                setSubmenuExpanded(submenu, true);
                positionFlyout(button, submenu);
            };

            const scheduleClose = () => {
                if (!isDesktop()) return;
                if (lockedOpen) return;
                clearCloseTimer();
                closeTimer = setTimeout(() => {
                    const active = document.activeElement;
                    if (openButton && openSubmenu && (openButton.contains(active) || openSubmenu.contains(active))) {
                        return;
                    }
                    closeFlyout();
                }, 140);
            };

            flyoutButtons.forEach((button) => {
                const submenuId = button.getAttribute('data-flyout-target');
                const submenu = submenuId ? document.getElementById(submenuId) : null;
                if (!submenu) return;

                button.addEventListener('click', (event) => {
                    event.preventDefault();
                    event.stopPropagation();
                    if (isDesktop()) {
                        if (openSubmenu && openSubmenu !== submenu) {
                            closeFlyout();
                        }
                        if (openSubmenu === submenu && !submenu.classList.contains('hidden')) {
                            if (lockedOpen) {
                                closeFlyout();
                                return;
                            }
                            lockedOpen = true;
                            return;
                        }
                        openSubmenu = submenu;
                        openButton = button;
                        lockedOpen = true;
                        submenu.classList.remove('hidden');
                        setSubmenuExpanded(submenu, true);
                        positionFlyout(button, submenu);
                        return;
                    }
                    toggleMenu(submenuId, event);
                });

                button.addEventListener('mouseenter', () => openFlyout(button, submenu));
                button.addEventListener('focusin', () => openFlyout(button, submenu));
                button.addEventListener('mouseleave', scheduleClose);
                button.addEventListener('focusout', () => setTimeout(scheduleClose, 0));

                submenu.addEventListener('mouseenter', clearCloseTimer);
                submenu.addEventListener('mouseleave', scheduleClose);
                submenu.addEventListener('focusin', clearCloseTimer);
                submenu.addEventListener('focusout', () => setTimeout(scheduleClose, 0));
            });

            nav?.addEventListener('scroll', () => {
                if (!isDesktop()) return;
                if (openButton && openSubmenu && !openSubmenu.classList.contains('hidden')) {
                    positionFlyout(openButton, openSubmenu);
                }
            }, { passive: true });

            window.addEventListener('resize', () => {
                const nowDesktop = isDesktop();
                if (nowDesktop !== lastDesktop) {
                    lastDesktop = nowDesktop;
                    if (nowDesktop) {
                        hideAllSubmenus();
                    } else {
                        closeFlyout();
                    }
                }

                if (!nowDesktop) {
                    closeFlyout();
                    return;
                }
                if (openButton && openSubmenu && !openSubmenu.classList.contains('hidden')) {
                    positionFlyout(openButton, openSubmenu);
                }
            });

            document.addEventListener('click', (e) => {
                if (!isDesktop()) return;
                if (!openSubmenu || !openButton) return;
                if (!openSubmenu.contains(e.target) && !openButton.contains(e.target)) {
                    closeFlyout();
                }
            });

            if (lastDesktop) {
                hideAllSubmenus();
            }

            // Expose positionFlyout globally for toggleMenu
            window.VendorUI = window.VendorUI || {};
            VendorUI.positionFlyout = positionFlyout;
        })();

        const vendorProfileTrigger = document.getElementById('vendor-profile-trigger');
        const vendorProfileMenu = document.getElementById('vendor-profile-menu');

        const closeVendorProfileMenu = () => {
            vendorProfileMenu?.classList.add('hidden');
            vendorProfileTrigger?.setAttribute('aria-expanded', 'false');
        };

        vendorProfileTrigger?.addEventListener('click', (event) => {
            event.stopPropagation();
            const expanded = vendorProfileTrigger.getAttribute('aria-expanded') === 'true';
            vendorProfileTrigger.setAttribute('aria-expanded', expanded ? 'false' : 'true');
            vendorProfileMenu?.classList.toggle('hidden');
        });

        document.addEventListener('click', (event) => {
            if (!vendorProfileMenu?.contains(event.target) && !vendorProfileTrigger?.contains(event.target)) {
                closeVendorProfileMenu();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeVendorProfileMenu();
            }
        });
    </script>
    
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    
    @stack('scripts')
</body>
</html>
