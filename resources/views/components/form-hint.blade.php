@props(['text'])

<div {{ $attributes->merge(['class' => 'absolute inset-y-0 right-3 flex items-center']) }}>
    <div class="relative group">
        <button
            type="button"
            class="inline-flex h-5 w-5 items-center justify-center rounded-full border border-orange-300 bg-white/90 text-xs font-semibold text-orange-600 shadow-sm transition hover:bg-orange-50 focus:outline-none focus:ring-2 focus:ring-orange-500"
            aria-label="Field help"
        >
            ?
        </button>
        <div
            role="tooltip"
            class="pointer-events-none absolute right-0 top-7 z-50 w-56 rounded-md bg-gray-900 px-3 py-2 text-xs leading-5 text-white opacity-0 shadow-lg transition-opacity duration-150 group-hover:opacity-100 group-focus-within:opacity-100"
        >
            {{ $text }}
        </div>
    </div>
</div>

