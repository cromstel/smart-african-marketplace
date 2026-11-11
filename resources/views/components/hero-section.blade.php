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
    $brandColorFrom = \App\Models\Setting::getValue('brand_color_from', '#ea580c');
    $brandColorTo = \App\Models\Setting::getValue('brand_color_to', '#dc2626');
    
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
        'standard' => ['mobile' => '45vh', 'desktop' => '600px', 'exact' => true],
    ];
    $resolvedPreset = $heightPreset && isset($heightPresets[$heightPreset]) ? $heightPresets[$heightPreset] : null;
    $heroInlineStyle = null;
    $heroHeightModeClass = 'hero-height-managed';
    $heroContentPaddingClasses = 'py-12 md:py-20';
    // Always allow wrapping: fixed-width/nowrap CTAs previously risked overflow
    // on narrow viewports or with longer translated button labels.
    $heroButtonsLayoutClasses = 'flex-wrap';
    $heroMobileHeight = '45vh';
    $heroDesktopHeight = '600px';
    $heroUseExactHeight = true;

    // Determine hero height values: custom > preset > home (60vh desktop) > default (600px desktop)
    if ($customHeight) {
        $heroUseExactHeight = true;
        $customHeightValue = $customHeightValue ?? ($pageIdentifier === 'home' ? '50vh|60vh' : '45vh|600px');
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
        $heroContentPaddingClasses = 'py-6 md:py-10';
    } elseif ($pageIdentifier === 'home') {
        $heroMobileHeight = '50vh';
        $heroDesktopHeight = '60vh';
        $heroUseExactHeight = true;
        $heroContentPaddingClasses = 'py-8 md:py-12';
    } else {
        $heroMobileHeight = '45vh';
        $heroDesktopHeight = '600px';
        $heroUseExactHeight = true;
        $heroContentPaddingClasses = 'py-6 md:py-10';
    }

    $heroInlineStyle = "--hero-height-mobile: {$heroMobileHeight}; --hero-height-desktop: {$heroDesktopHeight};";
@endphp

@push('head')
<style>
    .hero-height-managed {
        min-height: var(--hero-height-mobile);
    }

    /*
     * "Exact" height is a target, not a hard cap: it sets the same
     * min-height as the managed default. A fixed `height`/`max-height`
     * here used to clip the title/buttons on short viewports or with
     * longer admin-entered titles (verified clipped at 320x568 with the
     * default fallback copy) because the content is vertically centered
     * inside a box that can't grow. Letting the box grow past the target
     * when content needs more room keeps CTAs reachable; the background
     * (absolutely positioned, inset-0) simply stretches to match, so the
     * common case -- where content already fits within the target height
     * -- looks identical to before.
     */
    .hero-height-managed.hero-height-exact {
        min-height: var(--hero-height-mobile);
    }

    @media (min-width: 768px) {
        .hero-height-managed {
            min-height: var(--hero-height-desktop);
        }

        .hero-height-managed.hero-height-exact {
            min-height: var(--hero-height-desktop);
        }
    }

    .decoration-clone {
        image-rendering: -webkit-optimize-contrast;
        image-rendering: crisp-edges;
        backface-visibility: hidden;
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
            <div class="absolute inset-0 w-full h-full {{ $animated ? 'hero-background-animated hero-background-drift' : '' }} decoration-clone" style="{{ $backgroundStyle }}"></div>
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_25%_20%,rgba(255,255,255,0.1),transparent_45%),radial-gradient(circle_at_80%_70%,rgba(255,255,255,0.08),transparent_42%)] mix-blend-screen pointer-events-none"></div>
        </div>
        <div class="absolute inset-0" style="background-color: rgba(0, 0, 0, 0.4);"></div>
    @else
        <div class="absolute inset-0" style="{{ $backgroundStyle }}"></div>
    @endif
    
    <div class="relative w-full px-4 sm:px-6 lg:px-8 {{ $heroContentPaddingClasses }} h-full flex items-center">
        <div class="w-full flex justify-start items-center">
            <div class="text-left">
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
                <div class="hero-stats flex flex-wrap gap-4 mt-6 md:mt-8">
                    @foreach($stats as $stat)
                        <div class="bg-white/10 backdrop-blur-sm rounded-lg px-4 sm:px-6 py-3 min-w-[8.5rem]">
                            <div class="text-[clamp(1.125rem,4vw,1.875rem)] leading-tight font-bold">{{ $stat['value'] ?? '' }}</div>
                            <div class="text-[clamp(0.75rem,2.2vw,0.9rem)] text-white/90">{{ $stat['label'] ?? '' }}</div>
                        </div>
                    @endforeach
                </div>
            @endif

            @php
                // Compact, consistent CTA styling shared by every hero across the
                // site: same padding/height/radius/font-size for primary and
                // secondary so they read as one design system, regardless of
                // whether the copy comes from the admin-configured HeroSection
                // or the hardcoded homepage fallback. Sized for a comfortable
                // >=44px touch target without the buttons dominating the hero.
                $heroPrimaryBtnClasses = 'inline-flex items-center justify-center gap-1.5 bg-white text-gray-900 hover:bg-gray-100 active:bg-gray-200 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white px-5 py-2.5 sm:px-6 sm:py-3 rounded-lg font-semibold text-sm sm:text-base shadow-sm hover:shadow-md active:scale-[0.98] transition-all duration-150';
                $heroSecondaryBtnClasses = 'inline-flex items-center justify-center gap-1.5 bg-white/10 backdrop-blur-sm border border-white/70 text-white hover:bg-white/20 hover:border-white active:bg-white/25 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white px-5 py-2.5 sm:px-6 sm:py-3 rounded-lg font-semibold text-sm sm:text-base active:scale-[0.98] transition-all duration-150';
            @endphp

            @if(($hero && ($hero->primary_button_text || $hero->secondary_button_text)) || $pageIdentifier === 'home')
                <div class="hero-buttons flex {{ $heroButtonsLayoutClasses }} items-center gap-3 mt-6 md:mt-8">
                    @if($hero && $hero->primary_button_text && $hero->primary_button_link)
                        <a href="{{ $hero->primary_button_link }}" 
                           class="{{ $heroPrimaryBtnClasses }}">
                            {{ $hero->primary_button_text }}
                        </a>
                    @elseif($pageIdentifier === 'home')
                        <a href="{{ route('shop.index') }}" 
                           class="{{ $heroPrimaryBtnClasses }}">
                            Shop Now
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                        </a>
                    @endif
                    
                    @if($hero && $hero->secondary_button_text && $hero->secondary_button_link)
                        <a href="{{ $hero->secondary_button_link }}" 
                           class="{{ $heroSecondaryBtnClasses }}">
                            {{ $hero->secondary_button_text }}
                        </a>
                    @elseif($pageIdentifier === 'home')
                        <a href="{{ route('register', ['type' => 'vendor']) }}" 
                           class="{{ $heroSecondaryBtnClasses }}">
                            Get Started
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

