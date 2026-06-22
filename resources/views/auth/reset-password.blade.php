@php($hideBreadcrumbs = true)
@php($suppressHeaderWidgets = true)
@php($hideFooter = true)
@extends('layouts.shop')

@section('content')
<x-auth-overlay-shell panelClass="max-w-md lg:max-w-lg">
                <div>
                    <a href="/" class="inline-flex items-center space-x-3 mb-8 group">
                        <a href="{{ route('home') }}" class="relative" aria-label="{{ config('app.name', 'African Market') }} - Go to homepage">
                            <img src="{{ asset(config('branding.logo')) }}" alt="{{ config('app.name', 'African Market') }}" class="h-16 w-16 rounded-full p-2 bg-gradient-to-br from-orange-500 to-red-600 shadow-lg animate-pulse-glow transition-transform duration-300 group-hover:scale-110 group-hover:rotate-6">
                        </a>
                        <span class="text-sm sm:text-base lg:text-lg font-extrabold uppercase tracking-wide bg-gradient-to-r from-orange-600 via-red-600 to-orange-600 bg-clip-text text-transparent animate-gradient">Smart African Marketplace</span>
                    </a>
                    <h2 class="mt-6 text-4xl font-bold text-gray-900">Reset Your Password</h2>
                    <p class="mt-2 text-sm text-gray-600">Enter your new password below</p>
                </div>

                <form method="POST" action="{{ route('password.store') }}" class="mt-8 space-y-6">
                    @csrf

                    <input type="hidden" name="token" value="{{ $request->route()->parameter('token') }}">

                    <div>
                        <label for="email" class="sr-only">Email Address</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                                </svg>
                            </div>
                            <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username"
                                   class="block w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition"
                                   placeholder="you@example.com">
                            <x-form-hint text="Use the same email where you received your reset link." />
                        </div>
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div>
                        <label for="password" class="sr-only">New Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <input id="password" type="password" name="password" required autocomplete="new-password"
                                   class="block w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition"
                                   placeholder="Minimum 8 characters">
                            <button type="button" data-toggle-password="password" aria-label="Show password" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                                <svg data-password-eye class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg data-password-eye-off class="h-5 w-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.96 9.96 0 012.042-3.368M9.88 9.88A3 3 0 0114.12 14.12M3 3l18 18"/>
                                </svg>
                            </button>
                        </div>
                        <div data-password-strength-for="password" class="mt-3 space-y-1">
                            <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                                <div data-password-strength-fill class="h-full w-0 rounded-full bg-gradient-to-r from-orange-500 via-yellow-500 to-rose-500 transition-all duration-200"></div>
                            </div>
                            <p class="text-xs font-semibold text-gray-500" data-password-strength-label>Very Weak</p>
                        </div>
                        <x-form-hint text="Create a new strong password with at least 8 characters." />
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div>
                        <label for="password_confirmation" class="sr-only">Confirm New Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                                   class="block w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition"
                                   placeholder="Confirm your password">
                            <button type="button" data-toggle-password="password_confirmation" aria-label="Show password" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                                <svg data-password-eye class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg data-password-eye-off class="h-5 w-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.96 9.96 0 012.042-3.368M9.88 9.88A3 3 0 0114.12 14.12M3 3l18 18"/>
                                </svg>
                            </button>
                            <x-form-hint text="Repeat the exact same password to avoid mismatch errors." />
                        </div>
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                    </div>

                    <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-base font-medium text-white bg-gradient-to-r from-orange-600 to-red-600 hover:from-orange-700 hover:to-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-200">
                        Reset Password
                    </button>
                </form>

            </x-auth-overlay-shell>
@endsection





