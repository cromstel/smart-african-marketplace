@props(['categoryTypes', 'genderTargets', 'currentCategoryType', 'currentGenderTarget'])

<form method="GET" class="space-y-4">
    @csrf

    <div>
        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Category Type</label>
        <select name="category_type"
                class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors">
            <option value="">All Categories</option>
            @foreach($categoryTypes as $type)
                <option value="{{ $type }}"
                        {{ $currentCategoryType === $type ? 'selected' : '' }}>
                    {{ ucwords(str_replace('_', ' ', $type)) }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Gender</label>
        <select name="gender_target"
                class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors">
            <option value="">All Genders</option>
            @foreach($genderTargets as $gender)
                <option value="{{ $gender }}"
                        {{ $currentGenderTarget === $gender ? 'selected' : '' }}>
                    {{ ucfirst($gender) }}
                </option>
            @endforeach
        </select>
    </div>

    <button type="submit"
            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded-lg text-sm font-medium transition-colors flex items-center justify-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        Filter
    </button>
</form>
