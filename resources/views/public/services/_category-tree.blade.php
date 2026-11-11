@props(['parentCategories', 'currentRoute'])

<div class="space-y-4">
    @foreach($parentCategories as $parent)
        <div class="overflow-hidden">
            <div class="flex items-center justify-between px-3 py-2 rounded-lg cursor-pointer group
                @php
                    $isActive = $currentRoute === 'services.category' && request()->route('category') === $parent->slug;
                @endphp
                {{ $isActive ? 'bg-indigo-600/20 border border-indigo-500/50' : 'hover:bg-gray-700/50' }}
                {{ $isActive ? 'text-white' : 'text-gray-300' }}
            ">
                <a href="{{ route('services.category', $parent->slug) }}"
                   class="block w-full text-lg font-medium">
                    {{ $parent->name }}
                </a>

                @if($parent->children->count() > 0)
                    <button type="button" class="ml-2 text-gray-400 hover:text-indigo-400 transition-colors"
                            aria-label="Toggle {{ $parent->name }} subcategories">
                        <svg class="w-4 h-4 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                @endif
            </div>

            @if($parent->children->count() > 0)
                <div class="mt-1 space-y-1 ml-4 border-l-2 border-gray-700 pl-4
                    @php
                        $isActive = $currentRoute === 'services.category' && request()->route('category') === $parent->slug;
                    @endphp
                    {{ $isActive ? '' : 'hidden' }}">
                    @foreach($parent->children as $child)
                        <a href="{{ route('services.category', $child->slug) }}"
                           class="block px-2 py-1.5 rounded text-sm
                           @php
                               $childActive = $currentRoute === 'services.category' && request()->route('category') === $child->slug;
                           @endphp
                           {{ $childActive ? 'bg-indigo-600/50 text-indigo-300' : 'text-gray-400 hover:text-white hover:bg-gray-700' }}
                           {{ $child->vendorServices()->count() > 0 ? '' : 'opacity-50' cursor-not-allowed' }}">
                            {{ $child->name }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    @endforeach
</div>
