@extends('layouts.salon-console')

@section('title', 'Profile - Salon Console')

@section('content')
<div class="max-w-lg mx-auto">
    <h2 class="text-lg font-semibold text-white mb-6">Profile Settings</h2>

    <form method="POST" action="{{ route('salon.profile.update') }}" class="bg-gray-800 border border-gray-700 rounded-lg p-6">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-300 mb-2">Full Name</label>
            <input type="text" name="name" value="{{ old('name', $user->name ?? '') }}" required
                   class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-300 mb-2">Email Address</label>
            <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}" required
                   class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-300 mb-2">Phone Number</label>
            <input type="tel" name="phone" value="{{ old('phone', $user->phone ?? '') }}"
                   class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
        </div>

        <div class="flex justify-end">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded text-sm font-medium transition-colors">
                Save Changes
            </button>
        </div>
    </form>
</div>
@endsection
