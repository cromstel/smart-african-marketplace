@extends('vendor.layout')

@section('title', 'Add Salon Service')
@section('header', 'Add Salon Service')

@section('content')
<div class="max-w-4xl">
    <form action="{{ route('vendor.salon-services.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h2 class="text-xl font-bold text-white mb-4">Service Information</h2>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Service Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                           placeholder="Hair Cut & Styling">
                    @error('name') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Description *</label>
                    <textarea name="description" rows="5" required
                              class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                              placeholder="Detailed description of the service...">{{ old('description') }}</textarea>
                    @error('description') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Category *</label>
                        <select name="category_id" required class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Service Type</label>
                        <select name="salon_category_id" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                            <option value="">Select Type</option>
                            @foreach($salonCategories as $salonCategory)
                                <option value="{{ $salonCategory->id }}" {{ old('salon_category_id') == $salonCategory->id ? 'selected' : '' }}>
                                    {{ $salonCategory->icon }} {{ $salonCategory->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('salon_category_id') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                        <p class="text-xs text-gray-500 mt-1">Optional predefined type</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Price * ($)</label>
                        <input type="number" name="price" value="{{ old('price') }}" required min="0" step="0.01"
                               class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                               placeholder="50.00">
                        @error('price') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Duration * (minutes)</label>
                        <input type="number" name="duration_minutes" value="{{ old('duration_minutes', 60) }}" required min="15" step="15"
                               class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                               placeholder="60">
                        <p class="text-xs text-gray-500 mt-1">Minimum 15 minutes</p>
                        @error('duration_minutes') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Salon</label>
                        <select name="salon_id" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                            <option value="">Select Salon</option>
                            @foreach($salons as $salon)
                                <option value="{{ $salon->id }}" {{ old('salon_id') == $salon->id ? 'selected' : '' }}>
                                    {{ $salon->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('salon_id') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Stylist</label>
                        <select name="stylist_id" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                            <option value="">Select Stylist</option>
                            @foreach($stylists as $stylist)
                                <option value="{{ $stylist->id }}" {{ old('stylist_id') == $stylist->id ? 'selected' : '' }}>
                                    {{ $stylist->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('stylist_id') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Service Image</label>
                    <input type="file" name="image" accept="image/*"
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                    <p class="text-xs text-gray-500 mt-1">Recommended size: 800x600px (JPG, PNG, max 2MB)</p>
                    @error('image') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                           class="w-4 h-4 text-indigo-600 bg-gray-700 border-gray-600 rounded focus:ring-indigo-500">
                    <label for="is_active" class="ml-2 text-sm text-gray-300">Active (available for booking)</label>
                </div>
            </div>
        </div>

        <div class="flex gap-4">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-medium">
                Create Service
            </button>
            <a href="{{ route('vendor.salon-services.index') }}" class="bg-gray-700 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-medium">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection

