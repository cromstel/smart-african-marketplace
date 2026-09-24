@php
    $modalErrors = $errors->getBag('default');
    $oldEmail = old('email');
@endphp

<div
    x-data="{ open: false }"
    x-init="$watch('open', value => document.body.classList[value ? 'add' : 'remove']('overflow-hidden'))"
    @open-forgot-modal.window="open = true"
    x-cloak
>
    <div
        x-show="open"
        x-transition.opacity
        class="fixed inset-0 bg-black/40 backdrop-blur-sm z-40"
        aria-hidden="true"
        @click="open = false"
    ></div>

    <div
        x-show="open"
        x-transition
        class="fixed inset-0 flex items-center justify-center z-50 px-4 py-8"
        role="dialog"
        aria-modal="true"
    >
        <div class="w-full max-w-md rounded-2xl bg-white py-8 px-6 shadow-2xl border border-gray-200 text-left">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Reset Your Password</h3>
                    <p class="text-sm text-gray-500">Enter your email address and we’ll send a reset link.</p>
                </div>
                <button type="button" class="text-gray-500 hover:text-gray-700" @click.prevent.stop="open = false" @keydown.escape.window="open = false" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
                @csrf
                @include('auth.partials.honeypot')

                <div>
                    <label for="modal-email" class="sr-only">Email</label>
                    <input
                        id="modal-email"
                        type="email"
                        name="email"
                        value="{{ $oldEmail }}"
                        required
                        autocomplete="email"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-orange-600 focus:border-transparent"
                        placeholder="you@example.com"
                    >
                    @if ($modalErrors->has('email'))
                        <p class="mt-2 text-xs text-red-600">{{ $modalErrors->first('email') }}</p>
                    @endif
                </div>

                <button
                    type="submit"
                    class="w-full rounded-lg bg-gradient-to-r from-orange-600 to-red-600 text-white font-semibold py-3 text-sm shadow-sm hover:from-orange-700 hover:to-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-600"
                >
                    Email reset link
                </button>
            </form>
        </div>
    </div>
</div>

