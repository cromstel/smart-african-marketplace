<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{--
        VendorStoreController::renderStore() already builds a full SeoService
        state (title, description, keywords, canonical, OG image, and an
        Organization schema via addStructuredData()) and passes it here as
        $seo -- but <x-seo /> previously rendered with no props at all,
        producing a second, empty <title> alongside a hardcoded one above it
        and silently dropping the vendor's own description/canonical/image
        and Organization rich result on every storefront page.
    --}}
    <x-seo
        :title="$seo->getTitle()"
        :description="$seo->getDescription()"
        :keywords="$seo->getKeywords()"
        :canonical="$seo->getCanonical()"
        :image="$seo->getOpenGraph()['image'] ?? null"
        :type="$seo->getOpenGraph()['type'] ?? 'website'"
    />
    @foreach($seo->getStructuredData() as $schema)
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
    @endforeach

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @if($theme && $theme->is_active)
        <style>
            :root {
                --primary-color: {{ $theme->primary_color }};
                --secondary-color: {{ $theme->secondary_color }};
                --accent-color: {{ $theme->accent_color }};
                --text-color: {{ $theme->text_color }};
                --background-color: {{ $theme->background_color }};
                --font-family: {{ $theme->font_family }}, sans-serif;
                --font-heading: {{ $theme->font_heading }}, sans-serif;
            }
            body {
                background-color: var(--background-color);
                color: var(--text-color);
                font-family: var(--font-family);
            }
            h1, h2, h3, h4, h5, h6 {
                font-family: var(--font-heading);
            }
            .btn-primary {
                background-color: var(--primary-color);
            }
            .btn-primary:hover {
                background-color: var(--secondary-color);
            }
            .text-primary {
                color: var(--primary-color);
            }
            .bg-primary {
                background-color: var(--primary-color);
            }
            .border-primary {
                border-color: var(--primary-color);
            }
        </style>

        @if($theme->custom_css)
            <style>{!! $theme->custom_css !!}</style>
        @endif
    @endif
</head>
<body class="min-h-screen">
    <header class="sticky top-0 z-50 bg-white shadow-md">
        <div class="container mx-auto px-4 py-4">
            <h2 class="sr-only">{{ $vendor->business_name }}</h2>
            @php
                $storeProfileImage = $vendor->profile_image ?: $vendor->logo;
            @endphp
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    @if($storeProfileImage)
                        <img src="{{ '/storage/' . ltrim($storeProfileImage ?? '', '/') }}" alt="{{ $vendor->business_name }} profile image" class="h-12 w-12 rounded-full object-cover">
                    @else
                        <div class="h-12 w-12 rounded-full bg-primary text-white flex items-center justify-center font-semibold">
                            {{ strtoupper(substr($vendor->business_name, 0, 1)) }}
                        </div>
                    @endif
                    <span class="text-xl font-semibold text-primary">{{ $vendor->business_name }}</span>
                </div>

                <nav class="hidden md:flex items-center space-x-6">
                    <a href="{{ route('home') }}" class="hover:text-primary transition">Home</a>
                    <a href="{{ route('shop.index') }}" class="hover:text-primary transition">Marketplace</a>
                    @auth
                        <a href="{{ route('cart.index') }}" class="hover:text-primary transition">Cart</a>
                        <a href="{{ route('dashboard') }}" class="hover:text-primary transition">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="hover:text-primary transition">Login</a>
                    @endauth
                </nav>

                <form action="{{ $vendor->storefrontUrl() }}" method="GET" class="hidden md:block">
                    @if($theme && $theme->is_active && $theme->show_search)
                        <input type="search" name="search" value="{{ request('search') }}" placeholder="Search products..." class="border rounded-lg px-4 py-2 w-64">
                    @endif
                </form>
            </div>
        </div>
    </header>

    @if($theme && $theme->is_active && $theme->banner_image)
        <div class="relative h-64 md:h-96 overflow-hidden">
            <img src="{{ '/storage/' . ltrim($theme->banner_image ?? '', '/') }}" alt="Store Banner" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-black bg-opacity-40 flex items-center justify-center">
                <div class="text-center text-white">
                    <h2 class="text-4xl md:text-6xl font-bold mb-4">{{ $vendor->business_name }}</h2>
                    @if($vendor->description)
                        <p class="text-lg md:text-xl max-w-2xl mx-auto px-4">{{ Str::limit($vendor->description, 150) }}</p>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <div class="container mx-auto px-4 py-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            @if($theme && $theme->is_active && ($theme->show_categories || $theme->show_filters))
                <aside class="md:col-span-1">
                    <div class="bg-white rounded-lg shadow-md p-6 sticky top-24">
                        @if($theme->show_categories && $categories->count() > 0)
                            <h3 class="font-bold text-lg mb-4">Categories</h3>
                            <ul class="space-y-2 mb-6">
                                <li>
                                    <a href="{{ $vendor->storefrontUrl() }}" class="block hover:text-primary transition {{ !request('category') ? 'text-primary font-semibold' : '' }}">
                                        All Products
                                    </a>
                                </li>
                                @foreach($categories as $category)
                                    <li>
                                        <a href="{{ $vendor->storefrontUrl(['category' => $category->id]) }}" class="block hover:text-primary transition {{ request('category') == $category->id ? 'text-primary font-semibold' : '' }}">
                                            {{ $category->name }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        @if($theme->show_filters)
                            <h3 class="font-bold text-lg mb-4">Filter by Price</h3>
                            <form action="{{ $vendor->storefrontUrl() }}" method="GET" class="space-y-3">
                                @if(request('category'))
                                    <input type="hidden" name="category" value="{{ request('category') }}">
                                @endif
                                <input type="number" name="min_price" placeholder="Min Price" value="{{ request('min_price') }}" class="w-full border rounded px-3 py-2">
                                <input type="number" name="max_price" placeholder="Max Price" value="{{ request('max_price') }}" class="w-full border rounded px-3 py-2">
                                <button type="submit" class="w-full btn-primary text-white py-2 rounded hover:opacity-90 transition">Apply</button>
                            </form>
                        @endif

                        <div class="mt-6 pt-6 border-t">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-semibold">Followers</span>
                                <span class="text-primary">{{ $followers }}</span>
                            </div>
                            @auth
                                <form action="{{ route('vendors.follow', $vendor->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full btn-primary text-white py-2 rounded hover:opacity-90 transition">
                                        Follow Store
                                    </button>
                                </form>
                            @endauth
                        </div>

                        @if($theme->about_text)
                            <div class="mt-6 pt-6 border-t">
                                <h3 class="font-bold text-lg mb-2">About Us</h3>
                                <p class="text-sm text-gray-600">{{ Str::limit($theme->about_text, 200) }}</p>
                            </div>
                        @endif

                        @if($theme->contact_info)
                            <div class="mt-6 pt-6 border-t">
                                <h3 class="font-bold text-lg mb-2">Contact</h3>
                                <p class="text-sm text-gray-600 whitespace-pre-line">{{ $theme->contact_info }}</p>
                            </div>
                        @endif

                        @if(!empty($theme->getSocialLinksArray()))
                            <div class="mt-6 pt-6 border-t">
                                <h3 class="font-bold text-lg mb-3">Follow Us</h3>
                                <div class="flex gap-3">
                                    @foreach($theme->getSocialLinksArray() as $platform => $link)
                                        @if($link)
                                            <a href="{{ $link }}" target="_blank" class="text-gray-600 hover:text-primary transition" aria-label="{{ ucfirst($platform) }}">
                                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                                    @if($platform === 'facebook')
                                                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                                    @elseif($platform === 'twitter')
                                                        <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                                                    @elseif($platform === 'instagram')
                                                        <path d="M12 0C8.74 0 8.333.015 7.053.072 5.775.132 4.905.333 4.14.63c-.789.306-1.459.717-2.126 1.384S.935 3.35.63 4.14C.333 4.905.131 5.775.072 7.053.012 8.333 0 8.74 0 12s.015 3.667.072 4.947c.06 1.277.261 2.148.558 2.913.306.788.717 1.459 1.384 2.126.667.666 1.336 1.079 2.126 1.384.766.296 1.636.499 2.913.558C8.333 23.988 8.74 24 12 24s3.667-.015 4.947-.072c1.277-.06 2.148-.262 2.913-.558.788-.306 1.459-.718 2.126-1.384.666-.667 1.079-1.335 1.384-2.126.296-.765.499-1.636.558-2.913.06-1.28.072-1.687.072-4.947s-.015-3.667-.072-4.947c-.06-1.277-.262-2.149-.558-2.913-.306-.789-.718-1.459-1.384-2.126C21.319 1.347 20.651.935 19.86.63c-.765-.297-1.636-.499-2.913-.558C15.667.012 15.26 0 12 0zm0 2.16c3.203 0 3.585.016 4.85.071 1.17.055 1.805.249 2.227.415.562.217.96.477 1.382.896.419.42.679.819.896 1.381.164.422.36 1.057.413 2.227.057 1.266.07 1.646.07 4.85s-.015 3.585-.074 4.85c-.061 1.17-.256 1.805-.421 2.227-.224.562-.479.96-.899 1.382-.419.419-.824.679-1.38.896-.42.164-1.065.36-2.235.413-1.274.057-1.649.07-4.859.07-3.211 0-3.586-.015-4.859-.074-1.171-.061-1.816-.256-2.236-.421-.569-.224-.96-.479-1.379-.899-.421-.419-.69-.824-.9-1.38-.165-.42-.359-1.065-.42-2.235-.045-1.26-.061-1.649-.061-4.844 0-3.196.016-3.586.061-4.861.061-1.17.255-1.814.42-2.234.21-.57.479-.96.9-1.381.419-.419.81-.689 1.379-.898.42-.166 1.051-.361 2.221-.421 1.275-.045 1.65-.06 4.859-.06l.045.03zm0 3.678c-3.405 0-6.162 2.76-6.162 6.162 0 3.405 2.76 6.162 6.162 6.162 3.405 0 6.162-2.76 6.162-6.162 0-3.405-2.76-6.162-6.162-6.162zM12 16c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm7.846-10.405c0 .795-.646 1.44-1.44 1.44-.795 0-1.44-.646-1.44-1.44 0-.794.646-1.439 1.44-1.439.793-.001 1.44.645 1.44 1.439z"/>
                                                    @elseif($platform === 'linkedin')
                                                        <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                                                    @endif
                                                </svg>
                                            </a>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </aside>
            @endif

            <main class="{{ $theme && $theme->is_active && ($theme->show_categories || $theme->show_filters) ? 'md:col-span-3' : 'md:col-span-4' }}">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold">Products ({{ $products->total() }})</h2>
                    <form action="{{ $vendor->storefrontUrl() }}" method="GET" class="flex items-center gap-2">
                        @if(request('category'))
                            <input type="hidden" name="category" value="{{ request('category') }}">
                        @endif
                        @if(request('search'))
                            <input type="hidden" name="search" value="{{ request('search') }}">
                        @endif
                        <select name="sort" onchange="this.form.submit()" class="border rounded px-3 py-2">
                            <option value="">Sort By</option>
                            <option value="price_low" {{ request('sort') === 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                            <option value="price_high" {{ request('sort') === 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                            <option value="popular" {{ request('sort') === 'popular' ? 'selected' : '' }}>Most Popular</option>
                        </select>
                    </form>
                </div>

                @if($products->count() > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 {{ $theme && $theme->layout_style === 'list' ? 'lg:grid-cols-1' : 'lg:grid-cols-3' }} gap-6">
                        @foreach($products as $product)
                            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition {{ $theme && $theme->layout_style === 'list' ? 'flex' : '' }}">
                                <a href="{{ route('shop.show', $product->slug) }}" class="{{ $theme && $theme->layout_style === 'list' ? 'w-1/3' : '' }}">
                                    <img src="{{ $product->images->first() ? '/storage/' . ltrim($product->images->first()->image_path ?? '', '/') : '/images/placeholder.png' }}" alt="{{ $product->name }}" class="w-full {{ $theme && $theme->layout_style === 'list' ? 'h-full' : 'h-48' }} object-cover">
                                </a>
                                <div class="p-4 {{ $theme && $theme->layout_style === 'list' ? 'flex-1' : '' }}">
                                    <a href="{{ route('shop.show', $product->slug) }}" class="font-semibold text-lg hover:text-primary transition">{{ $product->name }}</a>
                                    @if($product->short_description)
                                        <p class="text-sm text-gray-600 mt-2">{{ Str::limit($product->short_description, 80) }}</p>
                                    @endif
                                    <div class="mt-3 flex items-center justify-between">
                                        <span class="text-xl font-bold text-primary">${{ number_format((float) $product->price, 2) }}</span>
                                        @if($product->original_price && $product->original_price > $product->price)
                                            <span class="text-sm text-gray-400 line-through">${{ number_format((float) $product->original_price, 2) }}</span>
                                        @endif
                                    </div>
                                    @if($product->category)
                                        <span class="inline-block mt-2 text-xs text-gray-500">{{ $product->category->name }}</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-8">
                        {{ $products->links() }}
                    </div>
                @else
                    <div class="text-center py-12">
                        <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                        </svg>
                        <h3 class="text-xl font-semibold text-gray-700 mb-2">No Products Found</h3>
                        <p class="text-gray-500">This store hasn't added any products yet.</p>
                    </div>
                @endif
            </main>
        </div>
    </div>

    @include('components.site-footer')

    @php
        try {
            $chatbotEnabled = \App\Models\Setting::getValue('chatbot_enabled', true);
            $chatbotVendorStorefront = \App\Models\Setting::getValue('chatbot_show_on_vendor_storefront', true);
        } catch (\Throwable $e) {
            $chatbotEnabled = true;
            $chatbotVendorStorefront = true;
        }
    @endphp
    @if($chatbotEnabled && $chatbotVendorStorefront)
        @include('components.chatbot-widget')
    @endif

    @if($theme && $theme->is_active && $theme->custom_js)
        <script>{!! $theme->custom_js !!}</script>
    @endif
</body>
</html>

