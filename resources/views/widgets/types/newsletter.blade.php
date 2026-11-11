@php
    $settings = $widget->settings ?? [];
@endphp
<section class="py-10 bg-gray-900 text-white">
    <div class="max-w-[1600px] mx-auto px-4 text-center">
        <h3 class="text-2xl font-bold">{{ $settings['title'] ?? $widget->title ?? 'Newsletter' }}</h3>
        <p class="text-gray-300 mt-2">{{ $settings['text'] ?? 'Get updates and offers.' }}</p>
        <form action="{{ route('newsletter.subscribe') }}" method="POST" class="mt-4 flex flex-col sm:flex-row gap-2 justify-center">
            @csrf
            <input type="email" name="email" required placeholder="Your email" class="px-4 py-2 rounded text-gray-900">
            <button type="submit" class="bg-orange-600 px-5 py-2 rounded font-semibold">Subscribe</button>
        </form>
    </div>
</section>

