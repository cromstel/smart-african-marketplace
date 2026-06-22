@props([
    'panelClass' => 'max-w-md lg:max-w-lg',
    'wrapperClass' => 'px-4 sm:px-6 lg:px-12 xl:px-16 py-8',
])

<div class="relative h-[100vh] min-h-[100vh] overflow-hidden">
    <img src="/images/auth-bg.avif" alt="" class="absolute inset-0 h-full w-full object-cover" />
    <div class="absolute inset-0 bg-black/60"></div>

    <div class="relative z-10 h-full min-h-[100vh] flex items-center justify-center {{ $wrapperClass }}">
        <div class="w-full {{ $panelClass }} rounded-2xl border border-white/40 bg-white shadow-2xl backdrop-blur-sm p-6 sm:p-8 lg:p-10">
            {{ $slot }}
        </div>
    </div>
    @include('auth.partials.forgot-password-modal')
</div>

@once
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('modal') === 'forgot-password') {
                // Remove the query parameter immediately so refresh/navigation back won't reopen modal
                history.replaceState(null, '', window.location.pathname);
                setTimeout(() => {
                    window.dispatchEvent(new CustomEvent('open-forgot-modal'));
                }, 100);
            }
        });
    </script>
    @endpush
@endonce

