@php
    $auctionEndsAt = $auction->ends_at;
    $auctionIsEndingSoon = $auctionEndsAt && $auctionEndsAt->isFuture() && $auctionEndsAt->diffInHours(now()) < 24;
@endphp
<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow duration-200 flex flex-col h-full">
    <a href="{{ route('auctions.show', $auction->slug) }}" class="block relative">
        <div class="aspect-w-16 aspect-h-12 bg-gray-100">
            @php
                $auctionImage = $auction->product?->images->first()?->image_path;
            @endphp
            @if($auctionImage)
                <img src="{{ '/storage/' . ltrim($auctionImage, '/') }}" alt="{{ $auction->title }}" class="object-cover w-full h-48" loading="lazy">
            @else
                <div class="flex items-center justify-center h-48 bg-gray-100">
                    <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h11M9 21V3m0 0L3 9m6-6l6 6"/>
                    </svg>
                </div>
            @endif
        </div>
        <div class="absolute top-2 right-2 flex flex-col items-end gap-1">
            @if($auction->is_featured)
                <span class="px-2 py-1 bg-amber-500 text-white text-xs font-semibold rounded shadow-sm">Featured</span>
            @endif
            @if($auctionIsEndingSoon)
                <span class="px-2 py-1 bg-red-600 text-white text-xs font-semibold rounded shadow-sm">Ending Soon</span>
            @endif
        </div>
    </a>

    <div class="p-4 flex flex-col flex-1">
        @if($auction->vendor)
            <p class="text-xs text-gray-500 mb-1">{{ $auction->vendor->business_name }}</p>
        @endif

        <h3 class="text-lg font-semibold text-gray-900 mb-1 line-clamp-2">
            <a href="{{ route('auctions.show', $auction->slug) }}" class="hover:text-orange-600 transition-colors">{{ $auction->title }}</a>
        </h3>

        @if($auction->description)
            <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ Str::limit($auction->description, 100) }}</p>
        @endif

        <div class="mt-auto space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-500">Current Bid</span>
                <span class="text-xl font-bold text-gray-900">${{ number_format((float) $auction->current_price, 2) }}</span>
            </div>

            <div class="flex items-center justify-between text-sm">
                <span class="text-gray-500">{{ $auction->total_bids }} {{ Str::plural('bid', $auction->total_bids) }}</span>
                @if($auctionEndsAt)
                    <span class="text-gray-700 font-medium">
                        {{ $auctionEndsAt->isFuture() ? 'Ends ' . $auctionEndsAt->diffForHumans() : 'Ended' }}
                    </span>
                @endif
            </div>

            <a href="{{ route('auctions.show', $auction->slug) }}" class="block w-full text-center bg-gradient-to-r from-orange-600 to-red-600 hover:from-orange-700 hover:to-red-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200">
                View Auction
            </a>
        </div>
    </div>
</div>
