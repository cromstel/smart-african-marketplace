@extends('admin.layout')

@section('title', 'Product Attributes')
@section('header', 'Product Attributes')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-3 w-full max-w-4xl">
            <select name="scope" class="bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-white">
                <option value="all" {{ ($filters['scope'] ?? 'all') === 'all' ? 'selected' : '' }}>All scopes</option>
                <option value="global" {{ ($filters['scope'] ?? '') === 'global' ? 'selected' : '' }}>Global only</option>
                <option value="vendor" {{ ($filters['scope'] ?? '') === 'vendor' ? 'selected' : '' }}>Vendor only</option>
            </select>
            <select name="vendor_id" class="bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-white">
                <option value="">All vendors</option>
                @foreach($vendors as $vendor)
                    <option value="{{ $vendor->id }}" {{ (string) ($filters['vendor_id'] ?? '') === (string) $vendor->id ? 'selected' : '' }}>
                        {{ $vendor->business_name ?: $vendor->user?->name }}
                    </option>
                @endforeach
            </select>
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search name or slug" class="bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-white">
            <button type="submit" class="px-4 py-2 rounded bg-gray-600 text-white hover:bg-gray-500">Filter</button>
        </form>

        <a href="{{ route('admin.products.attributes.create') }}" class="ml-4 px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700 whitespace-nowrap">Add attribute</a>
    </div>

    <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-700">
            <thead class="bg-gray-900">
                <tr>
                    <th class="px-4 py-3 text-left text-xs uppercase tracking-wider text-gray-400">Name</th>
                    <th class="px-4 py-3 text-left text-xs uppercase tracking-wider text-gray-400">Slug</th>
                    <th class="px-4 py-3 text-left text-xs uppercase tracking-wider text-gray-400">Owner</th>
                    <th class="px-4 py-3 text-left text-xs uppercase tracking-wider text-gray-400">Archives</th>
                    <th class="px-4 py-3 text-left text-xs uppercase tracking-wider text-gray-400">Sort</th>
                    <th class="px-4 py-3 text-right text-xs uppercase tracking-wider text-gray-400">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                @forelse($attributes as $attribute)
                    <tr>
                        <td class="px-4 py-3 text-white">{{ $attribute->name }}</td>
                        <td class="px-4 py-3 text-gray-300">{{ $attribute->slug }}</td>
                        <td class="px-4 py-3 text-gray-300">
                            @if($attribute->vendor_id)
                                {{ $attribute->vendor?->business_name ?: $attribute->vendor?->user?->name }}
                            @else
                                Global
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-300">{{ $attribute->enable_archives ? 'Enabled' : 'Disabled' }}</td>
                        <td class="px-4 py-3 text-gray-300">{{ str_replace('_', ' ', $attribute->default_sort_order ?? 'custom_ordering') }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="inline-flex items-center gap-2">
                                <a href="{{ route('admin.products.attributes.edit', $attribute) }}" class="text-indigo-400 hover:text-indigo-300">Edit</a>
                                <form method="POST" action="{{ route('admin.products.attributes.destroy', $attribute) }}" onsubmit="return confirm('Delete this attribute?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:text-red-300">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-400">No attributes found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $attributes->links() }}
</div>
@endsection

