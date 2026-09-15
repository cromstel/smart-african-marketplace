@php
    $auctionTabs = [
        'index' => ['route' => 'auctions.index', 'label' => 'All Active'],
        'featured' => ['route' => 'auctions.featured', 'label' => 'Featured'],
        'ending-soon' => ['route' => 'auctions.ending-soon', 'label' => 'Ending Soon'],
    ];
@endphp
<div class="flex flex-wrap gap-2">
    @foreach($auctionTabs as $key => $tab)
        <a
            href="{{ route($tab['route']) }}"
            class="px-4 py-2 rounded-lg text-sm font-semibold transition-colors duration-200 {{ ($active ?? 'index') === $key
                ? 'bg-gray-900 text-white'
                : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50' }}"
        >
            {{ $tab['label'] }}
        </a>
    @endforeach
</div>
