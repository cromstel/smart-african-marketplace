@php
    $settings = $widget->settings ?? [];
    $limit = (int) ($settings['limit'] ?? 3);
    $posts = \App\Models\BlogPost::where('status', 'published')->orderByDesc('published_at')->limit($limit)->get();
@endphp
<section class="py-12 bg-gray-50">
    <div class="max-w-[1600px] mx-auto px-4">
        <h3 class="text-2xl font-bold mb-6">{{ $settings['title'] ?? $widget->title ?? 'Latest Posts' }}</h3>
        <div class="grid md:grid-cols-3 gap-6">
            @foreach($posts as $post)
                <a href="{{ route('blog.show', $post) }}" class="bg-white rounded-lg p-5 shadow-sm hover:shadow-md transition">
                    <div class="font-semibold text-gray-800 mb-2">{{ $post->title }}</div>
                    <div class="text-sm text-gray-500">{{ \Illuminate\Support\Str::limit(strip_tags($post->excerpt ?? $post->content), 120) }}</div>
                </a>
            @endforeach
        </div>
    </div>
</section>

