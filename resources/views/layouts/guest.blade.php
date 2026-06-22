<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
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

            @vite(['resources/css/app.css', 'resources/js/app.js'])
            <script>
                window.deferLoadingAlpine = true;
            </script>
            <script defer src="{{ asset('js/alpine.min.js') }}"></script>

            <script>
                (() => {
                    const startAlpine = () => {
                        if (!window.Alpine || window.Alpine.__deferredStarted) {
                            return;
                        }
                        window.Alpine.start();
                        window.Alpine.__deferredStarted = true;
                    };

                    const scheduleStart = () => {
                        if (window.Livewire && window.Alpine) {
                            startAlpine();
                        }
                    };

                    document.addEventListener('livewire:load', () => {
                        startAlpine();
                    });

                    document.addEventListener('DOMContentLoaded', scheduleStart);

                    if (window.Livewire && window.Alpine) {
                        startAlpine();
                    }
                })();
            </script>
        </x-seo>
        <style>
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
        </style>
    </head>
    <body class="font-sans antialiased">
        {{ $slot }}
        @include('components.scroll-to-top')
        @include('components.password-toggle-script')
    </body>
</html>

