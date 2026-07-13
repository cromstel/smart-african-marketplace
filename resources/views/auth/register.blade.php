@php($hideBreadcrumbs = true)
@php($suppressHeaderWidgets = true)
@php($hideFooter = true)
@extends('layouts.shop')

@section('content')
<x-auth-overlay-shell panelClass="w-full sm:w-1/2 lg:w-1/2 lg:max-w-[500px] lg:ml-[25%]" wrapperClass="px-4 sm:px-6 lg:px-12 xl:px-16 py-8">
                <div>
                    <x-auth.brand-header />
                    <h2 class="mt-6 text-[1.5rem] font-semibold text-gray-900">Create Your User Account</h2>
                    <p class="mt-2 text-sm text-gray-600">Join Smart African Marketplace and start your journey</p>
                </div>

                <form method="POST" action="{{ route('register') }}" class="mt-8 space-y-5">
                    @csrf
                    @include('auth.partials.honeypot')

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="firstname" class="sr-only">First Name</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                                <input id="firstname" type="text" name="firstname" value="{{ old('firstname') }}" required autofocus autocomplete="given-name"
                                    class="block w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition"
                                    placeholder="First name">
                                <x-form-hint text="Enter your legal or preferred first name." />
                            </div>
                            <x-input-error :messages="$errors->get('firstname')" class="mt-2" />
                        </div>

                        <div>
                            <label for="lastname" class="sr-only">Last Name</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                                <input id="lastname" type="text" name="lastname" value="{{ old('lastname') }}" required autocomplete="family-name"
                                    class="block w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition"
                                    placeholder="Last name">
                                <x-form-hint text="Enter your family name or surname." />
                            </div>
                            <x-input-error :messages="$errors->get('lastname')" class="mt-2" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="email" class="sr-only">Email Address</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                                    </svg>
                                </div>
                                <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                                    class="block w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition"
                                    placeholder="e-mail@domain.com">
                                <x-form-hint text="Use an email you can access for verification and password recovery." />
                            </div>
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <div>
                            <label for="email_confirmation" class="sr-only">Confirm Email Address</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                                    </svg>
                                </div>
                                <input id="email_confirmation" type="email" name="email_confirmation" value="{{ old('email_confirmation') }}" required autocomplete="email"
                                    class="block w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition"
                                    placeholder="Confirm your email">
                                <x-form-hint text="Repeat the same email to avoid typos." />
                            </div>
                            <x-input-error :messages="$errors->get('email_confirmation')" class="mt-2" />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="password" class="sr-only">Password</label>
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
                            <x-form-hint text="Use at least 8 characters with a mix of letters, numbers, and symbols." />
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <div>
                            <label for="password_confirmation" class="sr-only">Confirm Password</label>
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
                            </div>
                            <x-form-hint text="Re-enter the exact same password to confirm." />
                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pt-5">
                        @include('auth.partials.vendor-register-modal')
                    </div>

                    {{-- Hidden vendor fields for form submission (populated from modal via JavaScript) --}}
                    <div id="vendor-fields-hidden" class="hidden">
                        <input type="hidden" name="business_name" id="hidden-business_name" value="{{ old('business_name') }}">
                        <input type="hidden" name="vendor_category_id" id="hidden-vendor_category_id" value="{{ old('vendor_category_id') }}">
                        <input type="hidden" name="business_email" id="hidden-business_email" value="{{ old('business_email') }}">
                        <input type="hidden" name="business_phone" id="hidden-business_phone" value="{{ old('business_phone') }}">
                        <input type="hidden" name="business_address" id="hidden-business_address" value="{{ old('business_address') }}">
                    </div>

                    <div class="flex items-start">
                        <input id="terms" type="checkbox" required class="w-4 h-4 mt-1 text-orange-600 bg-gray-100 border-gray-300 rounded focus:ring-orange-500 focus:ring-2">
                        <label for="terms" class="ml-2 text-sm text-gray-600">
                            I agree to the <a href="{{ route('terms') }}" class="text-orange-600 hover:text-orange-500">Terms of Service</a> and <a href="{{ route('privacy') }}" class="text-orange-600 hover:text-orange-500">Privacy Policy</a>
                        </label>
                    </div>

                    <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-base font-medium text-white bg-gradient-to-r from-orange-600 to-red-600 hover:from-orange-700 hover:to-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-200">
                        Create Account
                    </button>
                </form>


                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-600">
                        Already have an account?
                        <a href="{{ route('login') }}" class="font-medium text-orange-600 hover:text-orange-500">
                            Sign in
                        </a>
                    </p>
                </div>

            </x-auth-overlay-shell>
@endsection

@push('scripts')
<script>
    // Sync modal fields to hidden form fields on input
    document.addEventListener('DOMContentLoaded', function() {
        const modalFields = ['business_name', 'vendor_category_id', 'business_email', 'business_phone', 'business_address'];
        
        modalFields.forEach(function(field) {
            const modalInput = document.getElementById('modal-' + field);
            const hiddenInput = document.getElementById('hidden-' + field);
            
            if (modalInput && hiddenInput) {
                modalInput.addEventListener('input', function() {
                    hiddenInput.value = this.value;
                });
                
                // Also sync on change for select elements
                modalInput.addEventListener('change', function() {
                    hiddenInput.value = this.value;
                });
            }
        });
        
        // Handle vendor_terms checkbox - sync to hidden field if needed
        const vendorTerms = document.getElementById('vendor_terms');
        if (vendorTerms) {
            vendorTerms.addEventListener('change', function() {
                // Vendor terms is handled separately, not synced to hidden field
            });
        }
    });
</script>
@endpush





