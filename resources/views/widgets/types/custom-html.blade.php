@php
    $settings = $widget->settings ?? [];
@endphp
<section class="py-8">
    <div class="max-w-[1600px] mx-auto px-4">
        {!! $settings['html'] ?? '' !!}
    </div>
</section>

