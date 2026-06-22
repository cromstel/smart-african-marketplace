@extends('vendor.layout')

@section('title', 'Product Attributes')
@section('header', 'Manage Product Attributes')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <p class="text-gray-400">Define extra product data like size, color, material. Use these in layered navigation.</p>
        <a href="{{ route('vendor.attributes.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
            Add New Attribute
        </a>
    </div>

    <div class="bg-gray-800 rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-700">
            <thead class="bg-gray-900">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Name</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Type</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Values</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Filterable</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Scope</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-300 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-gray-800 divide-y divide-gray-700">
                @forelse($attributes as $attribute)
                    <tr>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-white">{{ $attribute->name }}</div>
                            @if($attribute->description)
                                <div class="text-sm text-gray-400">{{ Str::limit($attribute->description, 50) }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-700 text-gray-300">
                                {{ ucfirst($attribute->type) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if($attribute->values)
                                <div class="flex flex-wrap gap-1">
                                    @foreach(array_slice($attribute->values, 0, 3) as $value)
                                        <span class="px-2 py-1 text-xs rounded bg-gray-700 text-gray-300">{{ $value }}</span>
                                    @endforeach
                                    @if(count($attribute->values) > 3)
                                        <span class="px-2 py-1 text-xs rounded bg-gray-700 text-gray-400">+{{ count($attribute->values) - 3 }}</span>
                                    @endif
                                </div>
                            @else
                                <span class="text-sm text-gray-500">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($attribute->is_filterable)
                                <span class="text-green-400">✓</span>
                            @else
                                <span class="text-gray-600">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($attribute->vendor_id)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-indigo-900 text-indigo-200">Your Attribute</span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-700 text-gray-300">Global</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            @if($attribute->vendor_id)
                                <div class="flex items-center justify-end space-x-2">
                                    <a href="{{ route('vendor.attributes.edit', $attribute) }}" class="text-indigo-400 hover:text-indigo-300">Edit</a>
                                    <form action="{{ route('vendor.attributes.destroy', $attribute) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-400 hover:text-red-300">Delete</button>
                                    </form>
                                </div>
                            @else
                                <span class="text-gray-600">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                            <svg class="mx-auto h-12 w-12 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                            </svg>
                            <p class="mt-4">No attributes found. Create your first attribute to add extra product data.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($attributes->hasPages())
        <div class="mt-4">
            {{ $attributes->links() }}
        </div>
    @endif
</div>
@endsection

