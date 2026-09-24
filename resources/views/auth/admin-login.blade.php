<x-guest-layout>
    <style>
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes pulse-glow {
            0%, 100% {
                box-shadow: 0 0 20px rgba(234, 88, 12, 0.4);
            }
            50% {
                box-shadow: 0 0 30px rgba(234, 88, 12, 0.6);
            }
        }
        
        @keyframes gradient-shift {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }
        
        .animate-pulse-glow {
            animation: pulse-glow 2s ease-in-out infinite;
        }
        
        .animate-gradient {
            background-size: 100% 100%;
            animation: gradient-shift 8s ease infinite;
        }
    </style>

    <div class="min-h-screen flex flex-col lg:flex-row">
        <div class="w-full lg:w-[30%] flex items-center justify-center px-4 sm:px-6 lg:px-12 xl:px-16 bg-white">
            <div class="max-w-md w-full space-y-8">
                <div>
                    <x-auth.brand-header size="lg" />
                    <h2 class="mt-6 text-4xl font-bold text-gray-900">Super Admin Portal</h2>
                    <p class="mt-2 text-sm text-gray-600">Sign in to access the site super admin dashboard</p>
                </div>

                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('admin.login') }}" class="mt-8 space-y-6">
                    @csrf
                    @include('auth.partials.honeypot')

                    <div>
                        <label for="email" class="sr-only">Super Admin Email</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                                </svg>
                            </div>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                                class="block w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-600 focus:border-transparent transition"
                                placeholder="e-mail@domain.com">
                        </div>
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div>
                        <label for="password" class="sr-only">Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <input id="password" type="password" name="password" required autocomplete="current-password"
                                class="block w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-600 focus:border-transparent transition"
                                placeholder="Enter your admin password">
                            <button type="button" data-toggle-password="password" aria-label="Show password" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-orange-600 rounded z-20">
                                <svg data-password-eye class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg data-password-eye-off class="h-5 w-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.96 9.96 0 012.042-3.368M9.88 9.88A3 3 0 0114.12 14.12M3 3l18 18"/>
                                </svg>
                            </button>
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-between">
                        <label for="remember_me" class="inline-flex items-center cursor-pointer">
                            <input id="remember_me" type="checkbox" name="remember" class="w-4 h-4 text-orange-600 bg-gray-100 border-gray-300 rounded focus:ring-orange-600 focus:ring-2">
                            <span class="ml-2 text-sm text-gray-600">Remember me</span>
                        </label>

                    </div>

                    <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-base font-medium text-white bg-gradient-to-r from-orange-600 to-red-600 hover:from-orange-700 hover:to-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-600 transition-all duration-200">
                        Sign In to Site Admin Portal
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-600">
                        Not a site admin?
                        <a href="{{ route('login') }}" class="font-medium text-orange-600 hover:text-orange-500">
                            Regular Login
                        </a>
                    </p>
                </div>

            </div>
        </div>

        <div class="hidden lg:flex relative w-[70%] min-h-screen">
            <img src="/images/auth-bg.avif" alt="" class="absolute inset-0 h-full w-full object-cover" />
            <div class="absolute inset-0 bg-gradient-to-br from-orange-600/35 via-red-600/25 to-black/35"></div>
            <div class="relative z-20 flex flex-col h-full px-12 text-white">
                <div class="flex-1 flex flex-col justify-center">
                    <div class="max-w-xl">
                        <h2 class="text-5xl font-bold leading-tight mb-6">
                            Super Admin<br/>Dashboard
                        </h2>
                        <p class="text-xl text-white/90 mb-8">
                            Manage the entire marketplace from one powerful dashboard. Monitor sales, vendors, products, and customer activity.
                        </p>
                        
                        <div class="space-y-3 text-white/90">
                            <div class="flex items-center space-x-3">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span>Full Platform Control</span>
                            </div>
                            <div class="flex items-center space-x-3">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span>Real-time Analytics</span>
                            </div>
                            <div class="flex items-center space-x-3">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span>Vendor Management</span>
                            </div>
                            <div class="flex items-center space-x-3">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span>Content Management</span>
                            </div>
                        </div>
                    </div>
                </div>
                <x-auth-footer />
            </div>
        </div>
    </div>
</x-guest-layout>

