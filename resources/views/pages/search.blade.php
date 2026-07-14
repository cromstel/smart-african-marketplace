@extends('layouts.shop')

@section('title', 'Search')

@section('content')
@php
    $selectedCategories = $filters['categories'] ?? [];
    $selectedBrands = $filters['brands'] ?? [];
    $selectedVendors = $filters['vendors'] ?? [];
    $locationCity = $filters['location_city'] ?? '';
    $locationCountry = $filters['location_country'] ?? '';
    $ratingMin = $filters['rating_min'] ?? null;
    $minPrice = $filters['min_price'] ?? null;
    $maxPrice = $filters['max_price'] ?? null;
    $baseQuery = request()->query();
    $resetQuery = ['q' => $query ?: null];
    $activeFilterCount = count($selectedCategories) + count($selectedBrands) + count($selectedVendors)
        + ($locationCity !== '' ? 1 : 0)
        + ($locationCountry !== '' ? 1 : 0)
        + ($ratingMin !== null ? 1 : 0)
        + ($minPrice !== null || $maxPrice !== null ? 1 : 0);
@endphp

<div class="min-h-screen bg-gray-50">
    <div class="bg-gradient-to-r from-orange-600 to-red-600 text-white py-16">
        <div class="container mx-auto px-4">
            <div class="space-y-3 max-w-3xl">
                <h1 class="text-4xl md:text-5xl font-semibold leading-tight">Search the Smart African Marketplace</h1>
                <p class="text-lg text-orange-50">Use filters, insights, and suggestions to find products, salon services, vendors, and stories that matter.</p>
            </div>
            <div class="mt-8 max-w-4xl">
                <form id="search-form" action="{{ route('search.index') }}" method="GET"
                      x-data="searchSuggestions(@json(route('search.suggestions')), @json($query))"
                      x-init="init()"
                      @click.away="open = false"
                      class="space-y-6">
                    <div class="relative">
                        <input
                            type="search"
                            name="q"
                            x-model="query"
                            @input.debounce.300ms="fetchSuggestions"
                            placeholder="Search products, salons, services, or articles..."
                            class="w-full rounded-full border border-white/60 bg-white/95 px-5 py-3 pr-32 text-gray-900 shadow-2xl outline-none focus:border-transparent focus:ring-4 focus:ring-orange-300 transition"
                        >
                        <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 rounded-full bg-black bg-opacity-30 px-5 py-2 text-xs font-semibold uppercase tracking-wide text-white shadow-lg">
                            Search
                        </button>
                        <div
                            x-show="open"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 translate-y-1"
                            class="absolute inset-x-0 top-full z-20 mt-3 max-h-72 overflow-y-auto rounded-3xl border border-white/40 bg-white text-gray-900 shadow-2xl"
                            style="display: none;"
                        >
                            <template x-for="suggestion in suggestions" :key="suggestion">
                                <a
                                    :href="`{{ route('search.index') }}?q=${encodeURIComponent(suggestion)}`"
                                    @click="open = false"
                                    class="flex items-center justify-between gap-2 px-4 py-3 text-sm font-medium transition hover:bg-orange-50"
                                >
                                    <span x-text="suggestion"></span>
                                    <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            </template>
                            <template x-if="suggestions.length === 0">
                                <div class="px-4 py-3 text-sm text-gray-500">
                                    <span x-text="`Press enter to search for \"${query}\"`"></span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="grid gap-6 rounded-3xl bg-white/90 p-6 shadow-2xl lg:grid-cols-2">
                        <div class="space-y-5">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-2">Categories</p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($facetCategories as $category)
                                        <label class="flex items-center gap-2 rounded-full border border-gray-200 px-3 py-1 text-sm text-gray-700 bg-white hover:border-orange-500">
                                            <input type="checkbox" name="categories[]" value="{{ $category['id'] }}"
                                                   class="h-4 w-4 rounded border-gray-300 text-orange-500 focus:ring-orange-400"
                                                   {{ in_array($category['id'], $selectedCategories, true) ? 'checked' : '' }}>
                                            <span>{{ $category['name'] }} <span class="text-xs text-gray-400">({{ $category['count'] }})</span></span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-2">Brands</p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($facetBrands as $brand)
                                        <label class="flex items-center gap-2 rounded-full border border-gray-200 px-3 py-1 text-sm text-gray-700 bg-white hover:border-orange-500">
                                            <input type="checkbox" name="brands[]" value="{{ $brand['id'] }}"
                                                   class="h-4 w-4 rounded border-gray-300 text-orange-500 focus:ring-orange-400"
                                                   {{ in_array($brand['id'], $selectedBrands, true) ? 'checked' : '' }}>
                                            <span>{{ $brand['name'] }} <span class="text-xs text-gray-400">({{ $brand['count'] }})</span></span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-2">Vendors</p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($facetVendors as $vendor)
                                        <label class="flex items-center gap-2 rounded-full border border-gray-200 px-3 py-1 text-sm text-gray-700 bg-white hover:border-orange-500">
                                            <input type="checkbox" name="vendors[]" value="{{ $vendor['id'] }}"
                                                   class="h-4 w-4 rounded border-gray-300 text-orange-500 focus:ring-orange-400"
                                                   {{ in_array($vendor['id'], $selectedVendors, true) ? 'checked' : '' }}>
                                            <span>{{ $vendor['name'] }} <span class="text-xs text-gray-400">({{ $vendor['count'] }})</span></span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="space-y-5">
                            <div class="space-y-2">
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Location</p>
                                <div class="grid gap-3 md:grid-cols-2">
                                    <input type="text" name="location_city" value="{{ $locationCity }}" placeholder="City"
                                           class="w-full rounded-lg border border-gray-200 px-4 py-2 text-sm text-gray-700 focus:border-orange-400 focus:outline-none">
                                    <input type="text" name="location_country" value="{{ $locationCountry }}" placeholder="Country"
                                           class="w-full rounded-lg border border-gray-200 px-4 py-2 text-sm text-gray-700 focus:border-orange-400 focus:outline-none">
                                </div>
                            </div>
                            <div x-data="priceRange(@json($priceMin), @json($priceMax), @json($minPrice), @json($maxPrice))" class="space-y-3">
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Price range</p>
                                <div class="text-sm text-gray-600 flex items-center justify-between">
                                    <span>Min: <span x-text="formatValue(minValue)"></span></span>
                                    <span>Max: <span x-text="formatValue(maxValue)"></span></span>
                                </div>
                                <div class="space-y-2">
                                    <input type="range" min="@json($priceMin)" max="@json($priceMax)" step="1"
                                           x-model.number="minValue"
                                           @input="if (minValue > maxValue) { minValue = maxValue }"
                                           class="w-full accent-orange-500">
                                    <input type="range" min="@json($priceMin)" max="@json($priceMax)" step="1"
                                           x-model.number="maxValue"
                                           @input="if (maxValue < minValue) { maxValue = minValue }"
                                           class="w-full accent-orange-500">
                                </div>
                                <input type="hidden" name="min_price" :value="minValue">
                                <input type="hidden" name="max_price" :value="maxValue">
                            </div>
                            <div class="space-y-2">
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Minimum rating</p>
                                <select name="rating_min" class="w-full rounded-lg border border-gray-200 px-4 py-2 text-sm text-gray-700 focus:border-orange-400 focus:outline-none">
                                    <option value="">Any rating</option>
                                    @foreach(range(5, 1) as $rating)
                                        <option value="{{ $rating }}" {{ $ratingMin == $rating ? 'selected' : '' }}>
                                            {{ $rating }} stars & up
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-3 text-sm">
                        <button type="submit" class="inline-flex items-center justify-center rounded-full bg-black bg-opacity-30 px-6 py-2 font-semibold uppercase tracking-widest text-white shadow-lg shadow-black/50 transition hover:bg-black/70">
                            Apply filters
                        </button>
                        <a href="{{ route('search.index', array_filter($resetQuery, fn($value) => $value !== null && $value !== '')) }}" class="text-sm font-semibold text-white hover:text-orange-200">
                            Reset filters
                        </a>
                        <span class="text-xs text-white/70">{{ $activeFilterCount }} filters active</span>
                    </div>
                </form>

                @if($topQueries->isNotEmpty())
                    <div class="mt-4 flex flex-wrap gap-2 text-sm text-white/80">
                        <span class="text-xs font-semibold uppercase tracking-widest text-orange-100">Trending</span>
                        @foreach($topQueries as $suggestion)
                            <a href="{{ route('search.index', ['q' => $suggestion->query]) }}"
                               class="rounded-full border border-white/40 px-4 py-1 transition hover:border-white">
                                {{ $suggestion->query }}
                            </a>
                        @endforeach
                    </div>
                @endif

                @if($activeFilterCount > 0)
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach($selectedCategories as $categoryId)
                            @php
                                $params = $baseQuery;
                                $params['categories'] = array_values(array_filter(array_diff($params['categories'] ?? [], [$categoryId])));
                                if (empty($params['categories'])) {
                                    unset($params['categories']);
                                }
                            @endphp
                            <a href="{{ route('search.index', $params) }}" class="inline-flex items-center gap-1 rounded-full border border-white/50 bg-white/20 px-3 py-1 text-xs text-white transition hover:bg-white/40">
                                Category: {{ $selectedCategoryNames->get($categoryId) ?? 'Category' }}
                                <span aria-hidden="true">×</span>
                            </a>
                        @endforeach
                        @foreach($selectedBrands as $brandId)
                            @php
                                $params = $baseQuery;
                                $params['brands'] = array_values(array_filter(array_diff($params['brands'] ?? [], [$brandId])));
                                if (empty($params['brands'])) {
                                    unset($params['brands']);
                                }
                            @endphp
                            <a href="{{ route('search.index', $params) }}" class="inline-flex items-center gap-1 rounded-full border border-white/50 bg-white/20 px-3 py-1 text-xs text-white transition hover:bg-white/40">
                                Brand: {{ $selectedBrandNames->get($brandId) ?? 'Brand' }}
                                <span aria-hidden="true">×</span>
                            </a>
                        @endforeach
                        @foreach($selectedVendors as $vendorId)
                            @php
                                $params = $baseQuery;
                                $params['vendors'] = array_values(array_filter(array_diff($params['vendors'] ?? [], [$vendorId])));
                                if (empty($params['vendors'])) {
                                    unset($params['vendors']);
                                }
                            @endphp
                            <a href="{{ route('search.index', $params) }}" class="inline-flex items-center gap-1 rounded-full border border-white/50 bg-white/20 px-3 py-1 text-xs text-white transition hover:bg-white/40">
                                Vendor: {{ $selectedVendorNames->get($vendorId) ?? 'Vendor' }}
                                <span aria-hidden="true">×</span>
                            </a>
                        @endforeach
                        @if($locationCity !== '')
                            @php
                                $params = $baseQuery;
                                unset($params['location_city']);
                            @endphp
                            <a href="{{ route('search.index', $params) }}" class="inline-flex items-center gap-1 rounded-full border border-white/50 bg-white/20 px-3 py-1 text-xs text-white transition hover:bg-white/40">
                                City: {{ $locationCity }}
                                <span aria-hidden="true">×</span>
                            </a>
                        @endif
                        @if($locationCountry !== '')
                            @php
                                $params = $baseQuery;
                                unset($params['location_country']);
                            @endphp
                            <a href="{{ route('search.index', $params) }}" class="inline-flex items-center gap-1 rounded-full border border-white/50 bg-white/20 px-3 py-1 text-xs text-white transition hover:bg-white/40">
                                Country: {{ $locationCountry }}
                                <span aria-hidden="true">×</span>
                            </a>
                        @endif
                        @if($ratingMin !== null)
                            @php
                                $params = $baseQuery;
                                unset($params['rating_min']);
                            @endphp
                            <a href="{{ route('search.index', $params) }}" class="inline-flex items-center gap-1 rounded-full border border-white/50 bg-white/20 px-3 py-1 text-xs text-white transition hover:bg-white/40">
                                Rating: {{ $ratingMin }}+
                                <span aria-hidden="true">×</span>
                            </a>
                        @endif
                        @if($minPrice !== null || $maxPrice !== null)
                            @php
                                $params = $baseQuery;
                                unset($params['min_price'], $params['max_price']);
                            @endphp
                            <a href="{{ route('search.index', $params) }}" class="inline-flex items-center gap-1 rounded-full border border-white/50 bg-white/20 px-3 py-1 text-xs text-white transition hover:bg-white/40">
                                Price range
                                <span aria-hidden="true">×</span>
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-16">
        <div class="space-y-12">
            <div>
                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <h2 class="text-2xl font-bold text-gray-900">Products ({{ $products->total() }})</h2>
                    @if($query !== '')
                        <p class="text-sm text-gray-500">{{ $products->total() }} items match “{{ $query }}”</p>
                    @else
                        <p class="text-sm text-gray-500">Showing the latest matching items</p>
                    @endif
                </div>
                @if($products->count() > 0)
                    <div class="mt-6 grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                        @foreach($products as $product)
                            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 hover:shadow-xl transition">
                                <a href="{{ route('shop.show', $product) }}">
                                    <div class="aspect-w-16 aspect-h-12 bg-gray-100">
                                        @if($product->images->count())
                                            <img src="{{ '/storage/' . ltrim($product->images->first()->image_path ?? '', '/') }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                                        @else
                                            <div class="flex h-full items-center justify-center">
                                                <span class="text-sm text-gray-400">No image</span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="p-4">
                                        <h3 class="text-lg font-semibold text-gray-900">{{ $product->name }}</h3>
                                        <p class="text-sm text-gray-500 truncate">{{ Str::limit($product->short_description ?? $product->description, 80) }}</p>
                                        <div class="mt-3 flex items-center justify-between text-base font-semibold text-gray-900">
                                            <span>${{ number_format($product->price ?? 0, 2) }}</span>
                                            <span class="text-xs text-gray-500">{{ number_format($product->average_rating ?? 0, 1) }} ★</span>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-8">
                        {{ $products->links() }}
                    </div>
                @else
                    <div class="mt-8 rounded-2xl border border-dashed border-gray-300 bg-white p-12 text-center text-gray-500">
                        <p class="text-xl font-semibold text-gray-900">No products found</p>
                        <p class="mt-2">Try adjusting your filters or search terms.</p>
                    </div>
                @endif
            </div>

            @if($posts->count() > 0)
                <div>
                    <div class="flex items-center justify-between">
                        <h2 class="text-2xl font-bold text-gray-900">Blog posts</h2>
                        <a href="{{ route('blog.index') }}" class="text-sm font-semibold text-orange-600">View all</a>
                    </div>
                    <div class="mt-6 space-y-4">
                        @foreach($posts as $post)
                            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                                <h3 class="text-xl font-semibold text-gray-900"><a href="{{ route('blog.show', $post->slug) }}" class="hover:text-orange-600">{{ $post->title }}</a></h3>
                                <p class="mt-2 text-sm text-gray-500">{{ Str::limit($post->excerpt ?? strip_tags($post->content), 150) }}</p>
                                <p class="mt-2 text-xs text-gray-400">{{ $post->published_at->format('M d, Y') }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($categoryMatches->count() > 0)
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Categories</h2>
                    <div class="flex flex-wrap gap-3">
                        @foreach($categoryMatches as $match)
                            <a href="{{ route('shop.index', ['category' => $match->id]) }}" class="rounded-full border border-gray-200 px-4 py-2 text-sm text-gray-700 hover:border-orange-500 hover:text-orange-500">
                                {{ $match->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    function priceRange(minBound, maxBound, initialMin, initialMax) {
        minBound = Number(minBound);
        maxBound = Number(maxBound);
        return {
            minValue: Number(initialMin ?? minBound),
            maxValue: Number(initialMax ?? maxBound),
            minBound,
            maxBound,
            formatValue(value) {
                return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(value);
            },
        };
    }

    function searchSuggestions(endpoint, initialQuery = '') {
        return {
            query: initialQuery,
            suggestions: [],
            open: false,
            controller: null,
            init() {
                if (this.query.length >= 2) {
                    this.fetchSuggestions();
                }
            },
            fetchSuggestions() {
                if (this.query.length < 2) {
                    this.suggestions = [];
                    this.open = false;
                    return;
                }
                if (this.controller) {
                    this.controller.abort();
                }
                this.controller = new AbortController();
                fetch(`${endpoint}?q=${encodeURIComponent(this.query)}`, {
                    signal: this.controller.signal,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                })
                    .then(response => response.json())
                    .then(data => {
                        this.suggestions = data;
                        this.open = data.length > 0;
                    })
                    .catch(() => {
                        this.suggestions = [];
                        this.open = false;
                    });
            },
        };
    }
</script>
@endsection

