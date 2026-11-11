@extends('layouts.shop')

@section('title', $post->title ?? __('ui.blog.meta_title'))
@section('seoDescription', $post->meta_description ?? $post->excerpt ?? __('ui.blog.meta_description'))
@section('seoKeywords', $post->meta_keywords ?? __('ui.blog.meta_keywords'))

@section('content')
@php
    $suppressHeaderWidgets = true;
    $featuredImageUrl = null;
    if ($post->featured_image) {
        $featuredImageUrl = \Illuminate\Support\Str::startsWith($post->featured_image, ['http://', 'https://', '/'])
            ? $post->featured_image
            : '/storage/' . ltrim($post->featured_image ?? '', '/');
    }
@endphp
<x-hero-section
    pageIdentifier="blog-post"
    heightPreset="standard"
    fallbackTitle="{{ $post->title }}"
    :background-image-url="$featuredImageUrl"
    headingTag="h2"
    title-size-classes="text-3xl sm:text-4xl lg:text-5xl"
    
/>

<div class="container mx-auto px-4 py-8">
    @if(!empty($isPreview))
        <div class="mb-6 rounded-lg border border-orange-200 bg-orange-50 px-4 py-3 text-orange-800">
            {{ __('ui.blog.preview') }}
        </div>
    @endif

    <a href="{{ route('blog.index') }}" class="inline-flex items-center text-orange-600 hover:text-orange-700 mb-6">
        &larr; {{ __('ui.blog.back_to_blog') }}
    </a>

    <article class="bg-white rounded-lg shadow-md overflow-hidden mb-12">
        <div class="p-8 md:p-12">
            <div class="prose max-w-none text-gray-800">
                {!! $post->content !!}
            </div>

            @php
                $publishedDate = $post->published_at?->format('F d, Y');
                $lastUpdatedDate = $post->updated_at?->format('F d, Y') ?? $publishedDate;
            @endphp

            <div class="flex flex-wrap gap-3 text-sm text-gray-600 mt-10 pt-6 border-t border-gray-200">
                <span class="font-semibold">{{ $post->author?->name ?? 'Admin' }}</span>
                <span class="mx-2">&bull;</span>
                @if($publishedDate)
                    <span>{{ __('ui.blog.published') }} {{ $publishedDate }}</span>
                    <span class="mx-2">&bull;</span>
                @endif
                <span>{{ __('ui.blog.updated') }} {{ $lastUpdatedDate }}</span>
                <span class="mx-2">&bull;</span>
                <span>{{ $post->views }} {{ __('ui.blog.views') }}</span>
            </div>
        </div>
    </article>

    <section class="bg-white rounded-lg shadow-md border border-gray-200 p-6 mb-12">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-semibold text-gray-900">{{ __('ui.blog.comments') }} ({{ $comments->count() }})</h2>
            <span class="text-sm text-gray-500">{{ $comments->count() === 0 ? __('ui.blog.no_comments') : __('ui.blog.most_recent') }}</span>
        </div>

        @if($comments->isEmpty())
            <p class="text-gray-600">{{ __('ui.blog.be_first') }}</p>
        @else
            <div class="space-y-4 mb-6">
                @foreach($comments as $comment)
                    <div class="bg-gray-50 rounded-lg border border-gray-200 p-4">
                        <div class="flex items-center justify-between text-sm text-gray-600 mb-2">
                            <span class="font-semibold">{{ $comment->user?->name ?? 'Member' }}</span>
                            <span>{{ $comment->created_at->format('F d, Y') }}</span>
                        </div>
                        <p class="text-gray-800 whitespace-pre-line">{{ $comment->body }}</p>
                    </div>
                @endforeach
            </div>
        @endif

        @auth
            <form method="POST" action="{{ route('blog.comments.store', $post) }}" class="space-y-4">
                @csrf
                <div>
                    <label for="body" class="block text-sm font-medium text-gray-700">{{ __('ui.blog.leave_comment') }}</label>
                    <textarea
                        id="body"
                        name="body"
                        rows="4"
                        class="mt-1 block w-full rounded-lg border border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-600 sm:text-sm"
                        placeholder="{{ __('ui.blog.share_thoughts') }}"
                    >{{ old('body') }}</textarea>
                    @error('body')
                        <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <button
                    type="submit"
                    class="inline-flex items-center px-4 py-2 bg-orange-600 text-white rounded-lg font-semibold shadow-sm hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-600"
                >
                    {{ __('ui.blog.submit_comment') }}
                </button>
            </form>
        @else
            <div class="rounded-lg border border-dashed border-gray-300 p-4 text-sm text-gray-600">
                <p>
                    <a href="{{ route('login') }}" class="text-orange-600 hover:underline">{{ __('ui.blog.login_to_comment') }}</a>
                    {{ __('ui.blog.login_to_comment_suffix') }}
                </p>
            </div>
        @endauth
    </section>

    @if($relatedPosts->count() > 0)
        <div class="mb-12">
            <h2 class="text-2xl md:text-3xl font-bold mb-6 text-gray-900">{{ __('ui.blog.related_posts') }}</h2>
            <div class="grid md:grid-cols-3 gap-6">
                @foreach($relatedPosts as $related)
                    <article class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition">
                        @if($related->featured_image)
                            <img src="{{ '/storage/' . ltrim($related->featured_image ?? '', '/') }}" alt="{{ $related->title }}" class="w-full h-40 object-cover">
                        @else
                            <div class="w-full h-40 bg-gradient-to-r from-orange-400 to-red-400"></div>
                        @endif

                        <div class="p-4">
                            <h3 class="font-bold mb-2 text-gray-900 hover:text-orange-600">
                                <a href="{{ route('blog.show', $related->slug) }}">{{ $related->title }}</a>
                            </h3>
                            <p class="text-sm text-gray-600">{{ $related->published_at->format('M d, Y') }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection

