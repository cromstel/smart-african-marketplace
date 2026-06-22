@extends('vendor.layout')

@section('title', 'Edit Salon')
@section('header', 'Edit Salon')

@section('content')
<div class="max-w-3xl mx-auto bg-gray-800 rounded-lg border border-gray-700 p-8">
    <form action="{{ route('vendor.salon.update', $salon) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-gray-400 mb-2">Salon Name</label>
                <input type="text" name="name" value="{{ old('name', $salon->name) }}" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-white" required>
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-2">Email</label>
                <input type="email" name="email" value="{{ old('email', $salon->email) }}" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-white">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-gray-400 mb-2">Phone</label>
                <input type="text" name="phone" value="{{ old('phone', $salon->phone) }}" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-white">
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-2">Address</label>
                <input type="text" name="address" value="{{ old('address', $salon->address) }}" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-white">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm text-gray-400 mb-2">City</label>
                <input type="text" name="city" value="{{ old('city', $salon->city) }}" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-white">
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-2">State</label>
                <input type="text" name="state" value="{{ old('state', $salon->state) }}" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-white">
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-2">Country</label>
                <input type="text" name="country" value="{{ old('country', $salon->country) }}" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-white">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm text-gray-400 mb-2">Postal Code</label>
                <input type="text" name="postal_code" value="{{ old('postal_code', $salon->postal_code) }}" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-white">
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-2">Latitude</label>
                <input type="text" name="latitude" value="{{ old('latitude', $salon->latitude) }}" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-white">
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-2">Longitude</label>
                <input type="text" name="longitude" value="{{ old('longitude', $salon->longitude) }}" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-white">
            </div>
        </div>

        <div>
            <label class="block text-sm text-gray-400 mb-2">Description</label>
            <textarea name="description" rows="4" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-white">{{ old('description', $salon->description) }}</textarea>
        </div>

        <div class="flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1" class="rounded border-gray-600 bg-gray-900" {{ $salon->is_active ? 'checked' : '' }}>
            <span class="text-sm text-gray-300">Active</span>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('vendor.salon.index') }}" class="bg-gray-700 text-white px-4 py-2 rounded-lg">Cancel</a>
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg">Update</button>
        </div>
    </form>
</div>
@endsection

