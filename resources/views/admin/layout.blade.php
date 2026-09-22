<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <x-seo 
        :title="trim($__env->yieldContent('title', 'Admin Dashboard')).' - '.config('app.name', 'Smart African Marketplace')"
        :description="$seoDescription ?? 'Admin dashboard for marketplace management, analytics, vendors, products, and operations.'"
        :keywords="$seoKeywords ?? 'admin dashboard, marketplace management, analytics'"
        :image="$seoImage ?? null"
        :canonical="$seoCanonical ?? null"
        :type="$seoType ?? 'website'"
    />
    @php
        try {
            $brandColorFrom = \App\Models\Setting::getValue('brand_color_from', '#ea580c');
            $brandColorTo = \App\Models\Setting::getValue('brand_color_to', '#dc2626');
        } catch (\Throwable $e) {
            $brandColorFrom = '#ea580c';
            $brandColorTo = '#dc2626';
        }
    @endphp
    <style>
        :root {
            --brand-from: {{ $brandColorFrom }};
            --brand-to: {{ $brandColorTo }};
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
        .bg-brand-gradient {
            background: linear-gradient(to right, var(--brand-from), var(--brand-to));
        }
        .bg-brand-from {
            background-color: var(--brand-from);
        }
        .hover\:bg-brand-from:hover {
            background-color: var(--brand-from);
        }
        .text-brand-from {
            color: var(--brand-from);
        }
        .border-brand-from {
            border-color: var(--brand-from);
        }
        .focus\:ring-brand-from:focus {
            --tw-ring-color: var(--brand-from);
        }
        .focus\:border-brand-from:focus {
            border-color: var(--brand-from);
        }
        .hover\:bg-gray-750:hover {
            background-color: rgba(55, 65, 81, 0.5);
        }
        .no-scrollbar {
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        .no-scrollbar::-webkit-scrollbar {
            width: 0;
            height: 0;
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
</head>
<body class="font-sans antialiased bg-gray-900 text-gray-100">
            <button id="sidebar-toggle" type="button" aria-label="Toggle sidebar menu" class="lg:hidden fixed top-4 left-4 z-50 p-2 bg-gray-800 text-white rounded-lg">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
    </button>

    <div class="min-h-screen flex flex-col lg:flex-row">
        <aside id="sidebar" class="fixed top-0 left-0 h-screen lg:sticky lg:top-0 overflow-hidden z-40 w-64 bg-gray-800 border-r border-gray-700 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out flex-shrink-0">
            <div class="p-6 border-b border-gray-700">
                <div class="flex items-center space-x-2">
                    <a href="{{ route('home') }}" class="flex items-center space-x-2 min-w-0" aria-label="{{ config('app.name', 'Smart African Marketplace') }} - Go to homepage">
                        <img src="{{ asset(config('branding.logo')) }}" alt="{{ config('app.name', 'Smart African Marketplace') }}" class="h-8 w-8">
                        <div class="sidebar-brand-text">
                            <h1 class="text-sm font-bold uppercase">Smart <span class="text-brand-from">African</span></h1>
                            <p class="text-xs text-gray-300">MARKETPLACE</p>
                        </div>
                    </a>
                    <button id="sidebar-collapse-toggle" type="button" class="hidden lg:inline-flex ml-auto items-center justify-center w-9 h-9 rounded-lg border border-gray-700 bg-gray-800 text-gray-200 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-600" aria-label="Toggle sidebar" title="Collapse sidebar">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l-7 7 7 7M15 5h2m-2 14h2"/>
                        </svg>
                    </button>
                </div>
            </div>
            @php
                $adminSubmenuActive = [
                    'media' => request()->routeIs('admin.media.*'),
                    'products' => request()->routeIs('admin.products.*', 'admin.categories.*', 'admin.products.brands.*', 'admin.products.tags.*', 'admin.products.attributes.*', 'admin.products.variations.*', 'admin.reviews.*'),
                    'marketplace' => request()->routeIs('admin.vendors.*', 'admin.vendor-verifications.*', 'admin.payouts.*', 'admin.withdrawals.*', 'admin.refunds.*'),
                    'blog' => request()->routeIs('admin.blog.*'),
                    'pages' => request()->routeIs('admin.hero-sections.*', 'admin.about.*', 'admin.announcements.*', 'admin.pages.*'),
                    'growth' => request()->routeIs('admin.loyalty.*', 'admin.referrals.*', 'admin.campaigns.*', 'admin.coupons.*', 'admin.announcements.*'),
                    'appearance' => request()->routeIs('admin.appearance.*', 'admin.hero-sections.*', 'admin.widgets.*'),
                    'tools' => request()->routeIs('admin.chatbot.*', 'admin.ai-settings.*', 'admin.support-tickets.*'),
                    'settings' => request()->routeIs('admin.settings.*', 'admin.users.*', 'admin.subscriptions.*', 'admin.shipping-settings.*', 'admin.maintenance.*'),
                ];
            @endphp
            <nav class="p-4 space-y-1 overflow-x-visible no-scrollbar h-[calc(100vh-120px)]">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-2 px-4 py-2 rounded {{ request()->routeIs('admin.dashboard') ? 'bg-brand-gradient text-white' : 'text-gray-300 hover:bg-gray-700' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span class="sidebar-label">Dashboard</span>
                </a>
                <div class="relative group space-y-1">
                    <button type="button" data-flyout-target="media-submenu" aria-expanded="false" class="w-full flex items-center px-4 py-2 rounded {{ $adminSubmenuActive['media'] ? 'bg-brand-gradient text-white' : 'text-gray-300 hover:bg-gray-700' }}">
                        <div class="flex items-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span class="sidebar-label">Media Library</span>
                        </div>
                        <span aria-hidden="true" class="submenu-indicator ml-auto w-2.5 h-2.5 border-t border-r border-gray-500 rotate-45 opacity-60 group-hover:opacity-90"></span>
                    </button>
                    <div id="media-submenu" data-active="{{ $adminSubmenuActive['media'] ? '1' : '0' }}" class="hidden ml-4 lg:ml-0 space-y-1 bg-gray-800 border border-gray-700 rounded-lg shadow-2xl p-2 lg:fixed lg:w-80 lg:z-[9999] lg:max-h-[70vh] lg:overflow-y-auto no-scrollbar">
                        <a href="{{ route('admin.media.index') }}" class="flex items-center space-x-2 px-4 py-2 rounded text-sm {{ request()->routeIs('admin.media.index', 'admin.media.show', 'admin.media.edit', 'admin.media.create', 'admin.media.settings') && !request()->has('folder_id') ? 'bg-gray-700 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-gray-300' }}">
                            <span>Media</span>
                        </a>
                        <a href="{{ route('admin.media.index', ['folder_id' => 'all']) }}" class="flex items-center space-x-2 px-4 py-2 rounded text-sm {{ request()->get('folder_id') ? 'bg-gray-700 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-gray-300' }}">
                            <span>Folders</span>
                        </a>
                    </div>
                </div>
                <div class="relative group space-y-1">
                    <button type="button" data-flyout-target="products-submenu" aria-expanded="false" class="w-full flex items-center px-4 py-2 rounded {{ $adminSubmenuActive['products'] ? 'bg-brand-gradient text-white' : 'text-gray-300 hover:bg-gray-700' }}">
                        <div class="flex items-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                            <span class="sidebar-label">Products</span>
                        </div>
                        <span aria-hidden="true" class="submenu-indicator ml-auto w-2.5 h-2.5 border-t border-r border-gray-500 rotate-45 opacity-60 group-hover:opacity-90"></span>
                    </button>
                    <div id="products-submenu" data-active="{{ $adminSubmenuActive['products'] ? '1' : '0' }}" class="hidden ml-4 lg:ml-0 space-y-1 bg-gray-800 border border-gray-700 rounded-lg shadow-2xl p-2 lg:fixed lg:w-80 lg:z-[9999] lg:max-h-[70vh] lg:overflow-y-auto no-scrollbar">
                        <a href="{{ route('admin.products.index') }}" class="flex items-center space-x-2 px-4 py-2 rounded text-sm {{ request()->routeIs('admin.products.index', 'admin.products.create', 'admin.products.edit', 'admin.products.show', 'admin.products.variants.*') ? 'bg-gray-700 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-gray-300' }}">
                            <span>All Products</span>
                        </a>
                        <a href="{{ route('admin.products.create') }}" class="flex items-center space-x-2 px-4 py-2 rounded text-sm {{ request()->routeIs('admin.products.create') ? 'bg-gray-700 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-gray-300' }}">
                            <span>Add New</span>
                        </a>
                        <a href="{{ route('admin.categories.index') }}" class="flex items-center space-x-2 px-4 py-2 rounded text-sm {{ request()->routeIs('admin.categories.*') ? 'bg-gray-700 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-gray-300' }}">
                            <span>Categories</span>
                        </a>
                        <a href="{{ route('admin.products.tags.index') }}" class="flex items-center space-x-2 px-4 py-2 rounded text-sm {{ request()->routeIs('admin.products.tags.*') ? 'bg-gray-700 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-gray-300' }}">
                            <span>Tags</span>
                        </a>
                        <a href="{{ route('admin.products.attributes.index') }}" class="flex items-center space-x-2 px-4 py-2 rounded text-sm {{ request()->routeIs('admin.products.attributes.*') ? 'bg-gray-700 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-gray-300' }}">
                            <span>Attributes</span>
                        </a>
                        <a href="{{ route('admin.products.variations.overview') }}" class="flex items-center space-x-2 px-4 py-2 rounded text-sm {{ request()->routeIs('admin.products.variations.overview') ? 'bg-gray-700 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-gray-300' }}">
                            <span>Variations</span>
                        </a>
                        <a href="{{ route('admin.products.brands.index') }}" class="flex items-center space-x-2 px-4 py-2 rounded text-sm {{ request()->routeIs('admin.products.brands.*') ? 'bg-gray-700 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-gray-300' }}">
                            <span>Brands</span>
                        </a>
                        <hr class="my-2 border-gray-700">
                        <a href="{{ route('admin.products.stock-management') }}" class="flex items-center space-x-2 px-4 py-2 rounded text-sm {{ request()->routeIs('admin.products.stock-management') ? 'bg-gray-700 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-gray-300' }}">
                            <span>Stock Management</span>
                        </a>
                        <a href="{{ route('admin.products.import') }}" class="flex items-center space-x-2 px-4 py-2 rounded text-sm {{ request()->routeIs('admin.products.import', 'admin.products.import.process') ? 'bg-gray-700 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-gray-300' }}">
                            <span>Import Products</span>
                        </a>
                        <a href="{{ route('admin.products.export') }}" class="flex items-center space-x-2 px-4 py-2 rounded text-sm {{ request()->routeIs('admin.products.export', 'admin.products.export.process') ? 'bg-gray-700 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-gray-300' }}">
                            <span>Export Products</span>
                        </a>
                        <a href="{{ route('admin.reviews.index') }}" class="flex items-center space-x-2 px-4 py-2 rounded text-sm {{ request()->routeIs('admin.reviews.*') ? 'bg-gray-700 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-gray-300' }}">
                            <span>Product Reviews</span>
                        </a>
                    </div>
                </div>
                <a href="{{ route('admin.orders.index') }}" class="flex items-center space-x-2 px-4 py-2 rounded {{ request()->routeIs('admin.orders.*') ? 'bg-brand-gradient text-white' : 'text-gray-300 hover:bg-gray-700' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <span class="sidebar-label">Orders</span>
                </a>
                <div class="relative group space-y-1">
                    <button type="button" data-flyout-target="marketplace-submenu" aria-expanded="false" class="w-full flex items-center px-4 py-2 rounded {{ $adminSubmenuActive['marketplace'] ? 'bg-brand-gradient text-white' : 'text-gray-300 hover:bg-gray-700' }}">
                        <div class="flex items-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M5 11h14M7 15h10M9 19h6"/>
                            </svg>
                            <span class="sidebar-label">Marketplace Manager</span>
                        </div>
                        <span aria-hidden="true" class="submenu-indicator ml-auto w-2.5 h-2.5 border-t border-r border-gray-500 rotate-45 opacity-60 group-hover:opacity-90"></span>
                    </button>
                    <div id="marketplace-submenu" data-active="{{ $adminSubmenuActive['marketplace'] ? '1' : '0' }}" class="hidden ml-4 lg:ml-0 space-y-1 bg-gray-800 border border-gray-700 rounded-lg shadow-2xl p-2 lg:fixed lg:w-80 lg:z-[9999] lg:max-h-[70vh] lg:overflow-y-auto no-scrollbar">
                        <a href="{{ route('admin.vendors.index') }}" class="flex items-center space-x-2 px-4 py-2 rounded text-sm {{ request()->routeIs('admin.vendors.*') ? 'bg-gray-700 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-gray-300' }}">
                            <span>Vendors</span>
                        </a>
                        <a href="{{ route('admin.vendor-verifications.index') }}" class="flex items-center space-x-2 px-4 py-2 rounded text-sm {{ request()->routeIs('admin.vendor-verifications.*') ? 'bg-gray-700 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-gray-300' }}">
                            <span>Verifications</span>
                        </a>
                        <a href="{{ route('admin.payouts.index') }}" class="flex items-center space-x-2 px-4 py-2 rounded text-sm {{ request()->routeIs('admin.payouts.*') ? 'bg-gray-700 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-gray-300' }}">
                            <span>Ledger</span>
                        </a>
                    </div>
                </div>
                <div class="relative group space-y-1">
                    <button type="button" data-flyout-target="blog-submenu" aria-expanded="false" class="w-full flex items-center px-4 py-2 rounded {{ $adminSubmenuActive['blog'] ? 'bg-brand-gradient text-white' : 'text-gray-300 hover:bg-gray-700' }}">
                        <div class="flex items-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                            <span class="sidebar-label">Blog</span>
                        </div>
                        <span aria-hidden="true" class="submenu-indicator ml-auto w-2.5 h-2.5 border-t border-r border-gray-500 rotate-45 opacity-60 group-hover:opacity-90"></span>
                    </button>
                    <div id="blog-submenu" data-active="{{ $adminSubmenuActive['blog'] ? '1' : '0' }}" class="hidden ml-4 lg:ml-0 space-y-1 bg-gray-800 border border-gray-700 rounded-lg shadow-2xl p-2 lg:fixed lg:w-80 lg:z-[9999] lg:max-h-[70vh] lg:overflow-y-auto no-scrollbar">
                        <a href="{{ route('admin.blog.index') }}" class="flex items-center space-x-2 px-4 py-2 rounded text-sm {{ request()->routeIs('admin.blog.index', 'admin.blog.create', 'admin.blog.edit') ? 'bg-gray-700 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-gray-300' }}">
                            <span>Posts</span>
                        </a>
                        <a href="{{ route('admin.blog.categories.index') }}" class="flex items-center space-x-2 px-4 py-2 rounded text-sm {{ request()->routeIs('admin.blog.categories.*') ? 'bg-gray-700 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-gray-300' }}">
                            <span>Categories</span>
                        </a>
                        <a href="{{ route('admin.blog.tags.index') }}" class="flex items-center space-x-2 px-4 py-2 rounded text-sm {{ request()->routeIs('admin.blog.tags.*') ? 'bg-gray-700 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-gray-300' }}">
                            <span>Tags</span>
                        </a>
                    </div>
                </div>
                <div class="relative group space-y-1">
                    <button type="button" data-flyout-target="pages-submenu" aria-expanded="false" class="w-full flex items-center px-4 py-2 rounded {{ $adminSubmenuActive['pages'] ? 'bg-brand-gradient text-white' : 'text-gray-300 hover:bg-gray-700' }}">
                        <div class="flex items-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14-4H3m16 8H7m10 4H9"/>
                            </svg>
                            <span class="sidebar-label">Pages</span>
                        </div>
                        <span aria-hidden="true" class="submenu-indicator ml-auto w-2.5 h-2.5 border-t border-r border-gray-500 rotate-45 opacity-60 group-hover:opacity-90"></span>
                    </button>
                    <div id="pages-submenu" data-active="{{ $adminSubmenuActive['pages'] ? '1' : '0' }}" class="hidden ml-4 lg:ml-0 space-y-1 bg-gray-800 border border-gray-700 rounded-lg shadow-2xl p-2 lg:fixed lg:w-80 lg:z-[9999] lg:max-h-[70vh] lg:overflow-y-auto no-scrollbar">
                        <a href="{{ route('admin.pages.index') }}" class="flex items-center space-x-2 px-4 py-2 rounded text-sm {{ request()->routeIs('admin.pages.*') ? 'bg-gray-700 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-gray-300' }}">
                            <span>All Pages</span>
                        </a>
                        <a href="{{ route('admin.about.index') }}" class="flex items-center space-x-2 px-4 py-2 rounded text-sm {{ request()->routeIs('admin.about.*') ? 'bg-gray-700 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-gray-300' }}">
                            <span>About Page</span>
                        </a>
                    </div>
                </div>
                <div class="relative group space-y-1">
                    <button type="button" data-flyout-target="growth-submenu" aria-expanded="false" class="w-full flex items-center px-4 py-2 rounded {{ $adminSubmenuActive['growth'] ? 'bg-brand-gradient text-white' : 'text-gray-300 hover:bg-gray-700' }}">
                        <div class="flex items-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8L10 18l-4-4-6 6"/>
                            </svg>
                            <span class="sidebar-label">Growth</span>
                        </div>
                        <span aria-hidden="true" class="submenu-indicator ml-auto w-2.5 h-2.5 border-t border-r border-gray-500 rotate-45 opacity-60 group-hover:opacity-90"></span>
                    </button>
                    <div id="growth-submenu" data-active="{{ $adminSubmenuActive['growth'] ? '1' : '0' }}" class="hidden ml-4 lg:ml-0 space-y-1 bg-gray-800 border border-gray-700 rounded-lg shadow-2xl p-2 lg:fixed lg:w-80 lg:z-[9999] lg:max-h-[70vh] lg:overflow-y-auto no-scrollbar">
                        <a href="{{ route('admin.loyalty.index') }}" class="flex items-center space-x-2 px-4 py-2 rounded text-sm {{ request()->routeIs('admin.loyalty.*') ? 'bg-gray-700 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-gray-300' }}">
                            <span>Loyalty</span>
                        </a>
                        <a href="{{ route('admin.referrals.index') }}" class="flex items-center space-x-2 px-4 py-2 rounded text-sm {{ request()->routeIs('admin.referrals.*') ? 'bg-gray-700 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-gray-300' }}">
                            <span>Referrals</span>
                        </a>
                        <a href="{{ route('admin.coupons.index') }}" class="flex items-center space-x-2 px-4 py-2 rounded text-sm {{ request()->routeIs('admin.coupons.*') ? 'bg-gray-700 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-gray-300' }}">
                            <span>Coupons</span>
                        </a>
                        <a href="{{ route('admin.campaigns.index') }}" class="flex items-center space-x-2 px-4 py-2 rounded text-sm {{ request()->routeIs('admin.campaigns.*') ? 'bg-gray-700 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-gray-300' }}">
                            <span>Campaigns</span>
                        </a>
                        <a href="{{ route('admin.announcements.index') }}" class="flex items-center space-x-2 px-4 py-2 rounded text-sm {{ request()->routeIs('admin.announcements.*') ? 'bg-gray-700 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-gray-300' }}">
                            <span>Announcements</span>
                        </a>
                    </div>
                </div>
                <div class="relative group space-y-1">
                    <button type="button" data-flyout-target="appearance-submenu" aria-expanded="false" class="w-full flex items-center px-4 py-2 rounded {{ $adminSubmenuActive['appearance'] ? 'bg-brand-gradient text-white' : 'text-gray-300 hover:bg-gray-700' }}">
                        <div class="flex items-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3-3 3 3"/>
                            </svg>
                            <span class="sidebar-label">Appearance</span>
                        </div>
                        <span aria-hidden="true" class="submenu-indicator ml-auto w-2.5 h-2.5 border-t border-r border-gray-500 rotate-45 opacity-60 group-hover:opacity-90"></span>
                    </button>
                    <div id="appearance-submenu" data-active="{{ $adminSubmenuActive['appearance'] ? '1' : '0' }}" class="hidden ml-4 lg:ml-0 space-y-1 bg-gray-800 border border-gray-700 rounded-lg shadow-2xl p-2 lg:fixed lg:w-80 lg:z-[9999] lg:max-h-[70vh] lg:overflow-y-auto no-scrollbar">
                        <a href="{{ route('admin.appearance.theme') }}" class="flex items-center space-x-2 px-4 py-2 rounded text-sm {{ request()->routeIs('admin.appearance.theme') ? 'bg-gray-700 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-gray-300' }}">
                            <span>Theme</span>
                        </a>
                        <a href="{{ route('admin.appearance.customization') }}" class="flex items-center space-x-2 px-4 py-2 rounded text-sm {{ request()->routeIs('admin.appearance.customization', 'admin.appearance.customization.update') ? 'bg-gray-700 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-gray-300' }}">
                            <span>Customize</span>
                        </a>
                        <a href="{{ route('admin.hero-sections.index') }}" class="flex items-center space-x-2 px-4 py-2 rounded text-sm {{ request()->routeIs('admin.hero-sections.*') ? 'bg-gray-700 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-gray-300' }}">
                            <span>Hero Sections</span>
                        </a>
                        <a href="{{ route('admin.widgets.index') }}" class="flex items-center space-x-2 px-4 py-2 rounded text-sm {{ request()->routeIs('admin.widgets.*') ? 'bg-gray-700 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-gray-300' }}">
                            <span>Widgets</span>
                        </a>
                    </div>
                </div>
                <div class="relative group space-y-1">
                    <button type="button" data-flyout-target="tools-submenu" aria-expanded="false" class="w-full flex items-center px-4 py-2 rounded {{ $adminSubmenuActive['tools'] ? 'bg-brand-gradient text-white' : 'text-gray-300 hover:bg-gray-700' }}">
                        <div class="flex items-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16M4 12h16M4 17h16"/>
                            </svg>
                            <span class="sidebar-label">Tools</span>
                        </div>
                        <span aria-hidden="true" class="submenu-indicator ml-auto w-2.5 h-2.5 border-t border-r border-gray-500 rotate-45 opacity-60 group-hover:opacity-90"></span>
                    </button>
                    <div id="tools-submenu" data-active="{{ $adminSubmenuActive['tools'] ? '1' : '0' }}" class="hidden ml-4 lg:ml-0 space-y-1 bg-gray-800 border border-gray-700 rounded-lg shadow-2xl p-2 lg:fixed lg:w-80 lg:z-[9999] lg:max-h-[70vh] lg:overflow-y-auto no-scrollbar">
                        <a href="{{ route('admin.chatbot.sessions.index') }}" class="flex items-center space-x-2 px-4 py-2 rounded text-sm {{ request()->routeIs('admin.chatbot.sessions.*') ? 'bg-gray-700 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-gray-300' }}">
                            <span>Chatbot Sessions</span>
                        </a>
                        <a href="{{ route('admin.support-tickets.index') }}" class="flex items-center space-x-2 px-4 py-2 rounded text-sm {{ request()->routeIs('admin.support-tickets.*') ? 'bg-gray-700 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-gray-300' }}">
                            <span>Support Tickets</span>
                        </a>
                        <a href="{{ route('admin.ai-settings.index') }}" class="flex items-center space-x-2 px-4 py-2 rounded text-sm {{ request()->routeIs('admin.ai-settings.*') ? 'bg-gray-700 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-gray-300' }}">
                            <span>AI Settings</span>
                        </a>
                    </div>
                </div>
                <div class="relative group space-y-1">
                    <button type="button" data-flyout-target="settings-submenu" aria-expanded="false" class="w-full flex items-center px-4 py-2 rounded {{ $adminSubmenuActive['settings'] ? 'bg-brand-gradient text-white' : 'text-gray-300 hover:bg-gray-700' }}">
                        <div class="flex items-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span class="sidebar-label">Settings</span>
                        </div>
                        <span aria-hidden="true" class="submenu-indicator ml-auto w-2.5 h-2.5 border-t border-r border-gray-500 rotate-45 opacity-60 group-hover:opacity-90"></span>
                    </button>
                    <div id="settings-submenu" data-active="{{ $adminSubmenuActive['settings'] ? '1' : '0' }}" class="hidden ml-4 lg:ml-0 space-y-1 bg-gray-800 border border-gray-700 rounded-lg shadow-2xl p-2 lg:fixed lg:w-80 lg:z-[9999] lg:max-h-[70vh] lg:overflow-y-auto no-scrollbar">
                        <a href="{{ route('admin.settings.index') }}" class="flex items-center space-x-2 px-4 py-2 rounded text-sm {{ request()->routeIs('admin.settings.*') ? 'bg-gray-700 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-gray-300' }}">
                            <span>General</span>
                        </a>
                        <a href="{{ route('admin.maintenance.index') }}" class="flex items-center space-x-2 px-4 py-2 rounded text-sm {{ request()->routeIs('admin.maintenance.*') ? 'bg-gray-700 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-gray-300' }}">
                            <span>Maintenance Mode</span>
                        </a>
                        <a href="{{ route('admin.shipping-settings.index') }}" class="flex items-center space-x-2 px-4 py-2 rounded text-sm {{ request()->routeIs('admin.shipping-settings.*') ? 'bg-gray-700 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-gray-300' }}">
                            <span>Shipping</span>
                        </a>
                        <a href="{{ route('admin.users.index') }}" class="flex items-center space-x-2 px-4 py-2 rounded text-sm {{ request()->routeIs('admin.users.*') ? 'bg-gray-700 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-gray-300' }}">
                            <span>Users</span>
                        </a>
                        <a href="{{ route('admin.subscriptions.plans.index') }}" class="flex items-center space-x-2 px-4 py-2 rounded text-sm {{ request()->routeIs('admin.subscriptions.plans.*', 'admin.subscriptions.subscribers') ? 'bg-gray-700 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-gray-300' }}">
                            <span>Subscriptions</span>
                        </a>
                        <a href="{{ route('admin.settings.index') }}#privacy" class="flex items-center space-x-2 px-4 py-2 rounded text-sm {{ request()->routeIs('admin.settings.*') ? 'bg-gray-700 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-gray-300' }}">
                            <span>Data & Privacy</span>
                        </a>
                    </div>
                </div>
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
            </nav>
        </aside>

        <main class="flex-1 flex flex-col min-w-0 w-full">
            <header class="sticky top-0 z-30 bg-gray-800 border-b border-gray-700 flex-shrink-0">
                <div class="px-4 lg:px-6 py-4 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-white ml-12 lg:ml-0">@yield('header', 'Dashboard')</h2>
                    <div class="relative flex items-center space-x-4">
                        <span class="text-xs lg:text-sm text-gray-400 hidden sm:inline">{{ auth()->user()->name }}</span>
                        @php
                            $adminAvatarValue = auth()->user()->avatar;
                            $adminAvatarUrl = $adminAvatarValue
                                ? (str_starts_with($adminAvatarValue, 'http') ? $adminAvatarValue : asset('storage/' . ltrim($adminAvatarValue, '/')))
                                : null;
                        @endphp
                        <button id="admin-profile-trigger" type="button" class="flex items-center bg-gray-700 border border-gray-600 rounded-full px-2 py-1 text-sm text-white hover:border-gray-500 focus:outline-none" aria-expanded="false" aria-haspopup="menu">
                            <span class="sr-only">Open profile menu</span>
                            <span class="w-6 h-6 rounded-full bg-gray-800 overflow-hidden flex items-center justify-center">
                                @if ($adminAvatarUrl)
                                    <img src="{{ $adminAvatarUrl }}" alt="{{ auth()->user()->name }} avatar" class="w-full h-full object-cover">
                                @else
                                    <span class="font-semibold text-xs">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                                @endif
                            </span>
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div id="admin-profile-menu" class="absolute right-0 top-full z-40 w-56 rounded-lg bg-gray-800 border border-gray-700 shadow-lg text-left text-sm text-gray-200 hidden">
                            <a href="{{ route('profile.edit') }}#profile-summary" class="block px-4 py-2 hover:bg-gray-700">View Profile</a>
                            <a href="{{ route('profile.edit') }}#profile-info" class="block px-4 py-2 hover:bg-gray-700">Edit Profile</a>
                            <a href="{{ route('profile.edit') }}#profile-photo" class="block px-4 py-2 hover:bg-gray-700">Update Profile Picture</a>
                            <a href="{{ route('profile.edit') }}#password" class="block px-4 py-2 hover:bg-gray-700">Change Password</a>
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 hover:bg-gray-700">Account Settings</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 hover:bg-gray-700">Logout</button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <div class="flex-1 overflow-y-auto p-4 lg:p-6">
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
        </main>
    </div>

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

        const adminProfileTrigger = document.getElementById('admin-profile-trigger');
        const adminProfileMenu = document.getElementById('admin-profile-menu');

        const closeAdminProfileMenu = () => {
            adminProfileMenu?.classList.add('hidden');
            adminProfileTrigger?.setAttribute('aria-expanded', 'false');
        };

        adminProfileTrigger?.addEventListener('click', (event) => {
            event.stopPropagation();
            const expanded = adminProfileTrigger.getAttribute('aria-expanded') === 'true';
            adminProfileTrigger.setAttribute('aria-expanded', expanded ? 'false' : 'true');
            adminProfileMenu?.classList.toggle('hidden');
        });

        document.addEventListener('click', (event) => {
            if (!adminProfileMenu?.contains(event.target) && !adminProfileTrigger?.contains(event.target)) {
                closeAdminProfileMenu();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeAdminProfileMenu();
            }
        });



        const sidebarCollapseToggle = document.getElementById('sidebar-collapse-toggle');
        const SIDEBAR_COLLAPSED_KEY = 'admin_sidebar_collapsed';
        const LEGACY_SIDEBAR_COLLAPSED_KEY = 'sidebar_collapsed';
        const isDesktop = () => window.matchMedia('(min-width: 1024px)').matches;

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

        const hideAllSidebarSubmenus = () => {
            document.querySelectorAll('#sidebar [id$=\"-submenu\"]').forEach((panel) => {
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
            document.querySelectorAll('#sidebar [id$="-submenu"]').forEach((panel) => {
                if (keepId && panel.id === keepId) {
                    return;
                }
                panel.classList.add('hidden');
                setSubmenuExpanded(panel, false);
            });
        };

        const syncSubmenuExpandedStates = () => {
            document.querySelectorAll('#sidebar [id$="-submenu"]').forEach((panel) => {
                setSubmenuExpanded(panel, !panel.classList.contains('hidden'));
            });
        };

        const openFirstActiveSubmenuOnMobile = () => {
            if (window.innerWidth >= 1024) {
                return;
            }
            const activeSubmenu = document.querySelector('#sidebar [id$="-submenu"][data-active="1"]');
            if (!activeSubmenu) {
                return;
            }
            closeMobileSubmenus(activeSubmenu.id);
            activeSubmenu.classList.remove('hidden');
            setSubmenuExpanded(activeSubmenu, true);
        };

        const applyCollapsedState = () => {
            if (!isDesktop()) {
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
        if (isDesktop()) {
            hideAllSidebarSubmenus();
        }
        syncSubmenuExpandedStates();
        openFirstActiveSubmenuOnMobile();

        sidebarCollapseToggle?.addEventListener('click', () => {
            if (!isDesktop()) return;
            const nowCollapsed = !document.body.classList.contains('sidebar-collapsed');
            document.body.classList.toggle('sidebar-collapsed', nowCollapsed);
            localStorage.setItem(SIDEBAR_COLLAPSED_KEY, nowCollapsed ? '1' : '0');
            updateCollapseButton(nowCollapsed);
            hideAllSidebarSubmenus();
        });

        window.addEventListener('resize', () => {
            applyCollapsedState();
            if (isDesktop()) {
                hideAllSidebarSubmenus();
            }
            syncSubmenuExpandedStates();
            openFirstActiveSubmenuOnMobile();
        });

        function toggleSubmenu(button) {
            if (!button) return;
            const submenuId = button.getAttribute('data-flyout-target');
            const submenu = submenuId ? document.getElementById(submenuId) : null;
            if (!submenu) return;

            if (window.innerWidth >= 1024) {
                submenu.classList.toggle('hidden');
                if (!submenu.classList.contains('hidden')) {
                    positionFlyout(button, submenu);
                }
            } else {
                closeMobileSubmenus(submenu.id);
                submenu.classList.toggle('hidden');
            }
            setSubmenuExpanded(submenu, !submenu.classList.contains('hidden'));
        }

        // Shared flyout positioning function for desktop submenus
        function positionFlyout(button, submenu) {
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
                    toggleSubmenu(button);
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
        })();
    </script>
</body>
</html>
