@extends('vendor.layout')

@section('title', 'Create Product Attribute')
@section('header', 'Create Product Attribute')

@section('content')
<div class="max-w-4xl">
    <form action="{{ route('vendor.attributes.store') }}" method="POST" class="bg-gray-800 rounded-lg shadow p-6 space-y-6">
        @csrf

        <div>
            <h3 class="text-white font-semibold text-lg">Add new attribute</h3>
            <p class="text-sm text-gray-400 mt-1">Attributes let you define extra product data, such as size or color. You can use these attributes in the shop sidebar using layered navigation widgets.</p>
        </div>

        <div>
            <label for="name" class="block text-sm font-medium text-gray-300 mb-2">Name</label>
            <input type="text" name="name" id="name" value="{{ old('name') }}" required class="w-full rounded-lg bg-gray-700 border border-gray-600 text-white px-4 py-2">
            <p class="text-xs text-gray-400 mt-1">Name for the attribute (shown on the front-end).</p>
            @error('name') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="slug" class="block text-sm font-medium text-gray-300 mb-2">Slug</label>
            <input type="text" name="slug" id="slug" maxlength="28" value="{{ old('slug') }}" class="w-full rounded-lg bg-gray-700 border border-gray-600 text-white px-4 py-2" placeholder="auto-generated-if-empty">
            <p class="text-xs text-gray-400 mt-1">Unique slug/reference for the attribute; must be no more than 28 characters.</p>
            @error('slug') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center">
            <input type="checkbox" name="enable_archives" id="enable_archives" value="1" {{ old('enable_archives') ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-600 bg-gray-700 text-indigo-600">
            <label for="enable_archives" class="ml-2 text-sm text-gray-300">Enable Archives?</label>
        </div>
        <p class="-mt-4 text-xs text-gray-400">Enable this if you want this attribute to have product archives in your store.</p>

        <div>
            <label for="default_sort_order" class="block text-sm font-medium text-gray-300 mb-2">Default sort order</label>
            <select id="default_sort_order" name="default_sort_order" class="w-full rounded-lg bg-gray-700 border border-gray-600 text-white px-4 py-2">
                @php $sort = old('default_sort_order', 'custom_ordering'); @endphp
                <option value="custom_ordering" {{ $sort === 'custom_ordering' ? 'selected' : '' }}>Custom ordering</option>
                <option value="name" {{ $sort === 'name' ? 'selected' : '' }}>Name</option>
                <option value="name_num" {{ $sort === 'name_num' ? 'selected' : '' }}>Name (numeric)</option>
                <option value="term_id" {{ $sort === 'term_id' ? 'selected' : '' }}>Term ID</option>
            </select>
            <p class="text-xs text-gray-400 mt-1">Determines sort order of terms on frontend product pages.</p>
            @error('default_sort_order') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="border-t border-gray-700 pt-5">
            <h4 class="text-sm font-semibold text-gray-200 mb-4">Advanced Options</h4>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-300 mb-2">Description</label>
                <textarea name="description" id="description" rows="3" class="w-full rounded-lg bg-gray-700 border border-gray-600 text-white px-4 py-2">{{ old('description') }}</textarea>
                @error('description') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-300 mb-2">Attribute Type</label>
                    <select name="type" id="type" class="w-full rounded-lg bg-gray-700 border border-gray-600 text-white px-4 py-2">
                        <option value="text" {{ old('type') === 'text' ? 'selected' : '' }}>Text</option>
                        <option value="select" {{ old('type') === 'select' ? 'selected' : '' }}>Select (Dropdown)</option>
                        <option value="color" {{ old('type') === 'color' ? 'selected' : '' }}>Color Picker</option>
                        <option value="boolean" {{ old('type') === 'boolean' ? 'selected' : '' }}>Boolean (Yes/No)</option>
                    </select>
                    @error('type') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="sort_order" class="block text-sm font-medium text-gray-300 mb-2">Manual Sort Priority</label>
                    <input type="number" min="0" name="sort_order" id="sort_order" value="{{ old('sort_order', 0) }}" class="w-full rounded-lg bg-gray-700 border border-gray-600 text-white px-4 py-2">
                </div>
            </div>

            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-300 mb-2">Attribute Values</label>
                <div id="values-display" class="flex flex-wrap gap-2 mb-2"></div>
                <div class="flex gap-2">
                    <input type="text" id="values-input" placeholder="Enter a value and press Enter" class="flex-1 rounded-lg bg-gray-700 border border-gray-600 text-white px-4 py-2">
                    <button type="button" id="add-value" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Add</button>
                </div>
                <p class="text-xs text-gray-400 mt-1">For select/color types, add possible values (e.g., Small, Medium, Large).</p>
            </div>

            <div class="mt-4 flex flex-wrap gap-6">
                <label class="inline-flex items-center">
                    <input type="checkbox" name="is_filterable" id="is_filterable" value="1" {{ old('is_filterable', true) ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-600 bg-gray-700 text-indigo-600">
                    <span class="ml-2 text-sm text-gray-300">Use in layered navigation (shop filters)</span>
                </label>

                <label class="inline-flex items-center">
                    <input type="checkbox" name="is_visible" id="is_visible" value="1" {{ old('is_visible', true) ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-600 bg-gray-700 text-indigo-600">
                    <span class="ml-2 text-sm text-gray-300">Visible on product page</span>
                </label>
            </div>
        </div>

        <div class="flex items-center space-x-4">
            <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">Add attribute</button>
            <a href="{{ route('vendor.attributes.index') }}" class="px-6 py-2 bg-gray-700 text-gray-300 rounded-lg hover:bg-gray-600 transition">Cancel</a>
        </div>
    </form>
</div>

<script>
let values = @json(old('values', []));

function renderValues() {
    const display = document.getElementById('values-display');
    display.innerHTML = values.map((value, index) => `
        <span class="inline-flex items-center px-3 py-1 rounded-full bg-indigo-900 text-indigo-200 text-sm">
            ${value}
            <button type="button" onclick="removeValue(${index})" class="ml-2 text-indigo-300 hover:text-white">&times;</button>
            <input type="hidden" name="values[]" value="${value}">
        </span>
    `).join('');
}

function addValue() {
    const input = document.getElementById('values-input');
    const value = input.value.trim();
    if (!value || values.includes(value)) return;
    values.push(value);
    input.value = '';
    renderValues();
}

function removeValue(index) {
    values.splice(index, 1);
    renderValues();
}

document.getElementById('add-value').addEventListener('click', addValue);
document.getElementById('values-input').addEventListener('keypress', (e) => {
    if (e.key === 'Enter') {
        e.preventDefault();
        addValue();
    }
});

renderValues();
</script>
@endsection

