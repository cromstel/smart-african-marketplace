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
            <!-- Fonts -->
            <link rel="preconnect" href="https://fonts.bunny.net">
            <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
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
            .app-page-container {
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
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white dark:bg-gray-800 shadow">
                    <div class="app-page-container py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>

            @include('components.scroll-to-top')
            @include('components.site-footer')
        </div>
    </body>
</html>

