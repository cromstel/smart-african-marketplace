@props([
    'pageIdentifier',
    'fallbackTitle' => 'Welcome',
    'fallbackSubtitle' => '',
    'fallbackStats' => [],
    'fallbackImage' => null,
    'animated' => false,
    'customHeight' => null,
    'customHeightValue' => null,
    'heightPreset' => null,
    'headingTag' => 'h2',
    'backgroundImageUrl' => null,
    'titleSizeClasses' => null,
])

@php
    $hero = \App\Models\HeroSection::getByPage($pageIdentifier);
    $isHomeHero = $pageIdentifier === 'home';
    $headingTag = strtolower((string) $headingTag);
    if (! in_array($headingTag, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'], true)) {
        $headingTag = 'h2';
    }
    $defaultTitleSizeClasses = $isHomeHero
        ? ($headingTag === 'h2' ? 'text-[clamp(1.5rem,5.8vw,3rem)]' : 'text-[clamp(2rem,5vw,50px)]')
        : 'text-4xl md:text-5xl';
    $heroTitleSizeClasses = $titleSizeClasses ?: $defaultTitleSizeClasses;
    $heroSubtitleSizeClasses = $isHomeHero
        ? 'text-[clamp(1rem,3.8vw,1.875rem)]'
        : 'text-xl';
    $heroTitleWeightClass = ($isHomeHero && $headingTag === 'h1') ? 'font-[800]' : 'font-bold';
    
    // Default brand colors (red-orange gradient)
    $brandColorFrom = \App\Models\Setting::get('brand_color_from', '#ea580c');
    $brandColorTo = \App\Models\Setting::get('brand_color_to', '#dc2626');
    
    // Use hero-specific colors if set, otherwise use brand colors
    if ($hero && $hero->gradient_from && $hero->gradient_to) {
        $gradientFrom = $hero->gradient_from;
        $gradientTo = $hero->gradient_to;
    } else {
        $gradientFrom = $brandColorFrom;
        $gradientTo = $brandColorTo;
    }
    
    $backgroundStyle = "background: linear-gradient(to right, {$gradientFrom}, {$gradientTo});";
    $hasImageBackground = false;
    
    // Check for explicit image override (e.g., blog post featured image)
    if ($backgroundImageUrl) {
        $backgroundStyle = "background-image: url('" . $backgroundImageUrl . "'); background-size: cover; background-position: center;";
        $hasImageBackground = true;
    }
    // Check for custom hero image
    elseif ($hero && $hero->background_type === 'image' && $hero->background_image) {
        $backgroundStyle = "background-image: url('/storage/" . ltrim($hero->background_image ?? '', '/') . "'); background-size: cover; background-position: center;";
        $hasImageBackground = true;
    }
    // Check for fallback image prop
    elseif ($fallbackImage && file_exists(public_path($fallbackImage))) {
        $backgroundStyle = "background-image: url('" . asset($fallbackImage) . "'); background-size: cover; background-position: center;";
        $hasImageBackground = true;
    }
    // Check for default home hero image
    elseif ($pageIdentifier === 'home' && file_exists(public_path('images/hero-banner.avif'))) {
        $backgroundStyle = "background-image: url('/images/hero-banner.avif'); background-size: cover; background-position: center;";
        $hasImageBackground = true;
    }
    elseif ($pageIdentifier === 'home' && file_exists(public_path('uploads/hero/home-hero.webp'))) {
        $backgroundStyle = "background-image: url('/uploads/hero/home-hero.webp'); background-size: cover; background-position: center;";
        $hasImageBackground = true;
    }
    
    $heightPresets = [
        'standard' => ['mobile' => '45vh', 'desktop' => '50vh', 'exact' => true],
        'home' => ['mobile' => '45vh', 'desktop' => '60vh', 'exact' => true],
    ];
    $resolvedPreset = $heightPreset && isset($heightPresets[$heightPreset]) ? $heightPresets[$heightPreset] : null;
    $heroInlineStyle = null;
    $heroHeightModeClass = 'hero-height-managed';
    $heroContentPaddingClasses = 'py-12 md:py-20';
    $heroButtonsLayoutClasses = $pageIdentifier === 'home' ? 'flex-nowrap' : 'flex-wrap';
    $heroMobileHeight = '45vh';
    $heroDesktopHeight = '50vh';
    $heroUseExactHeight = false;

    // Determine hero height values: custom > preset > home (60vh desktop) > default (50vh desktop)
    if ($customHeight) {
        $heroUseExactHeight = true;
        $customHeightValue = $customHeightValue ?? ($pageIdentifier === 'home' ? '50vh|60vh' : '45vh|50vh');
        if (str_contains($customHeightValue, '|')) {
            [$mobileValue, $desktopValue] = array_pad(explode('|', $customHeightValue, 2), 2, null);
            $heroMobileHeight = trim((string) ($mobileValue ?: '45vh'));
            $heroDesktopHeight = trim((string) ($desktopValue ?: $heroMobileHeight));
        } else {
            $heroMobileHeight = trim((string) $customHeightValue);
            $heroDesktopHeight = $heroMobileHeight;
        }
    } elseif ($resolvedPreset) {
        $heroMobileHeight = $resolvedPreset['mobile'];
        $heroDesktopHeight = $resolvedPreset['desktop'];
        $heroUseExactHeight = (bool) ($resolvedPreset['exact'] ?? false);
        $heroContentPaddingClasses = $heightPreset === 'home' ? 'py-8 md:py-12' : 'py-6 md:py-10';
    } elseif ($pageIdentifier === 'home') {
        $heroMobileHeight = '50vh';
        $heroDesktopHeight = '60vh';
        $heroUseExactHeight = true;
        $heroContentPaddingClasses = 'py-8 md:py-12';
    } else {
        $heroMobileHeight = '45vh';
        $heroDesktopHeight = '50vh';
        $heroUseExactHeight = false;
        $heroContentPaddingClasses = 'py-6 md:py-10';
    }

    $heroInlineStyle = "--hero-height-mobile: {$heroMobileHeight}; --hero-height-desktop: {$heroDesktopHeight};";
@endphp

@push('head')
<style>
    .hero-height-managed {
        min-height: var(--hero-height-mobile);
    }

    .hero-height-managed.hero-height-exact {
        min-height: var(--hero-height-mobile);
        height: var(--hero-height-mobile);
        max-height: var(--hero-height-mobile);
    }

    @media (min-width: 768px) {
        .hero-height-managed {
            min-height: var(--hero-height-desktop);
        }

        .hero-height-managed.hero-height-exact {
            min-height: var(--hero-height-desktop);
            height: var(--hero-height-desktop);
            max-height: var(--hero-height-desktop);
        }
    }

    @keyframes slowZoom {
        0% { transform: scale(1); }
        100% { transform: scale(1.1); }
    }

    @keyframes heroDrift {
        0% {
            transform: translate3d(-2%, -1%, 0) scale(1.03);
        }
        100% {
            transform: translate3d(2%, 1%, 0) scale(1.08);
        }
    }
    
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes fadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }
    
    @keyframes slideInLeft {
        from {
            opacity: 0;
            transform: translateX(-50px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    @media (prefers-reduced-motion: no-preference) {
        .hero-background-animated {
            animation: slowZoom 20s ease-in-out infinite alternate;
        }

        .hero-background-drift {
            animation: heroDrift 24s ease-in-out infinite alternate;
        }
        
        .hero-title {
            animation: slideInLeft 1s ease-out;
        }
        
        .hero-subtitle {
            animation: fadeInUp 1s ease-out 0.3s backwards;
        }
        
        .hero-description {
            animation: fadeInUp 1s ease-out 0.5s backwards;
        }
        
        .hero-buttons {
            animation: fadeInUp 1s ease-out 0.7s backwards;
        }
        
        .hero-stats {
            animation: fadeIn 1.2s ease-out 0.9s backwards;
        }
    }
</style>
@endpush

<div class="relative text-white {{ $heroHeightModeClass }} {{ $heroUseExactHeight ? 'hero-height-exact' : '' }} overflow-hidden" style="{{ $heroInlineStyle }}">
    @if($hasImageBackground)
        <div class="absolute inset-0 overflow-hidden">
            <div class="hero-background-animated hero-background-drift absolute inset-0 w-full h-full" style="{{ $backgroundStyle }}"></div>
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_25%_20%,rgba(255,255,255,0.16),transparent_45%),radial-gradient(circle_at_80%_70%,rgba(255,255,255,0.12),transparent_42%)] mix-blend-screen pointer-events-none"></div>
        </div>
        <div class="absolute inset-0" style="background-color: rgba(0, 0, 0, 0.65);"></div>
    @else
        <div class="absolute inset-0" style="{{ $backgroundStyle }}"></div>
    @endif
    
    <div class="relative max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 {{ $heroContentPaddingClasses }} h-full flex items-center">
        <div class="w-full flex justify-start items-center">
            <div class="text-left max-w-3xl">
                <{{ $headingTag }} class="hero-title {{ $heroTitleSizeClasses }} {{ $heroTitleWeightClass }} mb-4 md:mb-6 leading-tight sm:leading-tight break-words text-white">
                    {!! $hero->title ?? $fallbackTitle !!}
                </{{ $headingTag }}>
            
            @if($hero && $hero->subtitle)
                <p class="hero-subtitle {{ $heroSubtitleSizeClasses }} leading-snug text-white/90 mb-4 md:mb-6 font-medium break-words">
                    {{ $hero->subtitle }}
                </p>
            @elseif($fallbackSubtitle)
                <p class="hero-subtitle {{ $heroSubtitleSizeClasses }} leading-snug text-white/90 mb-4 md:mb-6 font-medium break-words">
                    {{ $fallbackSubtitle }}
                </p>
            @endif

            @if($hero && $hero->description)
                <p class="hero-description text-[clamp(0.9375rem,2.4vw,1.25rem)] leading-relaxed text-white/80 mb-6 md:mb-8 max-w-2xl break-words">
                    {{ $hero->description }}
                </p>
            @endif

            @php
                $stats = ($hero && $hero->statistics) ? $hero->statistics : $fallbackStats;
            @endphp

            @if(count($stats) > 0)
                <div class="hero-stats flex flex-wrap gap-4 mt-8">
                    @foreach($stats as $stat)
                        <div class="bg-white/10 backdrop-blur-sm rounded-lg px-4 sm:px-6 py-3 min-w-[8.5rem]">
                            <div class="text-[clamp(1.125rem,4vw,1.875rem)] leading-tight font-bold">{{ $stat['value'] ?? '' }}</div>
                            <div class="text-[clamp(0.75rem,2.2vw,0.9rem)] text-white/90">{{ $stat['label'] ?? '' }}</div>
                        </div>
                    @endforeach
                </div>
            @endif

            @if(($hero && ($hero->primary_button_text || $hero->secondary_button_text)) || $pageIdentifier === 'home')
                <div class="hero-buttons flex {{ $heroButtonsLayoutClasses }} gap-4 mt-8">
                    @if($hero && $hero->primary_button_text && $hero->primary_button_link)
                        <a href="{{ $hero->primary_button_link }}" 
                           class="bg-white text-gray-900 hover:bg-gray-100 px-8 py-3 rounded-lg font-semibold transition-all duration-200 shadow-md hover:shadow-lg">
                            {{ $hero->primary_button_text }}
                        </a>
                    @elseif($pageIdentifier === 'home')
                        <a href="{{ route('shop.index') }}" 
                           class="bg-white text-gray-900 hover:bg-gray-100 w-[calc(50%-0.5rem)] sm:w-56 min-w-0 px-3 sm:px-8 py-3 sm:py-4 rounded-lg font-bold text-sm sm:text-lg uppercase tracking-wide transition-all duration-200 shadow-lg hover:shadow-xl hover:scale-105 inline-flex items-center justify-center">
                            SHOP NOW
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                        </a>
                    @endif
                    
                    @if($hero && $hero->secondary_button_text && $hero->secondary_button_link)
                        <a href="{{ $hero->secondary_button_link }}" 
                           class="bg-white/10 backdrop-blur-sm border-2 border-white text-white hover:bg-white/20 px-8 py-4 rounded-lg font-bold text-lg transition-all duration-200 hover:scale-105">
                            {{ $hero->secondary_button_text }}
                        </a>
                    @elseif($pageIdentifier === 'home')
                        <a href="{{ route('register', ['type' => 'vendor']) }}" 
                           class="bg-white/10 backdrop-blur-sm border-2 border-white text-white hover:bg-white/20 w-[calc(50%-0.5rem)] sm:w-56 min-w-0 px-3 sm:px-8 py-3 sm:py-4 rounded-lg font-bold text-sm sm:text-lg uppercase tracking-wide transition-all duration-200 hover:scale-105 inline-flex items-center justify-center">
                            GET STARTED
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                            </svg>
                        </a>
                    @endif
                </div>
            @endif
            </div>
        </div>
    </div>
</div>

