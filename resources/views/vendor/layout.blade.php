<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Vendor Dashboard') - {{ config('app.name', 'African Market') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-900 text-gray-100">
    <div class="min-h-screen flex">
        <aside class="w-64 bg-gray-800 border-r border-gray-700 flex-shrink-0">
            <div class="p-6 border-b border-gray-700">
                <h1 class="text-2xl font-bold text-white">African Market</h1>
                <p class="text-sm text-gray-400">Vendor Portal</p>
            </div>
            <nav class="p-4 space-y-1">
                <a href="{{ route('vendor.dashboard') }}" class="block px-4 py-2 rounded {{ request()->routeIs('vendor.dashboard') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700' }}">
                    Dashboard
                </a>
                <a href="{{ route('vendor.products.index') }}" class="block px-4 py-2 rounded {{ request()->routeIs('vendor.products.*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700' }}">
                    My Products
                </a>
                <hr class="my-4 border-gray-700">
                <a href="{{ route('dashboard') }}" class="block px-4 py-2 rounded text-gray-300 hover:bg-gray-700">
                    User Dashboard
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left px-4 py-2 rounded text-gray-300 hover:bg-gray-700">
                        Logout
                    </button>
                </form>
            </nav>
        </aside>

        <main class="flex-1 overflow-x-hidden overflow-y-auto">
            <header class="bg-gray-800 border-b border-gray-700">
                <div class="px-6 py-4 flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-white">@yield('header', 'Dashboard')</h2>
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-400">{{ auth()->user()->name }}</span>
                    </div>
                </div>
            </header>

            <div class="p-6">
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
</body>
</html>
