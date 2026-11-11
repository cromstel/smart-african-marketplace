@extends('admin.layout')

@section('title', 'Product Categories')
@section('header', 'Product Categories')

@php
    $buildQuery = function (array $overrides = []) {
        $query = request()->query();

        foreach ($overrides as $key => $value) {
            if ($value === null || $value === '') {
                unset($query[$key]);
            } else {
                $query[$key] = $value;
            }
        }

        return route('admin.categories.index', $query);
    };
@endphp

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h3 class="text-2xl font-bold text-white">Product Categories</h3>
            <p class="text-gray-400 mt-1">Two-panel woocommerce category manager with quick add and advanced list controls.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
        <div class="xl:col-span-4">
            <div class="bg-gray-800 rounded-lg border border-gray-700 p-6 sticky top-6">
                <h4 class="text-lg font-semibold text-white mb-1">Add New Category</h4>
                <p class="text-xs text-gray-400 mb-5">Create top-level categories or subcategories directly from this screen.</p>

                <form method="POST" action="{{ route('admin.categories.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label for="category_name" class="block text-sm font-medium text-gray-300 mb-2">Name <span class="text-red-400">*</span></label>
                        <input
                            id="category_name"
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            required
                            class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2.5"
                            placeholder="Category name">
                        @error('name')
                            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="parent_id" class="block text-sm font-medium text-gray-300 mb-2">Parent Category</label>
                        <select id="parent_id" name="parent_id" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2.5">
                            <option value="">None (Top Level)</option>
                            @foreach($allCategories as $parentCategory)
                                <option value="{{ $parentCategory->id }}" {{ (string) old('parent_id') === (string) $parentCategory->id ? 'selected' : '' }}>
                                    {{ $parentCategory->fullPath() }}
                                </option>
                            @endforeach
                        </select>
                        @error('parent_id')
                            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="category_description" class="block text-sm font-medium text-gray-300 mb-2">Description</label>
                        <textarea
                            id="category_description"
                            name="description"
                            rows="4"
                            class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2.5"
                            placeholder="Optional category description">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg">
                            Add Category
                        </button>
                        <a href="{{ route('admin.categories.create') }}" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                            Full Form
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="xl:col-span-8 space-y-4">
            <div class="flex flex-wrap items-center gap-4 text-sm">
                <a href="{{ $buildQuery(['status' => 'all']) }}" class="{{ $status === 'all' ? 'text-white font-semibold underline' : 'text-gray-300 hover:text-white' }}">
                    All ({{ $counts['all'] ?? 0 }})
                </a>
                <a href="{{ $buildQuery(['status' => 'active']) }}" class="{{ $status === 'active' ? 'text-green-300 font-semibold underline' : 'text-green-400 hover:text-green-300' }}">
                    Active ({{ $counts['active'] ?? 0 }})
                </a>
                <a href="{{ $buildQuery(['status' => 'inactive']) }}" class="{{ $status === 'inactive' ? 'text-yellow-300 font-semibold underline' : 'text-yellow-400 hover:text-yellow-300' }}">
                    Inactive ({{ $counts['inactive'] ?? 0 }})
                </a>
            </div>

            <form id="categoryFilterForm" method="GET" action="{{ route('admin.categories.index') }}" class="hidden">
                <input type="hidden" name="status" value="{{ $status }}">
            </form>

            <div class="bg-gray-800 rounded-lg border border-gray-700 p-4">
                <div class="flex flex-wrap items-center gap-3">
                    <select name="action" form="bulkCategoryForm" required class="w-full md:w-44 bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                        <option value="">Bulk actions</option>
                        <option value="activate">Activate</option>
                        <option value="deactivate">Deactivate</option>
                        <option value="delete">Delete</option>
                    </select>
                    <button type="submit" form="bulkCategoryForm" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg" onclick="return confirm('Apply action to selected categories?')">Apply</button>

                    <input type="text" name="q" form="categoryFilterForm" value="{{ $search }}" placeholder="Search categories..." class="w-full md:w-56 bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">

                    <select name="parent" form="categoryFilterForm" class="w-full md:w-44 bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                        <option value="">All Hierarchies</option>
                        <option value="top_level" {{ $parentFilter === 'top_level' ? 'selected' : '' }}>Top Level</option>
                        <option value="subcategories" {{ $parentFilter === 'subcategories' ? 'selected' : '' }}>Subcategories</option>
                    </select>

                    <select name="date_sort" form="categoryFilterForm" class="w-full md:w-40 bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                        <option value="newest" {{ $dateSort === 'newest' ? 'selected' : '' }}>Newest</option>
                        <option value="oldest" {{ $dateSort === 'oldest' ? 'selected' : '' }}>Oldest</option>
                    </select>

                    <button type="submit" form="categoryFilterForm" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">Filter</button>
                    <a href="{{ route('admin.categories.index') }}" class="bg-gray-900 hover:bg-gray-700 text-gray-200 px-4 py-2 rounded-lg">Reset</a>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.categories.bulk') }}" id="bulkCategoryForm">
                @csrf
            </form>

            <div class="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-700">
                            <thead class="bg-gray-900">
                                <tr>
                                    <th class="px-4 py-3 text-left"><input type="checkbox" id="selectAllCategories" class="rounded border-gray-600 bg-gray-700"></th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Name</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Description</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Parent</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Products</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Date</th>
                                </tr>
                            </thead>
                        <tbody class="divide-y divide-gray-700">
                                @forelse($categories as $category)
                                    <tr class="group hover:bg-gray-700/30">
                                        <td class="px-4 py-3 align-top">
                                            <input type="checkbox" name="category_ids[]" value="{{ $category->id }}" form="bulkCategoryForm" class="category-checkbox rounded border-gray-600 bg-gray-700">
                                        </td>
                                        <td class="px-4 py-3 align-top">
                                            <div class="text-white font-medium">{{ $category->name }}</div>
                                            <div class="text-xs text-gray-400">{{ $category->slug }}</div>
                                            <div class="text-xs text-gray-400 mt-1 md:opacity-0 md:group-hover:opacity-100 transition-opacity">
                                                <a href="{{ route('admin.categories.edit', $category) }}" class="text-blue-400 hover:text-blue-300">Edit</a>
                                                <span class="mx-1 text-gray-600">|</span>
                                                <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this category?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-400 hover:text-red-300">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 align-top text-sm text-gray-300">
                                            {{ \Illuminate\Support\Str::limit($category->description ?: '-', 80) }}
                                        </td>
                                        <td class="px-4 py-3 align-top text-sm text-gray-300">{{ $category->parent?->name ?? '-' }}</td>
                                        <td class="px-4 py-3 align-top text-sm text-gray-300">{{ $category->products_count }}</td>
                                        <td class="px-4 py-3 align-top">
                                            <span class="px-2 py-1 rounded-full text-xs {{ $category->is_active ? 'bg-green-900 text-green-200' : 'bg-gray-700 text-gray-300' }}">
                                                {{ $category->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 align-top text-sm text-gray-300">{{ optional($category->created_at)->format('M d, Y') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-8 text-center text-gray-400">No categories found.</td>
                                    </tr>
                                @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div>
                {{ $categories->links() }}
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectAll = document.getElementById('selectAllCategories');
    const checkboxes = document.querySelectorAll('.category-checkbox');

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            checkboxes.forEach(function (checkbox) {
                checkbox.checked = selectAll.checked;
            });
        });
    }
});
</script>
@endsection

