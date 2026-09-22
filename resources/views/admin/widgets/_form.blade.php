@php
    $types = ['hero','category-grid','featured-products','blog-posts','cta','custom-html','testimonials','newsletter','faq','brand-carousel','menu'];
@endphp

<div class="bg-gray-800 rounded-lg border border-gray-700 p-6 space-y-4">
    <div>
        <label class="block text-sm font-medium text-gray-300 mb-2">Page Identifier *</label>
        <input type="text" name="page_identifier" value="{{ old('page_identifier', $widget->page_identifier) }}" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2" placeholder="home, shop, blog-index, page-{slug}">
        <p class="text-xs text-gray-400 mt-1">Examples: home, shop, blog-index, page-about</p>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Region *</label>
            <select name="region" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                @foreach(['header','main','footer'] as $region)
                    <option value="{{ $region }}" {{ old('region', $widget->region) === $region ? 'selected' : '' }}>{{ ucfirst($region) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Type *</label>
            <select name="type" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                @foreach($types as $type)
                    <option value="{{ $type }}" {{ old('type', $widget->type) === $type ? 'selected' : '' }}>{{ $type }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Title</label>
            <input type="text" name="title" value="{{ old('title', $widget->title) }}" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Sort Order</label>
            <input type="number" name="sort_order" value="{{ old('sort_order', $widget->sort_order ?? 0) }}" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
        </div>
    </div>

    <div id="menu-builder" class="hidden">
        <label class="block text-sm font-medium text-gray-300 mb-2">Menu Builder</label>
        <div id="menu-items" class="space-y-2">
            @php
                $menuItems = old('settings.items', $widget->settings['items'] ?? []);
            @endphp
            @foreach($menuItems as $index => $item)
                <div class="grid grid-cols-5 gap-2 menu-item-row">
                    <input type="text" name="settings[items][{{ $index }}][label]" value="{{ $item['label'] ?? '' }}" placeholder="Label" class="col-span-2 bg-gray-700 border border-gray-600 text-white rounded px-3 py-2 text-sm">
                    <input type="text" name="settings[items][{{ $index }}][url]" value="{{ $item['url'] ?? '' }}" placeholder="URL" class="col-span-3 bg-gray-700 border border-gray-600 text-white rounded px-3 py-2 text-sm">
                </div>
            @endforeach
        </div>
        <button type="button" id="add-menu-item" class="mt-3 bg-gray-700 hover:bg-gray-600 text-white px-3 py-2 rounded text-sm">Add Menu Item</button>
    </div>

    <div id="settings-json">
        <label class="block text-sm font-medium text-gray-300 mb-2">Settings (JSON)</label>
        <textarea name="settings_json" rows="6" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 font-mono text-sm">{{ old('settings_json', $widget->settings ? json_encode($widget->settings, JSON_PRETTY_PRINT) : '') }}</textarea>
        <p class="text-xs text-gray-400 mt-1">Provide widget settings as JSON. Example: {"title":"Welcome","limit":4}</p>
    </div>

    <div class="flex items-center">
        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $widget->is_active) ? 'checked' : '' }} class="rounded border-gray-600 text-indigo-600 focus:ring-indigo-500">
        <span class="ml-2 text-gray-300">Active</span>
    </div>
</div>

<script>
    const typeSelect = document.querySelector('select[name="type"]');
    const menuBuilder = document.getElementById('menu-builder');
    const settingsJson = document.getElementById('settings-json');
    const menuItems = document.getElementById('menu-items');
    const addMenuItem = document.getElementById('add-menu-item');

    function toggleMenuBuilder() {
        const isMenu = typeSelect.value === 'menu';
        menuBuilder.classList.toggle('hidden', !isMenu);
        settingsJson.classList.toggle('hidden', isMenu);
    }

    function addMenuRow() {
        const index = menuItems.querySelectorAll('.menu-item-row').length;
        const row = document.createElement('div');
        row.className = 'grid grid-cols-5 gap-2 menu-item-row';
        row.innerHTML = `
            <input type="text" name="settings[items][${index}][label]" placeholder="Label" class="col-span-2 bg-gray-700 border border-gray-600 text-white rounded px-3 py-2 text-sm">
            <input type="text" name="settings[items][${index}][url]" placeholder="URL" class="col-span-3 bg-gray-700 border border-gray-600 text-white rounded px-3 py-2 text-sm">
        `;
        menuItems.appendChild(row);
    }

    typeSelect.addEventListener('change', toggleMenuBuilder);
    addMenuItem.addEventListener('click', addMenuRow);
    toggleMenuBuilder();
</script>

