@php
    $avatarValue = auth()->user()->avatar;
    $avatarUrl = $avatarValue
        ? (str_starts_with($avatarValue, 'http') ? $avatarValue : asset('storage/' . ltrim($avatarValue, '/')))
        : null;
@endphp

<header>
    <h2 class="text-lg font-medium text-gray-900">
        {{ __('Profile Photo') }}
    </h2>

    <p class="mt-1 text-sm text-gray-600">
        {{ __('Upload a new profile photo (max 50KB).') }}
    </p>
</header>

<form method="post" action="{{ route('profile.avatar.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
    @csrf
    @method('patch')

    <div class="flex items-center gap-4">
        <div class="w-16 h-16 rounded-full bg-gray-100 overflow-hidden flex items-center justify-center">
            @if ($avatarUrl)
                <img src="{{ $avatarUrl }}" alt="{{ auth()->user()->name }} avatar" class="w-full h-full object-cover">
            @else
                <span class="text-xl font-semibold text-gray-500">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
            @endif
        </div>
        <div class="flex-1">
            <x-input-label for="avatar" :value="__('Upload photo')" />
            <input id="avatar" name="avatar" type="file" accept="image/png,image/jpeg,image/webp" class="mt-2 block w-full text-sm text-gray-700" />
            <x-input-error :messages="$errors->updateAvatar->get('avatar')" class="mt-2" />
            <p class="mt-2 text-xs text-gray-500">JPEG, PNG, or WebP. Max size 50KB.</p>
        </div>
    </div>

    <div class="flex items-center gap-4">
        <x-primary-button>{{ __('Update Photo') }}</x-primary-button>

        @if (session('status') === 'avatar-updated')
            <p
                x-data="{ show: true }"
                x-show="show"
                x-transition
                x-init="setTimeout(() => show = false, 2000)"
                class="text-sm text-gray-600"
            >{{ __('Saved.') }}</p>
        @endif
    </div>
</form>

