<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <x-seo 
        :title="trim($__env->yieldContent('title', 'Customer Console')).' - '.config('app.name', 'Smart African Market')"
        :description="$seoDescription ?? 'Customer console for managing orders, bookings, wishlist, and profile.'"
        :keywords="$seoKeywords ?? 'customer console, account management, orders, bookings'"
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
</head>
<body class="font-sans antialiased bg-gray-900 text-gray-100">
    <button id="sidebar-toggle" class="lg:hidden fixed top-4 left-4 z-50 p-2 bg-gray-800 text-white rounded-lg" aria-label="Toggle sidebar">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
    </button>

    <div class="min-h-screen flex flex-col lg:flex-row">
        <aside id="sidebar" class="fixed lg:sticky lg:top-0 lg:h-screen lg:overflow-hidden inset-y-0 left-0 z-40 w-72 bg-gray-800 border-r border-gray-700 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out flex-shrink-0 flex flex-col">
            <div class="px-4 py-6 border-b border-gray-700 flex-shrink-0">
                <div class="flex items-center space-x-2">
                    <a href="{{ route('home') }}" class="flex items-center space-x-2 min-w-0" aria-label="{{ config('app.name', 'Smart African Market') }} - Go to homepage">
                        <img src="{{ asset(config('branding.logo')) }}" alt="{{ config('app.name', 'Smart African Market') }}" class="h-8 w-8">
                        <div class="sidebar-brand-text min-w-0">
                            <h2 class="text-sm font-bold uppercase tracking-wide leading-tight text-white">Smart <span class="text-indigo-400">African</span></h2>
                            <p class="text-xs text-gray-400 tracking-wide">CUSTOMER CONSOLE</p>
                        </div>
                    </a>
                    <button id="sidebar-collapse-toggle" type="button" class="hidden lg:inline-flex ml-auto items-center justify-center w-9 h-9 rounded-lg border border-gray-700 bg-gray-800 text-gray-200 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-600" aria-label="Toggle sidebar" title="Collapse sidebar">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l-7 7 7 7M15 5h2m-2 14h2"/>
                        </svg>
                    </button>
                </div>
            </div>
            <nav class="p-4 space-y-1 overflow-x-visible no-scrollbar flex-1 overflow-y-auto min-h-0 flex flex-col pb-0">
                <a href="{{ route('customer.dashboard') }}" class="flex items-center space-x-2 px-4 py-2 rounded {{ request()->routeIs('customer.dashboard') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span class="sidebar-label">Dashboard</span>
                </a>
                <a href="{{ route('customer.orders.index') }}" class="flex items-center space-x-2 px-4 py-2 rounded {{ request()->routeIs('customer.orders.*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    <span class="sidebar-label">Orders</span>
                </a>
                <a href="{{ route('customer.bookings.index') }}" class="flex items-center space-x-2 px-4 py-2 rounded {{ request()->routeIs('customer.bookings.*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10m-12 9h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <span class="sidebar-label">Bookings</span>
                </a>
                <a href="{{ route('customer.wishlist.index') }}" class="flex items-center space-x-2 px-4 py-2 rounded {{ request()->routeIs('customer.wishlist.*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                    </svg>
                    <span class="sidebar-label">Wishlist</span>
                </a>
                <a href="{{ route('customer.profile.edit') }}" class="flex items-center space-x-2 px-4 py-2 rounded {{ request()->routeIs('customer.profile.*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span class="sidebar-label">Profile</span>
                </a>
                <hr class="my-4 border-gray-700">
                <a href="{{ route('home') }}" class="flex items-center space-x-2 px-4 py-2 rounded text-gray-300 hover:bg-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span class="sidebar-label">Visit Site</span>
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
                <button type="button" class="border-t border-gray-700 mt-auto py-1 px-4 w-full text-left text-xs text-gray-400 sticky bottom-0 bg-gray-800 hover:bg-gray-700">
                    <span class="sidebar-label">Version {{ config('app.version') }}</span>
                </button>
            </nav>
        </aside>

        <main class="flex-1 flex flex-col min-w-0 w-full">
            <header class="sticky top-0 z-30 bg-gray-800 border-b border-gray-700 flex-shrink-0">
                <div class="px-4 lg:px-6 py-4 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-white ml-12 lg:ml-0">@yield('header', 'Dashboard')</h2>
                    <div class="relative flex items-center space-x-4">
                        <span class="text-xs lg:text-sm text-gray-400 hidden sm:inline">{{ auth()->user()->name }}</span>
                        <button id="customer-profile-trigger" type="button" class="flex items-center bg-gray-700 border border-gray-600 rounded-full px-2 py-1 text-sm text-white hover:border-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-400" aria-expanded="false" aria-haspopup="menu">
                            <span class="sr-only">Open profile menu</span>
                            <span class="w-6 h-6 rounded-full bg-gray-800 overflow-hidden flex items-center justify-center">
                                <span class="font-semibold text-xs">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                            </span>
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div id="customer-profile-menu" class="absolute right-0 top-full z-40 w-56 rounded-lg bg-gray-800 border border-gray-700 shadow-lg text-left text-sm text-gray-200 hidden">
                            <a href="{{ route('customer.profile.edit') }}" class="block px-4 py-2 hover:bg-gray-700">Profile</a>
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

        const customerProfileTrigger = document.getElementById('customer-profile-trigger');
        const customerProfileMenu = document.getElementById('customer-profile-menu');

        const closeCustomerProfileMenu = () => {
            customerProfileMenu?.classList.add('hidden');
            customerProfileTrigger?.setAttribute('aria-expanded', 'false');
        };

        customerProfileTrigger?.addEventListener('click', (event) => {
            event.stopPropagation();
            const expanded = customerProfileTrigger.getAttribute('aria-expanded') === 'true';
            customerProfileTrigger.setAttribute('aria-expanded', expanded ? 'false' : 'true');
            customerProfileMenu?.classList.toggle('hidden');
        });

        document.addEventListener('click', (event) => {
            if (!customerProfileMenu?.contains(event.target) && !customerProfileTrigger?.contains(event.target)) {
                closeCustomerProfileMenu();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeCustomerProfileMenu();
            }
        });
    </script>
</body>
</html>
