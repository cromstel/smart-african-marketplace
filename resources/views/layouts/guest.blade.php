<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
    @php
        try {
            $containerWidth = (int) \App\Models\Setting::getValue('theme_container_width', 1600);
        } catch (\Throwable $e) {
            $containerWidth = 1600;
        }
    @endphp
        <x-seo 
            :title="$seoTitle ?? null"
            :description="$seoDescription ?? null"
            :keywords="$seoKeywords ?? null"
            :image="$seoImage ?? null"
            :canonical="$seoCanonical ?? null"
            :type="$seoType ?? 'website'"
        >
            <link rel="preconnect" href="https://fonts.bunny.net">
            <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
        </x-seo>
        <style>
            :root {
                --theme-container-width: {{ max(1024, min(1600, $containerWidth)) }}px;
            }
            html, body {
                max-width: 100%;
                overflow-x: hidden;
            }
            *, *::before, *::after {
                box-sizing: border-box;
                min-width: 0;
            }
            img, video, canvas, svg {
                max-width: 100%;
                height: auto;
            }
            input, textarea, select, button {
                max-width: 100%;
            }
            .site-container {
                max-width: var(--theme-container-width);
                margin-left: auto;
                margin-right: auto;
                width: 100%;
            }
            .container {
                max-width: var(--theme-container-width) !important;
            }
        </style>
    </head>
    <body class="font-sans antialiased">
        {{ $slot }}
        @include('components.scroll-to-top')
        @include('components.password-toggle-script')
    </body>
</html>
