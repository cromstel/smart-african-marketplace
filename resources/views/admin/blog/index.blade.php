@extends('admin.layout')

@section('title', 'Weekly African News')
@section('header', 'Weekly African News')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div>
        <h2 class="text-2xl font-bold text-white">Weekly African News</h2>
        <p class="text-gray-400 mt-1">Create and manage your weekly African news content</p>
    </div>
    <a href="{{ route('admin.blog.create') }}" class="bg-brand-from hover:opacity-90 text-white px-6 py-3 rounded-lg font-medium inline-flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        New Post
    </a>
</div>

<div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-900 border-b border-gray-700">
                <tr>
                    <th class="text-left px-6 py-4 text-sm font-semibold text-gray-300">Title</th>
                    <th class="text-left px-6 py-4 text-sm font-semibold text-gray-300">Author</th>
                    <th class="text-left px-6 py-4 text-sm font-semibold text-gray-300">Categories</th>
                    <th class="text-left px-6 py-4 text-sm font-semibold text-gray-300">Status</th>
                    <th class="text-left px-6 py-4 text-sm font-semibold text-gray-300">Views</th>
                    <th class="text-left px-6 py-4 text-sm font-semibold text-gray-300">Published</th>
                    <th class="text-right px-6 py-4 text-sm font-semibold text-gray-300">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                @forelse($posts as $post)
                    <tr class="hover:bg-gray-750 transition">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                @if($post->featured_image)
                                    <img src="{{ '/storage/' . ltrim($post->featured_image ?? '', '/') }}" alt="{{ $post->title }}" class="w-12 h-12 rounded object-cover mr-3">
                                @else
                                    <div class="w-12 h-12 bg-gray-700 rounded flex items-center justify-center mr-3">
                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                @endif
                                <div>
                                    <p class="text-white font-medium">{{ Str::limit($post->title, 50) }}</p>
                                    <p class="text-gray-400 text-sm">{{ $post->slug }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-gray-300">{{ $post->author->name }}</td>
                        <td class="px-6 py-4">
                            @foreach($post->categories as $category)
                                <span class="inline-block bg-purple-900 text-purple-300 px-2 py-1 rounded text-xs mr-1 mb-1">{{ $category->name }}</span>
                            @endforeach
                        </td>
                        <td class="px-6 py-4">
                            @if($post->status === 'published')
                                <span class="px-3 py-1 text-xs rounded-full bg-green-900 text-green-300 font-semibold">Published</span>
                            @else
                                <span class="px-3 py-1 text-xs rounded-full bg-yellow-900 text-yellow-300 font-semibold">Draft</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-gray-300">{{ number_format($post->views) }}</td>
                        <td class="px-6 py-4 text-gray-300 text-sm">
                            {{ $post->published_at ? $post->published_at->format('M d, Y') : '-' }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('blog.show', $post->slug) }}" target="_blank" class="text-blue-400 hover:text-blue-300 p-2" aria-label="View {{ $post->title }} on site">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                <a href="{{ route('admin.blog.edit', $post) }}" class="text-brand-from hover:text-orange-300 p-2" aria-label="Edit {{ $post->title }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <form action="{{ route('admin.blog.destroy', $post) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this post?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:text-red-300 p-2" aria-label="Delete {{ $post->title }}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <svg class="w-16 h-16 mx-auto text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                            <p class="text-gray-400 text-lg">No blog posts yet</p>
                            <p class="text-gray-400 mt-2">Create your first blog post to get started</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($posts->hasPages())
        <div class="px-6 py-4 border-t border-gray-700">
            {{ $posts->links() }}
        </div>
    @endif
</div>
@endsection

