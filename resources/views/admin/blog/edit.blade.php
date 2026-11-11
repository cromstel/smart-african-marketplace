@extends('admin.layout')

@section('title', 'Edit Blog Post')
@section('header', 'Edit Blog Post')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.blog.index') }}" class="text-brand-from hover:text-orange-300 inline-flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Back to Blog Posts
    </a>
</div>

<form id="blog-form" action="{{ route('admin.blog.update', $blog) }}" method="POST" enctype="multipart/form-data" class="bg-gray-800 rounded-xl border border-gray-700 p-6">
    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Title *</label>
                <div class="flex gap-2">
                    <input type="text" id="post-title" name="title" value="{{ old('title', $blog->title) }}" required class="flex-1 bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-3">
                    <button type="button" id="generate-content-btn" class="px-4 py-3 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium whitespace-nowrap">Generate SEO + Content</button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Permalink / Slug</label>
                    <input type="text" name="slug" value="{{ old('slug', $blog->slug) }}" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Meta Title</label>
                    <input type="text" name="meta_title" value="{{ old('meta_title', $blog->meta_title) }}" maxlength="255" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Meta Description</label>
                <textarea name="meta_description" rows="2" maxlength="500" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">{{ old('meta_description', $blog->meta_description) }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Excerpt</label>
                <textarea name="excerpt" rows="3" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-3">{{ old('excerpt', $blog->excerpt) }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Content *</label>
                <textarea id="content-editor" name="content" rows="18" required class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-3">{{ old('content', $blog->content) }}</textarea>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-gray-900 rounded-lg p-4 border border-gray-700">
                <h3 class="text-white font-semibold mb-4">Publish Settings</h3>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-300 mb-2">Status *</label>
                    <select name="status" required class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                        <option value="draft" {{ old('status', $blog->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ old('status', $blog->status) === 'published' ? 'selected' : '' }}>Published</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Publish Date</label>
                    <input type="datetime-local" name="published_at" value="{{ old('published_at', $blog->published_at?->format('Y-m-d\TH:i')) }}" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                </div>
            </div>

            <div class="bg-gray-900 rounded-lg p-4 border border-gray-700">
                <h3 class="text-white font-semibold mb-4">Featured Image</h3>
                @php
                    $initialFeaturedImage = old('featured_image_media_id')
                        ? optional($mediaImages->firstWhere('id', (int) old('featured_image_media_id')))->file_path
                        : $blog->featured_image;
                @endphp
                <img id="featured-image-preview" src="{{ $initialFeaturedImage ? '/storage/' . ltrim($initialFeaturedImage ?? '', '/') : '' }}" alt="Current featured image" class="{{ $initialFeaturedImage ? '' : 'hidden' }} w-full h-40 object-cover rounded-lg mb-3 border border-gray-700">
                <input type="file" id="featured-image-input" name="featured_image" accept="image/*" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-300 mb-2">Or Replace With Media Library Image</label>
                    <select id="featured-image-media-id" name="featured_image_media_id" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                        <option value="">Keep current image</option>
                        @foreach($mediaImages as $mediaImage)
                            <option value="{{ $mediaImage->id }}" data-image-url="{{ '/storage/' . ltrim($mediaImage->file_path ?? '', '/') }}" {{ old('featured_image_media_id') == $mediaImage->id ? 'selected' : '' }}>
                                {{ $mediaImage->title ?: $mediaImage->original_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="bg-gray-900 rounded-lg p-4 border border-gray-700">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-white font-semibold">Categories</h3>
                    <button type="button" id="add-blog-category" class="text-xs px-3 py-1 rounded bg-indigo-600 text-white">+ Add</button>
                </div>
                <div class="space-y-2 max-h-48 overflow-y-auto" id="categories-list">
                    @php $selectedCategories = old('categories', $blog->categories->pluck('id')->toArray()); @endphp
                    @foreach($categories as $category)
                        @php $level = substr_count($category->fullPath(), ' > '); @endphp
                        <label class="flex items-center">
                            <input type="checkbox" name="categories[]" value="{{ $category->id }}" {{ in_array($category->id, $selectedCategories) ? 'checked' : '' }} class="w-4 h-4 text-brand-from bg-gray-700 border-gray-600 rounded">
                            <span class="ml-2 text-gray-300 text-sm" style="padding-left: {{ $level * 12 }}px;">{{ $category->fullPath() }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="bg-gray-900 rounded-lg p-4 border border-gray-700">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-white font-semibold">Tags</h3>
                    <button type="button" id="add-blog-tag" class="text-xs px-3 py-1 rounded bg-indigo-600 text-white">+ Add</button>
                </div>
                <div class="space-y-2 max-h-48 overflow-y-auto" id="tags-list">
                    @php $selectedTags = old('tags', $blog->tags->pluck('id')->toArray()); @endphp
                    @foreach($tags as $tag)
                        <label class="flex items-center">
                            <input type="checkbox" name="tags[]" value="{{ $tag->id }}" {{ in_array($tag->id, $selectedTags) ? 'checked' : '' }} class="w-4 h-4 text-brand-from bg-gray-700 border-gray-600 rounded">
                            <span class="ml-2 text-gray-300 text-sm">{{ $tag->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="bg-gray-900 rounded-lg p-4 border border-gray-700">
                <h3 class="text-white font-semibold mb-4">Revisions</h3>
                <div class="space-y-2 max-h-56 overflow-y-auto">
                    @forelse($blog->revisions as $revision)
                        <div class="border border-gray-700 rounded p-3">
                            <div class="text-xs text-gray-400 mb-2">{{ $revision->created_at->format('M d, Y H:i') }} by {{ $revision->author?->name ?? 'System' }}</div>
                            <form method="POST" action="{{ route('admin.blog.revisions.restore', [$blog, $revision]) }}">
                                @csrf
                                <button type="submit" class="text-xs px-2 py-1 rounded bg-gray-700 text-gray-200 hover:bg-gray-600">Restore</button>
                            </form>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400">No revisions yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="flex justify-end gap-4 mt-6 pt-6 border-t border-gray-700">
        <a href="{{ route('admin.blog.index') }}" class="px-6 py-3 border border-gray-600 text-gray-300 rounded-lg hover:bg-gray-700 transition">Cancel</a>
        <a href="{{ route('admin.blog.preview', $blog) }}" target="_blank" class="px-6 py-3 border border-gray-600 text-gray-300 rounded-lg hover:bg-gray-700 transition">Preview</a>
        <button type="submit" class="bg-brand-from hover:opacity-90 text-white px-6 py-3 rounded-lg font-medium">Update Post</button>
    </div>
</form>

@vite('resources/js/blog-editor.js')
<script>
const csrfToken = '{{ csrf_token() }}';
const postTitleInput = document.getElementById('post-title');
const generateButton = document.getElementById('generate-content-btn');
const metaTitleInput = document.querySelector('input[name="meta_title"]');
const metaDescriptionInput = document.querySelector('textarea[name="meta_description"]');
const contentInput = document.getElementById('content-editor');
const featuredImageInput = document.getElementById('featured-image-input');
const featuredMediaSelect = document.getElementById('featured-image-media-id');
const featuredImagePreview = document.getElementById('featured-image-preview');

let touchedFields = {
    metaTitle: false,
    metaDescription: false,
    content: false,
};

metaTitleInput?.addEventListener('input', () => { touchedFields.metaTitle = true; });
metaDescriptionInput?.addEventListener('input', () => { touchedFields.metaDescription = true; });
contentInput?.addEventListener('input', () => { touchedFields.content = true; });

async function generateSeoAndContent() {
    const title = postTitleInput?.value?.trim();
    if (!title) {
        alert('Enter a post title first.');
        return;
    }

    const hasManualChanges =
        touchedFields.metaTitle ||
        touchedFields.metaDescription ||
        touchedFields.content ||
        (metaTitleInput?.value || '').trim() !== '' ||
        (metaDescriptionInput?.value || '').trim() !== '' ||
        ((window.tinymce && tinymce.get('content-editor'))
            ? tinymce.get('content-editor').getContent({ format: 'text' }).trim() !== ''
            : (contentInput?.value || '').trim() !== '');

    if (hasManualChanges) {
        const confirmed = confirm('This will overwrite current SEO title, SEO description, and content. Continue?');
        if (!confirmed) {
            return;
        }
    }

    const response = await fetch('{{ route('admin.blog.generate-content') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ title, force_overwrite: true }),
    });

    if (!response.ok) {
        alert('Generation failed. Please try again.');
        return;
    }

    const payload = await response.json();
    metaTitleInput.value = payload.meta_title || '';
    metaDescriptionInput.value = payload.meta_description || '';

    if (window.tinymce && tinymce.get('content-editor')) {
        tinymce.get('content-editor').setContent(payload.content || '');
    } else {
        contentInput.value = payload.content || '';
    }

    touchedFields = { metaTitle: false, metaDescription: false, content: false };
}

function previewFeaturedFromFile(file) {
    if (!file) return;
    const reader = new FileReader();
    reader.onload = (e) => {
        featuredImagePreview.src = e.target?.result || '';
        featuredImagePreview.classList.remove('hidden');
    };
    reader.readAsDataURL(file);
}

function previewFeaturedFromMediaSelect() {
    const option = featuredMediaSelect.options[featuredMediaSelect.selectedIndex];
    const imageUrl = option?.dataset?.imageUrl || '';
    if (!imageUrl) {
        return;
    }
    featuredImagePreview.src = imageUrl;
    featuredImagePreview.classList.remove('hidden');
}

generateButton?.addEventListener('click', generateSeoAndContent);
featuredImageInput?.addEventListener('change', (event) => {
    const file = event.target?.files?.[0];
    if (file) {
        previewFeaturedFromFile(file);
    }
});
featuredMediaSelect?.addEventListener('change', previewFeaturedFromMediaSelect);
window.initAdminBlogEditor({
    selector: '#content-editor',
    uploadUrl: '{{ route('admin.blog.content-image.upload') }}',
    csrfToken,
    onChange: () => { touchedFields.content = true; },
});

async function createInlineCategory() {
    const name = prompt('New category name');
    if (!name) return;

    const response = await fetch('{{ route('admin.blog.inline-category.store') }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ name })
    });

    if (!response.ok) {
        alert('Could not create category.');
        return;
    }

    const category = await response.json();
    const wrapper = document.createElement('label');
    wrapper.className = 'flex items-center';
    wrapper.innerHTML = `<input type="checkbox" name="categories[]" value="${category.id}" checked class="w-4 h-4 text-brand-from bg-gray-700 border-gray-600 rounded"><span class="ml-2 text-gray-300 text-sm">${category.path}</span>`;
    document.getElementById('categories-list').appendChild(wrapper);
}

async function createInlineTag() {
    const name = prompt('New tag name');
    if (!name) return;

    const response = await fetch('{{ route('admin.blog.inline-tag.store') }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ name })
    });

    if (!response.ok) {
        alert('Could not create tag.');
        return;
    }

    const tag = await response.json();
    const wrapper = document.createElement('label');
    wrapper.className = 'flex items-center';
    wrapper.innerHTML = `<input type="checkbox" name="tags[]" value="${tag.id}" checked class="w-4 h-4 text-brand-from bg-gray-700 border-gray-600 rounded"><span class="ml-2 text-gray-300 text-sm">${tag.name}</span>`;
    document.getElementById('tags-list').appendChild(wrapper);
}

document.getElementById('add-blog-category').addEventListener('click', createInlineCategory);
document.getElementById('add-blog-tag').addEventListener('click', createInlineTag);
</script>
@endsection

