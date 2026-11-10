@php
    $supportedLocales = config('app.supported_locales', ['en']);
    $languageLabels = config('app.supported_locale_labels', []);
    $placement = $placement ?? 'floating';
    $isFooterPlacement = $placement === 'footer';
    $isHeaderPlacement = $placement === 'header';
@endphp

<div
    x-data="{ open: false }"
    class="{{ $isFooterPlacement ? 'relative' : ($isHeaderPlacement ? 'relative' : 'fixed right-4 sm:right-6 bottom-56 z-50') }}"
>
    <button
        type="button"
        @click="open = !open"
        @keydown.escape.window="open = false"
        class="{{ $isFooterPlacement ? 'inline-flex items-center gap-2 rounded-lg bg-gray-800 text-gray-200 px-3 py-2 text-sm hover:bg-gray-700 transition' : ($isHeaderPlacement ? 'inline-flex items-center gap-2 rounded-lg bg-white text-gray-700 px-3 py-2 text-sm border border-gray-200 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-orange-600' : 'h-12 w-12 rounded-full bg-white/95 text-[#ea580c] shadow-lg ring-1 ring-gray-200 backdrop-blur-sm flex items-center justify-center hover:text-orange-700 hover:shadow-xl transition') }}"
        :aria-expanded="open.toString()"
        aria-haspopup="true"
        aria-label="Change language"
    >
        <svg class="h-5 w-5 {{ $isFooterPlacement ? 'text-gray-400' : ($isHeaderPlacement ? 'text-gray-700' : '') }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2 12C2 6.477 4.477 4 7.477 4h1.954c.532 0 .999.339 1.174.82.466.917.466 2.016 0 2.934-.173.483-.634.82-1.174.82H5.477c-.532 0-.999.339-1.174.82-.466.917-.466 2.016 0 2.934.173.483.634.82 1.174.82h2.45c.532 0 .999.339 1.174.82.466.917.466 2.016 0 2.934-.173.483-.634.82-1.174.82H7.477c-.532 0-.999.339-1.174.82-.466.917-.466 2.016 0 2.934.173.483.634.82 1.174.82h1.954c2.997 0 5.477-2.477 5.477-6V6c0-3.523-2.477-6-5.477-6H5.477C2.477 4 0 6.477 0 12zM12 14a2 2 0 100-4 2 2 0 000 4z"/>
        </svg>
        @if($isFooterPlacement || $isHeaderPlacement)
            <span>{{ $languageLabels[app()->getLocale()] ?? strtoupper(app()->getLocale()) }}</span>
        @endif
    </button>

    <div
        x-show="open"
        x-transition.origin.top.left
        @click.away="open = false"
        class="{{ $isFooterPlacement ? 'absolute left-0 top-full mt-2 w-56 max-h-[70vh] overflow-y-auto rounded-xl bg-gray-900 shadow-2xl ring-1 ring-gray-700 p-2 z-[80]' : ($isHeaderPlacement ? 'absolute right-0 top-full mt-2 w-48 rounded-xl bg-white shadow-2xl ring-1 ring-gray-200 p-2 z-[80]' : 'absolute right-14 bottom-0 w-48 rounded-xl bg-white/95 backdrop-blur-sm shadow-2xl ring-1 ring-gray-200 p-2') }}"
        style="display: none;"
    >
        <p class="px-2 pb-2 text-[11px] uppercase tracking-wide {{ $isFooterPlacement ? 'text-gray-400' : ($isHeaderPlacement ? 'text-gray-500' : 'text-gray-500') }}">{{ __('ui.footer.language') }}</p>
        @foreach($supportedLocales as $localeOption)
            @php
                $isActiveLocale = app()->getLocale() === $localeOption;
            @endphp
            <a
                href="{{ route('language.switch', $localeOption) }}"
                class="flex items-center justify-between rounded-lg px-3 py-2 text-sm transition {{ $isActiveLocale ? ($isFooterPlacement ? 'bg-orange-600/20 text-orange-300 font-semibold' : ($isHeaderPlacement ? 'bg-orange-50 text-orange-700 font-semibold' : 'bg-orange-50 text-orange-700 font-semibold')) : ($isFooterPlacement ? 'text-gray-200 hover:bg-gray-700' : 'text-gray-700 hover:bg-gray-50') }}"
            >
                <span>{{ $languageLabels[$localeOption] ?? strtoupper($localeOption) }}</span>
            </a>
        @endforeach
    </div>
</div>