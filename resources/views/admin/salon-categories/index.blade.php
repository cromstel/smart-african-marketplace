@extends('admin.layout')

@section('title', 'Salon Service Categories')
@section('header', 'Salon Service Categories')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div>
        <h2 class="text-2xl font-bold text-white">Salon Service Categories</h2>
        <p class="text-gray-400 mt-1">Manage predefined categories for salon services</p>
    </div>
    <a href="{{ route('admin.salon-categories.create') }}" class="bg-brand-from hover:opacity-90 text-white px-6 py-3 rounded-lg font-medium inline-flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        New Category
    </a>
</div>

<div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-900 border-b border-gray-700">
                <tr>
                    <th class="text-left px-6 py-4 text-sm font-semibold text-gray-300">Category</th>
                    <th class="text-left px-6 py-4 text-sm font-semibold text-gray-300">Slug</th>
                    <th class="text-left px-6 py-4 text-sm font-semibold text-gray-300">Description</th>
                    <th class="text-left px-6 py-4 text-sm font-semibold text-gray-300">Services</th>
                    <th class="text-left px-6 py-4 text-sm font-semibold text-gray-300">Order</th>
                    <th class="text-left px-6 py-4 text-sm font-semibold text-gray-300">Status</th>
                    <th class="text-right px-6 py-4 text-sm font-semibold text-gray-300">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                @forelse($categories as $category)
                    <tr class="hover:bg-gray-750 transition">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                @if($category->icon)
                                    <span class="text-2xl mr-3">{{ $category->icon }}</span>
                                @else
                                    <div class="w-10 h-10 bg-gray-700 rounded-lg flex items-center justify-center mr-3">
                                        <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                        </svg>
                                    </div>
                                @endif
                                <div>
                                    <p class="text-white font-medium">{{ $category->name }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <code class="text-purple-400 bg-gray-900 px-2 py-1 rounded text-sm">{{ $category->slug }}</code>
                        </td>
                        <td class="px-6 py-4 text-gray-300">
                            {{ Str::limit($category->description, 50) ?: '-' }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 text-xs rounded-full bg-blue-900 text-blue-300 font-semibold">
                                {{ $category->services_count }} {{ Str::plural('service', $category->services_count) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-300">
                            {{ $category->sort_order }}
                        </td>
                        <td class="px-6 py-4">
                            <form action="{{ route('admin.salon-categories.toggle', $category) }}" method="POST" class="inline">
                                @csrf
                                @if($category->is_active)
                                    <span class="px-3 py-1 text-xs rounded-full bg-green-900 text-green-300 font-semibold cursor-pointer" onclick="this.closest('form').submit()">Active</span>
                                @else
                                    <span class="px-3 py-1 text-xs rounded-full bg-gray-700 text-gray-400 font-semibold cursor-pointer" onclick="this.closest('form').submit()">Inactive</span>
                                @endif
                            </form>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.salon-categories.edit', $category) }}" class="text-brand-from hover:text-orange-300 p-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <form action="{{ route('admin.salon-categories.destroy', $category) }}" method="POST" class="inline" 
                                      onsubmit="return confirm('Are you sure? This will only work if there are no services in this category.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:text-red-300 p-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <svg class="w-16 h-16 mx-auto text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                            <p class="text-gray-400 text-lg">No salon service categories yet</p>
                            <p class="text-gray-500 mt-2">Create predefined categories to organize salon services</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($categories->hasPages())
        <div class="px-6 py-4 border-t border-gray-700">
            {{ $categories->links() }}
        </div>
    @endif
</div>
@endsection

