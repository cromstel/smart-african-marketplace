@extends('admin.layout')

@section('title', 'Edit Stylist')
@section('header', 'Edit Stylist')

@section('content')
<div class="max-w-3xl mx-auto bg-gray-800 rounded-lg border border-gray-700 p-8">
    <form action="{{ route('admin.salon-stylists.update', $salonStylist) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm text-gray-400 mb-2">Salon</label>
            <select name="salon_id" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-white">
                <option value="">No salon</option>
                @foreach($salons as $salon)
                    <option value="{{ $salon->id }}" {{ $salonStylist->salon_id === $salon->id ? 'selected' : '' }}>
                        {{ $salon->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-gray-400 mb-2">Name</label>
                <input type="text" name="name" value="{{ old('name', $salonStylist->name) }}" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-white" required>
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-2">Email</label>
                <input type="email" name="email" value="{{ old('email', $salonStylist->email) }}" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-white">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-gray-400 mb-2">Phone</label>
                <input type="text" name="phone" value="{{ old('phone', $salonStylist->phone) }}" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-white">
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-2">Years of Experience</label>
                <input type="number" name="years_experience" value="{{ old('years_experience', $salonStylist->years_experience) }}" min="0" max="60" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-white">
            </div>
        </div>

        <div>
            <label class="block text-sm text-gray-400 mb-2">Bio</label>
            <textarea name="bio" rows="4" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-white">{{ old('bio', $salonStylist->bio) }}</textarea>
        </div>

        <div class="flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1" class="rounded border-gray-600 bg-gray-900" {{ $salonStylist->is_active ? 'checked' : '' }}>
            <span class="text-sm text-gray-300">Active</span>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.salon-stylists.index') }}" class="bg-gray-700 text-white px-4 py-2 rounded-lg">Cancel</a>
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg">Update</button>
        </div>
    </form>
</div>
@endsection

