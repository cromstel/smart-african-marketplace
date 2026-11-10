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
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
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
                <span class="text-xs {{ $isFooterPlacement ? 'text-gray-400' : ($isHeaderPlacement ? 'text-gray-500' : 'text-gray-500') }}">{{ strtoupper($localeOption) }}</span>
            </a>
        @endforeach
    </div>
</div>