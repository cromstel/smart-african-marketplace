<div x-data="{ open: false }" 
     @open-register-modal.window="open = true"
     @keydown.escape.window="open = false"
     class="relative z-50">
    
    <div x-show="open" 
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
         style="display: none;">
    </div>

    <div x-show="open"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         class="fixed inset-0 z-10 overflow-y-auto"
         style="display: none;">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-[35.2rem]">
                <div class="bg-white px-6 py-8">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-3xl font-bold text-gray-900">Create Account</h3>
                            <p class="mt-1 text-sm text-gray-600">Join {{ config('app.name') }} today</p>
                        </div>
                        <button @click="open = false" class="text-gray-400 hover:text-gray-500">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('register') }}" class="space-y-4">
                        @csrf
                        @include('auth.partials.honeypot')

                        <div>
                            <label for="name" class="sr-only">Full Name</label>
                            <div class="relative">
                                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                                    class="block w-full px-4 py-3 pr-10 border border-gray-300 rounded-lg bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                    placeholder="John Doe">
                            </div>
                            <x-input-error :messages="$errors->get('name')" class="mt-1" />
                        </div>

                        <div>
                            <label for="modal-email" class="sr-only">Email Address</label>
                            <div class="relative">
                                <input id="modal-email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                                    class="block w-full px-4 py-3 pr-10 border border-gray-300 rounded-lg bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                    placeholder="you@example.com">
                            </div>
                            <x-input-error :messages="$errors->get('email')" class="mt-1" />
                        </div>

                        <div>
                            <label for="modal-password" class="sr-only">Password</label>
                            <div class="relative">
                                <input id="modal-password" type="password" name="password" required autocomplete="new-password"
                                    class="block w-full px-4 py-3 pr-10 border border-gray-300 rounded-lg bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                    placeholder="Minimum 8 characters">
                                <button type="button" data-toggle-password="modal-password" aria-label="Show password" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-orange-500 rounded z-20">
                                    <svg data-password-eye class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <svg data-password-eye-off class="h-5 w-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.96 9.96 0 012.042-3.368M9.88 9.88A3 3 0 0114.12 14.12M3 3l18 18"/>
                                    </svg>
                                </button>
                            </div>
                            <div data-password-strength-for="modal-password" data-password-confirm-for="modal-password-confirmation" class="mt-3 space-y-1">
                                <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                                    <div data-password-strength-fill class="h-full w-0 rounded-full bg-gradient-to-r from-orange-500 via-yellow-500 to-rose-500 transition-all duration-200"></div>
                                </div>
                                <p class="text-xs font-semibold text-gray-500" data-password-strength-label>Very Weak</p>
                            </div>
                            <x-password-requirements for="modal-password" />
                            <x-input-error :messages="$errors->get('password')" class="mt-1" />
                        </div>

                        <div>
                            <label for="modal-password-confirmation" class="sr-only">Confirm Password</label>
                            <div class="relative">
                                <input id="modal-password-confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                                    class="block w-full px-4 py-3 pr-10 border border-gray-300 rounded-lg bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                    placeholder="Confirm your password">
                                <button type="button" data-toggle-password="modal-password-confirmation" aria-label="Show password" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-orange-500 rounded z-20">
                                    <svg data-password-eye class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <svg data-password-eye-off class="h-5 w-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.96 9.96 0 012.042-3.368M9.88 9.88A3 3 0 0114.12 14.12M3 3l18 18"/>
                                    </svg>
                                </button>
                            </div>
                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
                        </div>

                        <div class="space-y-4">
                            <div class="flex items-start">
                                <input id="terms" type="checkbox" required class="w-4 h-4 mt-1 text-orange-600 bg-gray-100 border-gray-300 rounded focus:ring-orange-500">
                                <label for="terms" class="ml-2 text-sm text-gray-600">
                                    I agree to the <a href="/terms" class="text-orange-600 hover:text-orange-500 font-medium">Terms</a> and <a href="/privacy" class="text-orange-600 hover:text-orange-500 font-medium">Privacy Policy</a>
                                </label>
                            </div>

                            <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-base font-medium text-white bg-gradient-to-r from-orange-600 to-red-600 hover:from-orange-700 hover:to-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-200">
                                Create Account
                            </button>
                        </div>
                    </form>


                    <div class="mt-4 text-center">
                        <p class="text-sm text-gray-600">
                            Already have an account?
                            <button @click="open = false" class="font-medium text-orange-600 hover:text-orange-500">
                                Sign in
                            </button>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

