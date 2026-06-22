@php
    $customItems = $items ?? null;
    $routeName = request()->route()?->getName();
    $hide = ($hideBreadcrumbs ?? false) || in_array($routeName, ['home', null], true);

    $segments = collect(request()->segments())
        ->reject(fn ($segment) => in_array($segment, ['admin', 'vendor'], true))
        ->values();

    $generated = collect([
        ['label' => 'Home', 'url' => route('home')],
    ]);

    $path = '';
    foreach ($segments as $segment) {
        $path .= '/'.$segment;
        $generated->push([
            'label' => ucwords(str_replace(['-', '_'], ' ', $segment)),
            'url' => url($path),
        ]);
    }

    $trail = collect($customItems ?: $generated)->values();
@endphp

@if(! $hide && $trail->count() > 1)
    <nav aria-label="Breadcrumb" class="site-container px-4 sm:px-6 lg:px-8 py-3">
        <ol class="flex flex-wrap items-center gap-2 text-sm text-gray-600">
            @foreach($trail as $index => $crumb)
                @php
                    $isLast = $index === $trail->count() - 1;
                    $label = $crumb['label'] ?? '';
                    $url = $crumb['url'] ?? null;
                @endphp
                <li class="inline-flex items-center gap-2">
                    @if(! $isLast && $url)
                        <a href="{{ $url }}" class="hover:text-orange-600 transition-colors">{{ $label }}</a>
                    @else
                        <span class="font-medium text-gray-800">{{ $label }}</span>
                    @endif
                    @if(! $isLast)
                        <span aria-hidden="true" class="text-gray-400">/</span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif


