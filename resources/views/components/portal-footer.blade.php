@php
    $containerWidth = max(1024, min(1600, (int) \App\Models\Setting::get('theme_container_width', 1600)));
@endphp

<footer class="border-t border-gray-700 bg-gray-800/80 mt-auto">
    <div class="mx-auto px-4 lg:px-6 py-3 flex flex-col gap-1 text-left sm:flex-row sm:items-center sm:justify-between text-xs text-gray-400" style="max-width: {{ $containerWidth }}px;">
        <p>&copy; 2025-{{ date('Y') }} {{ config('app.name', 'Smart African Marketplace') }}. All Rights Reserved.</p>
        <p>Portal Interface</p>
    </div>
</footer>

