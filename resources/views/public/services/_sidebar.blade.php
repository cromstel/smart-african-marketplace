@props(['parentCategories', 'currentCategorySlug', 'currentRouteName', 'filterParams'])

<div class="hidden lg:flex lg:flex-col lg:w-72 lg:sticky lg:top-24 order-first lg:order-first">
    <!-- Sidebar Container -->
    <div class="space-y-6">
        
        <!-- Search Section -->
        <div class="bg-gray-800 border border-gray-700 rounded-xl p-5 shadow-lg">
            <form method="GET" action="{{ route('services.index') }}" class="space-y-3">
                @csrf
                
                <!-- Search -->
                <div class="relative">
                    <label for="search-input" class="sr-only">Search services</label>
                    <input type="text" id="search-input" name="search" 
                           value="{{ $filterParams['search'] ?? '' }}"
                           placeholder="Search services..."
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-3 pl-11 text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors">
                    <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </div>

                <!-- Filters Row -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="category-type-select" class="sr-only">Category Type</label>
                        <select id="category-type-select" name="category_type" 
                                class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors">
                            <option value="">All Categories</option>
                            @foreach($filterParams['categoryTypes'] as $type)
                                <option value="{{ $type }}" 
                                        {{ ($filterParams['category_type'] ?? '') === $type ? 'selected' : '' }}>
                                    {{ ucwords(str_replace('_', ' ', $type)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="gender-target-select" class="sr-only">Gender Target</label>
                        <select id="gender-target-select" name="gender_target" 
                                class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors">
                            <option value="">All Genders</option>
                            @foreach($filterParams['genderTargets'] as $gender)
                                <option value="{{ $gender }}" 
                                        {{ ($filterParams['gender_target'] ?? '') === $gender ? 'selected' : '' }}>
                                    {{ ucfirst($gender) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <button type="submit" 
                        class="w-full bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white py-3 rounded-lg font-semibold transition-all shadow-lg shadow-indigo-500/20 flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Apply Filters
                </button>

                @if($filterParams['search'] ?? null || $filterParams['category_type'] ?? null || $filterParams['gender_target'] ?? null)
                    <a href="{{ route('services.index') }}" 
                       class="w-full mt-2 text-center text-xs text-gray-400 hover:text-white transition-colors underline underline-offset-4">
                        Clear Filters
                    </a>
                @endif
            </form>
        </div>

        <!-- Category Tree Section -->
        <div class="bg-gray-800 border border-gray-700 rounded-xl p-5 shadow-lg">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-lg font-semibold text-white">Service Categories</h3>
                <span class="text-xs text-gray-500 uppercase tracking-wider font-medium">{{ $parentCategories->count() }} categories</span>
            </div>

            <nav aria-label="Service categories" class="space-y-2">
                @foreach($parentCategories as $parent)
                    @php
                        $isParentActive = $currentCategorySlug === $parent->slug || 
                                         (request()->routeIs('services.category') && 
                                          str_starts_with(request()->route('category'), $parent->slug));
                    @endphp
                    
                    <!-- Parent Category -->
                    <div class="group">
                        <a href="{{ route('services.category', $parent->slug) }}"
                           class="flex items-center justify-between w-full px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
                           {{ $isParentActive ? 'bg-indigo-600/20 text-white border border-indigo-500/50' : 'text-gray-300 hover:text-white hover:bg-gray-700/50' }}"
                           {{ $isParentActive ? 'aria-current="page"' : '' }}>
                            <span class="flex-1">{{ $parent->name }}</span>
                            <span class="text-xs {{ $isParentActive ? 'text-indigo-400' : 'text-gray-500' }}">
                                {{ $parent->children->count() }}
                            </span>
                        </a>
                        
                        @if($parent->children->count() > 0)
                            <div class="mt-1 space-y-0 ml-4 border-l-2 border-gray-700 pl-4 
                                {{ $isParentActive ? '' : 'hidden' }}">
                                @foreach($parent->children as $child)
                                    @php
                                        $isChildActive = $currentCategorySlug === $child->slug;
                                        $hasServices = $child->vendorServices()->count() > 0;
                                    @endphp
                                    
                                    <a href="{{ $hasServices ? route('services.category', $child->slug) : '#' }}"
                                       class="block px-2 py-1.5 rounded text-xs font-medium transition-colors duration-200
                                       {{ $isChildActive ? 'bg-indigo-600/20 text-indigo-300' : 'text-gray-400 hover:text-white hover:bg-gray-700/50' }}
                                       {{ $hasServices ? '' : 'opacity-40 cursor-not-allowed' }}
                                       {{ !$hasServices ? 'tabindex="-1" aria-disabled="true"' : '' }}"
                                        {{ !$hasServices ? 'onclick="return false;"' : '' }}>
                                        {{ $child->name }}
                                        @if(!$hasServices)
                                            <span class="text-[10px] text-gray-600 ml-1">Coming Soon</span>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </nav>
        </div>

        <!-- Quick Stats -->
        <div class="bg-gray-900/50 border border-gray-700 rounded-xl p-4">
            <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Popular Categories</h4>
            <div class="space-y-2">
                @php
                    $popularCategories = $parentCategories->take(4);
                @endphp
                @foreach($popularCategories as $cat)
                    <div class="flex items-center justify-between p-2 bg-gray-800/50 rounded-lg">
                        <span class="text-sm text-gray-300">{{ $cat->name }}</span>
                        <span class="text-xs font-bold text-indigo-400">{{ $cat->children->sum(fn($c) => $c->vendorServices()->count()) }} services</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
