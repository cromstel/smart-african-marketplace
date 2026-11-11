@php
    $isEdit = isset($attribute);
@endphp

<div class="space-y-6">
    <div>
        <h3 class="text-white font-semibold text-lg">{{ $isEdit ? 'Edit attribute' : 'Add new attribute' }}</h3>
        <p class="text-sm text-gray-400 mt-1">Attributes let you define extra product data, such as size or color. You can use these attributes in the shop sidebar using layered navigation widgets.</p>
    </div>

    <div>
        <label for="name" class="block text-sm font-medium text-gray-300 mb-2">Name</label>
        <input type="text" id="name" name="name" value="{{ old('name', $attribute->name ?? '') }}" required class="w-full rounded-lg bg-gray-700 border border-gray-600 text-white px-4 py-2">
        <p class="text-xs text-gray-400 mt-1">Name for the attribute (shown on the front-end).</p>
        @error('name') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="slug" class="block text-sm font-medium text-gray-300 mb-2">Slug</label>
        <input type="text" id="slug" name="slug" maxlength="28" value="{{ old('slug', $attribute->slug ?? '') }}" class="w-full rounded-lg bg-gray-700 border border-gray-600 text-white px-4 py-2" placeholder="auto-generated-if-empty">
        <p class="text-xs text-gray-400 mt-1">Unique slug/reference for the attribute; must be no more than 28 characters.</p>
        @error('slug') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
    </div>

    <div class="flex items-center">
        <input type="checkbox" id="enable_archives" name="enable_archives" value="1" {{ old('enable_archives', $attribute->enable_archives ?? false) ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-600 bg-gray-700 text-indigo-600">
        <label for="enable_archives" class="ml-2 text-sm text-gray-300">Enable Archives?</label>
    </div>
    <p class="-mt-4 text-xs text-gray-400">Enable this if you want this attribute to have product archives in your store.</p>

    <div>
        <label for="default_sort_order" class="block text-sm font-medium text-gray-300 mb-2">Default sort order</label>
        <select id="default_sort_order" name="default_sort_order" class="w-full rounded-lg bg-gray-700 border border-gray-600 text-white px-4 py-2">
            @php $sort = old('default_sort_order', $attribute->default_sort_order ?? 'custom_ordering'); @endphp
            <option value="custom_ordering" {{ $sort === 'custom_ordering' ? 'selected' : '' }}>Custom ordering</option>
            <option value="name" {{ $sort === 'name' ? 'selected' : '' }}>Name</option>
            <option value="name_num" {{ $sort === 'name_num' ? 'selected' : '' }}>Name (numeric)</option>
            <option value="term_id" {{ $sort === 'term_id' ? 'selected' : '' }}>Term ID</option>
        </select>
        <p class="text-xs text-gray-400 mt-1">Determines the sort order of terms on frontend product pages. If using custom ordering, you can drag and drop terms in this attribute.</p>
        @error('default_sort_order') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
    </div>

    <div class="border-t border-gray-700 pt-5">
        <h4 class="text-sm font-semibold text-gray-200 mb-4">Advanced Options</h4>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="type" class="block text-sm font-medium text-gray-300 mb-2">Attribute Type</label>
                @php $type = old('type', $attribute->type ?? 'text'); @endphp
                <select name="type" id="type" class="w-full rounded-lg bg-gray-700 border border-gray-600 text-white px-4 py-2">
                    <option value="text" {{ $type === 'text' ? 'selected' : '' }}>Text</option>
                    <option value="select" {{ $type === 'select' ? 'selected' : '' }}>Select (Dropdown)</option>
                    <option value="color" {{ $type === 'color' ? 'selected' : '' }}>Color Picker</option>
                    <option value="boolean" {{ $type === 'boolean' ? 'selected' : '' }}>Boolean (Yes/No)</option>
                    <option value="checkbox" {{ $type === 'checkbox' ? 'selected' : '' }}>Checkbox</option>
                </select>
                @error('type') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="sort_order" class="block text-sm font-medium text-gray-300 mb-2">Manual Sort Priority</label>
                <input type="number" min="0" name="sort_order" id="sort_order" value="{{ old('sort_order', $attribute->sort_order ?? 0) }}" class="w-full rounded-lg bg-gray-700 border border-gray-600 text-white px-4 py-2">
                @error('sort_order') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="mt-4">
            <label class="block text-sm font-medium text-gray-300 mb-2">Description</label>
            <textarea name="description" rows="3" class="w-full rounded-lg bg-gray-700 border border-gray-600 text-white px-4 py-2">{{ old('description', $attribute->description ?? '') }}</textarea>
            @error('description') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="mt-4">
            <label class="block text-sm font-medium text-gray-300 mb-2">Attribute Values</label>
            <div id="values-display" class="flex flex-wrap gap-2 mb-2"></div>
            <div class="flex gap-2">
                <input type="text" id="values-input" class="flex-1 rounded-lg bg-gray-700 border border-gray-600 text-white px-4 py-2" placeholder="Enter a value and press Enter">
                <button type="button" id="add-value" class="px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">Add</button>
            </div>
            <p class="text-xs text-gray-400 mt-1">For select/color types, add possible values (e.g., Small, Medium, Large).</p>
        </div>

        <div class="mt-4 flex flex-wrap gap-6">
            <label class="inline-flex items-center">
                <input type="checkbox" name="is_filterable" value="1" {{ old('is_filterable', $attribute->is_filterable ?? true) ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-600 bg-gray-700 text-indigo-600">
                <span class="ml-2 text-sm text-gray-300">Use in layered navigation (shop filters)</span>
            </label>

            <label class="inline-flex items-center">
                <input type="checkbox" name="is_visible" value="1" {{ old('is_visible', $attribute->is_visible ?? true) ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-600 bg-gray-700 text-indigo-600">
                <span class="ml-2 text-sm text-gray-300">Visible on product page</span>
            </label>
        </div>
    </div>
</div>

@php
    $initialValues = old('values', $attribute->values ?? []);
@endphp
<script>
(() => {
    let values = @json($initialValues);
    const valuesDisplay = document.getElementById('values-display');
    const valuesInput = document.getElementById('values-input');
    const addValueBtn = document.getElementById('add-value');

    function renderValues() {
        valuesDisplay.innerHTML = values.map((value, index) => `
            <span class="inline-flex items-center px-3 py-1 rounded-full bg-indigo-900 text-indigo-200 text-sm">
                ${value}
                <button type="button" data-index="${index}" class="ml-2 text-indigo-300 hover:text-white">&times;</button>
                <input type="hidden" name="values[]" value="${value}">
            </span>
        `).join('');

        valuesDisplay.querySelectorAll('button[data-index]').forEach((button) => {
            button.addEventListener('click', () => {
                const index = Number(button.getAttribute('data-index'));
                values.splice(index, 1);
                renderValues();
            });
        });
    }

    function addValue() {
        const value = valuesInput.value.trim();
        if (!value || values.includes(value)) return;
        values.push(value);
        valuesInput.value = '';
        renderValues();
    }

    addValueBtn.addEventListener('click', addValue);
    valuesInput.addEventListener('keypress', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            addValue();
        }
    });

    renderValues();
})();
</script>

