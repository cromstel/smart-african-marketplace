@extends('layouts.shop')

@section('content')
@php $suppressHeaderWidgets = true; @endphp
<x-hero-section
    pageIdentifier="blog"
    heightPreset="standard"
    fallbackTitle="{{ __('ui.blog.title') }}"
    fallbackSubtitle="{{ __('ui.blog.subtitle') }}"
    fallbackImage="/images/hero-banners/blog-banner.avif"
    :animated="true"
/>

<div class="container mx-auto px-4 py-16">
        @if($posts->count() > 0)
            <div class="grid md:grid-cols-3 gap-8 mb-12">
                @foreach($posts as $post)
                    <article class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition">
                        @if($post->featured_image)
                            <img src="{{ '/storage/' . ltrim($post->featured_image ?? '', '/') }}" alt="{{ $post->title }}" class="w-full h-48 object-cover">
                        @else
                            <div class="w-full h-48 bg-gradient-to-r from-orange-400 to-red-400"></div>
                        @endif
                        
                        <div class="p-6">
                            <h2 class="text-xl font-bold mb-2 text-gray-900 hover:text-orange-600 transition">
                                <a href="{{ route('blog.show', $post->slug) }}">{{ $post->title }}</a>
                            </h2>
                            
                            <div class="flex items-center text-sm text-gray-600 mb-3">
                                <span>{{ $post->published_at->format('M d, Y') }}</span>
                                <span class="mx-2">•</span>
                                <span>{{ $post->views }} {{ __('ui.blog.views') }}</span>
                            </div>
                            
                            @if($post->excerpt)
                                <p class="text-gray-700 mb-4">{{ Str::limit($post->excerpt, 120) }}</p>
                            @endif
                            
                            <a href="{{ route('blog.show', $post->slug) }}" class="text-orange-600 font-semibold hover:text-orange-700">
                                {{ __('ui.blog.read_more') }} →
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $posts->links() }}
            </div>
        @else
            <div class="text-center py-16">
                <p class="text-gray-600 text-lg">{{ __('ui.blog.no_posts') }}</p>
            </div>
        @endif
    </div>
@endsection


