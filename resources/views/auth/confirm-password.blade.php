@php($hideBreadcrumbs = true)
@php($suppressHeaderWidgets = true)
@php($hideFooter = true)
@extends('layouts.shop')

@section('content')
<x-auth-overlay-shell panelClass="max-w-md lg:max-w-lg">
                <div>
                    <x-auth.brand-header />
                    <h2 class="mt-6 text-4xl font-bold text-gray-900">Confirm Password</h2>
                    <p class="mt-2 text-sm text-gray-600">
                        This is a secure action. Please confirm your password to continue.
                    </p>
                </div>

                <div class="text-sm text-gray-600 animate-fade-in-up">
                    {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
                </div>

                <form method="POST" action="{{ route('password.confirm') }}" class="mt-6 space-y-6">
                    @csrf
                    @include('auth.partials.honeypot')

                    <div>
                        <label for="password" class="sr-only">{{ __('Password') }}</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <input id="password" type="password" name="password" required autocomplete="current-password"
                                   class="block w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-600 focus:border-transparent transition"
                                   placeholder="Enter your current password" />
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

                    <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-base font-medium text-white bg-gradient-to-r from-orange-600 to-red-600 hover:from-orange-700 hover:to-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-600 transition-all duration-200">
                        {{ __('Confirm') }}
                    </button>
                </form>
            </x-auth-overlay-shell>
@endsection





