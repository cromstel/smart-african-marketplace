@extends('layouts.shop')

@section('title', 'Account Settings - Smart African Market')
@section('seoDescription', 'Manage your profile information and account security on Smart African Market.')
@section('seoKeywords', 'account settings, profile, security, African Market')

@section('content')
<div class="min-h-screen bg-gray-50 py-6 lg:py-12">
    <div class="container mx-auto px-4">
        <div class="mb-8">
            <h2 class="text-2xl lg:text-3xl font-bold text-gray-900">Account Settings</h2>
            <p class="text-gray-600 mt-1">Manage your profile information and account security</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6 sticky top-4">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Quick Links</h3>
                    <nav class="space-y-2">
                        <a href="#profile-summary" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6a4 4 0 100 8 4 4 0 000-8zM4 20a8 8 0 0116 0"/>
                            </svg>
                            Profile Summary
                        </a>
                        <a href="#profile-photo" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h3l2-2h6l2 2h3v12H4V7z"/>
                                <circle cx="12" cy="13" r="3" stroke-width="2" />
                            </svg>
                            Profile Photo
                        </a>
                        <a href="#profile-info" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Profile Information
                        </a>
                        <a href="#password" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            Change Password
                        </a>
                        <a href="#delete-account" class="flex items-center px-4 py-2 text-red-600 hover:bg-red-50 rounded-lg transition">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Delete Account
                        </a>
                        <hr class="my-4">
                        <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Back to Dashboard
                        </a>
                    </nav>
                </div>
            </div>

            <div class="lg:col-span-3 space-y-6">
                <div id="profile-summary" class="bg-white rounded-lg shadow-md p-6 lg:p-8">
                    @php
                        $avatarValue = $user->avatar ?? null;
                        $avatarUrl = $avatarValue
                            ? (str_starts_with($avatarValue, 'http') ? $avatarValue : asset('storage/' . ltrim($avatarValue, '/')))
                            : null;
                    @endphp

                    <header class="mb-6">
                        <h2 class="text-lg font-medium text-gray-900">Profile Summary</h2>
                        <p class="mt-1 text-sm text-gray-600">Quick view of your account details.</p>
                    </header>

                    <div class="flex flex-col sm:flex-row sm:items-center gap-6">
                        <div class="w-20 h-20 rounded-full bg-gray-100 overflow-hidden flex items-center justify-center">
                            @if ($avatarUrl)
                                <img src="{{ $avatarUrl }}" alt="{{ $user->name }} avatar" class="w-full h-full object-cover">
                            @else
                                <span class="text-2xl font-semibold text-gray-500">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                            @endif
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 flex-1">
                            <div>
                                <div class="text-xs uppercase tracking-wide text-gray-500">Name</div>
                                <div class="text-sm font-semibold text-gray-900">{{ $user->name }}</div>
                            </div>
                            <div>
                                <div class="text-xs uppercase tracking-wide text-gray-500">Email</div>
                                <div class="text-sm font-semibold text-gray-900">{{ $user->email }}</div>
                            </div>
                            <div>
                                <div class="text-xs uppercase tracking-wide text-gray-500">Phone</div>
                                <div class="text-sm font-semibold text-gray-900">{{ $user->phone ?? 'Not set' }}</div>
                            </div>
                            <div>
                                <div class="text-xs uppercase tracking-wide text-gray-500">Role</div>
                                <div class="text-sm font-semibold text-gray-900">{{ ucfirst($user->role ?? 'user') }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="profile-photo" class="bg-white rounded-lg shadow-md p-6 lg:p-8">
                    @include('profile.partials.update-avatar-form')
                </div>

                <div id="profile-info" class="bg-white rounded-lg shadow-md p-6 lg:p-8">
                    @include('profile.partials.update-profile-information-form')
                </div>

                <div id="password" class="bg-white rounded-lg shadow-md p-6 lg:p-8">
                    @include('profile.partials.update-password-form')
                </div>

                <div id="delete-account" class="bg-white rounded-lg shadow-md p-6 lg:p-8">
                    @include('profile.partials.delete-user-form')
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

