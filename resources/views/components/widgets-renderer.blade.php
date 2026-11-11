@php
    use App\Models\Widget;

    $pageKey = $page ?? 'home';
    $pageKeys = [$pageKey];
    if (str_starts_with($pageKey, 'page-')) {
        $pageKeys[] = 'page-*';
    }

    $widgets = Widget::query()
        ->whereIn('page_identifier', $pageKeys)
        ->where('region', $region ?? 'main')
        ->where('is_active', true)
        ->orderBy('sort_order')
        ->get();
@endphp

@foreach($widgets as $widget)
    @includeIf('widgets.types.' . $widget->type, ['widget' => $widget])
@endforeach

