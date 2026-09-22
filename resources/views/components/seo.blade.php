<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $title }}</title>

@if($description)
<meta name="description" content="{{ $description }}">
@endif

@if($keywords)
<meta name="keywords" content="{{ $keywords }}">
@endif

@if(isset($author) && $author)
<meta name="author" content="{{ $author }}">
@endif

@if($canonical)
<link rel="canonical" href="{{ $canonical }}">
@endif

<meta name="robots" content="{{ $index ? 'index' : 'noindex' }}, {{ $follow ? 'follow' : 'nofollow' }}, max-image-preview:large, max-snippet:-1, max-video-preview:-1">

<!-- DNS Prefetch -->
<link rel="dns-prefetch" href="https://fonts.bunny.net">

<!-- Open Graph / Facebook -->
<meta property="og:type" content="{{ $type }}">
<meta property="og:title" content="{{ $title }}">
@if($description)
<meta property="og:description" content="{{ $description }}">
@endif
@if($image)
<meta property="og:image" content="{{ $image }}">
@endif
@if($canonical)
<meta property="og:url" content="{{ $canonical }}">
@endif
<meta property="og:site_name" content="{{ config('app.name', 'Smart African Market') }}">

<!-- Twitter -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $title }}">
@if($description)
<meta name="twitter:description" content="{{ $description }}">
@endif
@if($image)
<meta name="twitter:image" content="{{ $image }}">
@endif

<!-- Favicon -->
<link rel="icon" type="image/svg+xml" href="{{ asset(config('branding.favicon_svg')) }}">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset(config('branding.favicon_32')) }}">
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset(config('branding.favicon_16')) }}">
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset(config('branding.apple_touch_icon')) }}">
<link rel="manifest" href="{{ asset(config('branding.webmanifest')) }}">

<!-- Google Analytics -->
@if(config('services.google_analytics.id'))
<script async src="https://www.googletagmanager.com/gtag/js?id={{ config('services.google_analytics.id') }}"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', '{{ config('services.google_analytics.id') }}');
</script>
@endif

<!-- Facebook Pixel -->
@if(config('services.facebook_pixel.id'))
<script>
    !function(f,b,e,v,n,t,s)
    {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};
    if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
    n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];
    s.parentNode.insertBefore(t,s)}(window, document,'script',
    'https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', '{{ config('services.facebook_pixel.id') }}');
    fbq('track', 'PageView');
</script>
<noscript>
    <img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id={{ config('services.facebook_pixel.id') }}&ev=PageView&noscript=1" />
</noscript>
@endif

<!-- Vite -->
@vite(['resources/css/app.css', 'resources/js/app.js'])

{{ $slot }}

<!-- Additional head stack -->
@stack('head')

