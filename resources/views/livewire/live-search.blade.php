<div
    class="relative"
    x-data="{ open: false }"
    x-init="
        const initEntangle = () => {
            if (typeof $wire === 'undefined') {
                return false;
            }
            open = $wire.entangle('showDropdown');
            return true;
        };
        if (!initEntangle()) {
            document.addEventListener('livewire:load', () => initEntangle(), { once: true });
        }
    "
    @click.away="open = false"
>
    <div class="relative">
        <label for="global-search-{{ $this->getId() }}" class="sr-only">Global search</label>
        <input 
            type="text"
            id="global-search-{{ $this->getId() }}"
            name="search"
            autocomplete="off"
            wire:model.live.debounce.300ms="query" 
            placeholder="Search products, services, blog..." 
            class="w-full md:w-44 lg:w-56 xl:w-72 max-w-full px-4 py-2 pl-10 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-600 focus:border-transparent transition"
            @focus="if ($wire.query.length >= 2) open = true"
        >
        <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        
        @if($query)
            <button 
                type="button"
                wire:click="clearSearch" 
                aria-label="Clear search"
                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        @endif
    </div>

    @if($showDropdown && (count($products) > 0 || count($posts) > 0 || count($services) > 0 || strlen($query) >= 2))
        <div 
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 transform scale-95"
            x-transition:enter-end="opacity-100 transform scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 transform scale-100"
            x-transition:leave-end="opacity-0 transform scale-95"
            class="absolute z-50 mt-2 w-full md:w-96 bg-white rounded-lg shadow-2xl border border-gray-200 max-h-[500px] overflow-y-auto"
        >
            @if(count($products) > 0)
                <div class="p-3 border-b border-gray-100">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Products</h3>
                    @foreach($products as $product)
                        <a href="{{ route('shop.show', $product) }}" 
                           class="flex items-center space-x-3 p-2 hover:bg-gray-50 rounded-lg transition group"
                           wire:click="clearSearch">
                            @if($product->featured_image)
                                <img src="{{ '/storage/' . ltrim($product->featured_image ?? '', '/') }}" 
                                     alt="{{ $product->name }}" 
                                     class="w-12 h-12 object-cover rounded-lg">
                            @else
                                <div class="w-12 h-12 bg-gradient-to-br from-orange-100 to-red-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                    </svg>
                                </div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 group-hover:text-orange-600 truncate">{{ $product->name }}</p>
                                <p class="text-xs text-gray-500">{{ $product->category->name ?? 'Uncategorized' }}</p>
                            </div>
                            <span class="text-sm font-bold text-gray-900">${{ number_format($product->price, 2) }}</span>
                        </a>
                    @endforeach
                </div>
            @endif

            @if(count($services) > 0)
                <div class="p-3 border-b border-gray-100">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Salon Services</h3>
                    @foreach($services as $service)
                        <a href="{{ route('salon.show', $service) }}" 
                           class="flex items-center space-x-3 p-2 hover:bg-gray-50 rounded-lg transition group"
                           wire:click="clearSearch">
                            @if($service->image)
                                <img src="{{ '/storage/' . ltrim($service->image ?? '', '/') }}" 
                                     alt="{{ $service->name }}" 
                                     class="w-12 h-12 object-cover rounded-lg">
                            @else
                                <div class="w-12 h-12 bg-gradient-to-br from-purple-100 to-pink-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 group-hover:text-orange-600 truncate">{{ $service->name }}</p>
                                <p class="text-xs text-gray-500">{{ $service->duration }} mins</p>
                            </div>
                            <span class="text-sm font-bold text-gray-900">${{ number_format($service->price, 2) }}</span>
                        </a>
                    @endforeach
                </div>
            @endif

            @if(count($posts) > 0)
                <div class="p-3">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Blog Posts</h3>
                    @foreach($posts as $post)
                        <a href="{{ route('blog.show', $post) }}" 
                           class="flex items-center space-x-3 p-2 hover:bg-gray-50 rounded-lg transition group"
                           wire:click="clearSearch">
                            @if($post->featured_image)
                                <img src="{{ '/storage/' . ltrim($post->featured_image ?? '', '/') }}" 
                                     alt="{{ $post->title }}" 
                                     class="w-12 h-12 object-cover rounded-lg">
                            @else
                                <div class="w-12 h-12 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                                    </svg>
                                </div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 group-hover:text-orange-600 truncate">{{ $post->title }}</p>
                                <p class="text-xs text-gray-500">{{ $post->published_at->format('M d, Y') }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif

            @if(count($products) == 0 && count($posts) == 0 && count($services) == 0 && strlen($query) >= 2)
                <div class="p-6 text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-gray-500 font-medium">No results found for "{{ $query }}"</p>
                    <p class="text-sm text-gray-400 mt-1">Try different keywords</p>
                </div>
            @endif

            @if(count($products) > 0 || count($posts) > 0 || count($services) > 0)
                <div class="p-3 border-t border-gray-100 bg-gray-50">
                    <a href="{{ route('search.index', ['q' => $query]) }}" 
                       class="block text-center text-sm text-orange-600 hover:text-orange-700 font-medium"
                       wire:click="clearSearch">
                        View all results for "{{ $query }}" →
                    </a>
                </div>
            @endif
        </div>
    @endif
</div>

