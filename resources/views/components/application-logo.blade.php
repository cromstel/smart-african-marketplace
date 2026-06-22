<a href="{{ route('home') }}" class="logo-link" aria-label="{{ config('app.name', 'Smart African Marketplace') }} - Go to homepage">
    <img src="{{ asset(config('branding.logo')) }}"
         alt="{{ config('app.name', 'Smart African Marketplace') }} logo"
         {{ $attributes }}>
</a>
