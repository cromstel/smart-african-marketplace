@extends('admin.layout')

@section('title', 'Add Tag')
@section('header', 'Add Tag')

@section('content')
<div class="max-w-3xl">
    <form action="{{ route('admin.products.tags.store') }}" method="POST" class="bg-gray-800 rounded-lg border border-gray-700 p-6 space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Name *</label>
            <input type="text" name="name" value="{{ old('name') }}" required class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
            @error('name')<p class="text-red-400 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Slug (optional)</label>
            <input type="text" name="slug" value="{{ old('slug') }}" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
            @error('slug')<p class="text-red-400 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Color</label>
            <div class="flex items-center gap-3">
                <input type="color" name="color" value="{{ old('color', '#3b82f6') }}" class="h-10 w-14 bg-gray-700 border border-gray-600 rounded">
                <input type="text" name="color_text" value="{{ old('color', '#3b82f6') }}" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2" disabled>
            </div>
            @error('color')<p class="text-red-400 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div class="flex justify-end gap-4 pt-2">
            <a href="{{ route('admin.products.tags.index') }}" class="bg-gray-700 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-medium">Cancel</a>
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-medium">Create Tag</button>
        </div>
    </form>
</div>
@endsection

