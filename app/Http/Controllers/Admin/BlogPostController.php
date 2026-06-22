<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogPostRevision;
use App\Models\BlogTag;
use App\Models\MediaLibrary;
use App\Services\BlogAutoGenerationService;
use App\Services\BlogRevisionService;
use App\Services\HtmlSanitizerService;
use App\Services\ImageOptimizationService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BlogPostController extends Controller
{
    public function __construct(
        private readonly ImageOptimizationService $imageService,
        private readonly HtmlSanitizerService $sanitizer,
        private readonly BlogRevisionService $revisionService,
        private readonly BlogAutoGenerationService $blogAutoGeneration,
    ) {
        $this->middleware(['auth', 'admin']);
    }

    public function index()
    {
        $posts = BlogPost::with(['author', 'categories'])
            ->latest()
            ->paginate(20);

        return view('admin.blog.index', compact('posts'));
    }

    public function create()
    {
        $categories = BlogCategory::with('parent')->orderBy('name')->get();
        $tags = BlogTag::orderBy('name')->get();
        $mediaImages = $this->mediaImages();

        return view('admin.blog.create', compact('categories', 'tags', 'mediaImages'));
    }

    public function store(Request $request)
    {
        $validated = $this->validatePost($request);
        $validated['content'] = $this->sanitizer->sanitize($validated['content']);
        $validated['slug'] = $this->uniquePostSlug($validated['slug'] ?? $validated['title']);
        $validated['author_id'] = auth()->id();

        $validated = $this->applyFeaturedImage($request, $validated);

        if ($validated['status'] === 'published' && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        $post = BlogPost::create($this->postPayload($validated));
        $this->syncTaxonomies($post, $validated);

        return redirect()->route('admin.blog.index')->with('success', 'Blog post created successfully');
    }

    public function edit(BlogPost $blog)
    {
        $blog->load(['categories', 'tags', 'revisions.author']);
        $categories = BlogCategory::with('parent')->orderBy('name')->get();
        $tags = BlogTag::orderBy('name')->get();
        $mediaImages = $this->mediaImages();

        return view('admin.blog.edit', compact('blog', 'categories', 'tags', 'mediaImages'));
    }

    public function update(Request $request, BlogPost $blog)
    {
        $validated = $this->validatePost($request, $blog);
        $validated['content'] = $this->sanitizer->sanitize($validated['content']);
        $validated['slug'] = $this->uniquePostSlug($validated['slug'] ?? $validated['title'], $blog->id);

        $this->revisionService->snapshot($blog, auth()->id());

        if ($request->hasFile('featured_image') && $blog->featured_image) {
            Storage::disk('public')->delete($blog->featured_image);
        }

        $validated = $this->applyFeaturedImage($request, $validated);

        if (
            $validated['status'] === 'published'
            && empty($validated['published_at'])
            && empty($blog->published_at)
        ) {
            $validated['published_at'] = now();
        }

        $blog->update($this->postPayload($validated));
        $this->syncTaxonomies($blog, $validated);

        return redirect()->route('admin.blog.index')->with('success', 'Blog post updated successfully');
    }

    public function destroy(BlogPost $blog)
    {
        if ($blog->featured_image) {
            Storage::disk('public')->delete($blog->featured_image);
        }

        $blog->delete();

        return redirect()->route('admin.blog.index')->with('success', 'Blog post deleted successfully');
    }

    public function preview(BlogPost $blog)
    {
        $post = $blog->replicate();
        $post->setAttribute('published_at', $blog->published_at ?? now());
        $post->setAttribute('views', $blog->views ?? 0);

        $relatedPosts = BlogPost::where('status', 'published')
            ->where('id', '!=', $blog->id)
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('pages.blog.show', [
            'post' => $post,
            'relatedPosts' => $relatedPosts,
            'isPreview' => true,
        ]);
    }

    public function previewDraft(Request $request)
    {
        $validated = $this->validatePost($request, null, true);
        $validated['content'] = $this->sanitizer->sanitize($validated['content']);

        $draftId = $request->integer('draft_id');
        $draft = $draftId ? BlogPost::where('id', $draftId)->where('status', 'draft')->first() : null;

        $validated['slug'] = $this->uniquePostSlug($validated['slug'] ?? $validated['title'], $draft?->id);
        $validated['status'] = 'draft';
        $validated['author_id'] = auth()->id();

        $validated = $this->applyFeaturedImage($request, $validated);

        if ($draft) {
            $this->revisionService->snapshot($draft, auth()->id());
            $draft->update($this->postPayload($validated));
            $post = $draft;
        } else {
            $post = BlogPost::create($this->postPayload($validated));
        }

        $this->syncTaxonomies($post, $validated);

        return response()->json([
            'draft_id' => $post->id,
            'preview_url' => route('admin.blog.preview', $post),
        ]);
    }

    public function restoreRevision(BlogPost $blog, BlogPostRevision $revision)
    {
        abort_unless($revision->blog_post_id === $blog->id, 404);

        $this->revisionService->snapshot($blog, auth()->id());

        $blog->update([
            'title' => $revision->title,
            'slug' => $this->uniquePostSlug($revision->slug, $blog->id),
            'excerpt' => $revision->excerpt,
            'content' => $revision->content,
            'meta_title' => $revision->meta_title,
            'meta_description' => $revision->meta_description,
        ]);

        return redirect()->route('admin.blog.edit', $blog)->with('success', 'Revision restored successfully.');
    }

    public function storeInlineCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:blog_categories,name'],
            'parent_id' => ['nullable', 'exists:blog_categories,id'],
        ]);

        $category = BlogCategory::create([
            'name' => $validated['name'],
            'slug' => $this->uniqueBlogCategorySlug($validated['name']),
            'parent_id' => $validated['parent_id'] ?? null,
        ]);

        return response()->json([
            'id' => $category->id,
            'name' => $category->name,
            'path' => $category->fullPath(),
        ]);
    }

    public function storeInlineTag(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:blog_tags,name'],
            'color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        $tag = BlogTag::create([
            'name' => $validated['name'],
            'slug' => $this->uniqueBlogTagSlug($validated['name']),
            'color' => $validated['color'] ?? '#6366f1',
        ]);

        return response()->json([
            'id' => $tag->id,
            'name' => $tag->name,
        ]);
    }

    public function uploadContentImage(Request $request)
    {
        $validated = $request->validate([
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp,avif', 'max:5120'],
        ]);

        $result = $this->imageService->uploadAndOptimize(
            $validated['image'],
            'blog/content',
            1400,
            1000,
            'blog_content',
            auth()->id()
        );

        return response()->json([
            'location' => Storage::url($result['original']),
        ]);
    }

    public function generateContent(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'force_overwrite' => ['nullable', 'boolean'],
        ]);

        return response()->json(
            $this->blogAutoGeneration->generateFromTitle($validated['title'])
        );
    }

    private function validatePost(Request $request, ?BlogPost $post = null, bool $preview = false): array
    {
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'featured_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp,avif', 'max:5120'],
            'featured_image_media_id' => ['nullable', 'integer', 'exists:media_library,id'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'published_at' => ['nullable', 'date'],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['integer', 'exists:blog_categories,id'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['integer', 'exists:blog_tags,id'],
        ];

        if ($preview) {
            // Preview always persists as draft.
            $rules['status'] = ['nullable'];
        }

        return $request->validate($rules);
    }

    private function applyFeaturedImage(Request $request, array $validated): array
    {
        if ($request->hasFile('featured_image')) {
            $result = $this->imageService->uploadAndOptimize(
                $request->file('featured_image'),
                'blog-images',
                1200,
                630,
                'blog_featured',
                auth()->id()
            );
            $validated['featured_image'] = $result['original'];
        } elseif ($request->filled('featured_image_media_id')) {
            $selectedMedia = MediaLibrary::query()
                ->images()
                ->find($request->integer('featured_image_media_id'));

            if ($selectedMedia) {
                $validated['featured_image'] = $selectedMedia->file_path;
            }
        }

        return $validated;
    }

    private function syncTaxonomies(BlogPost $post, array $validated): void
    {
        $post->categories()->sync($validated['categories'] ?? []);
        $post->tags()->sync($validated['tags'] ?? []);
    }

    private function uniquePostSlug(string $value, ?int $ignoreId = null): string
    {
        return $this->resolveUniqueSlug(BlogPost::query(), $value, $ignoreId);
    }

    private function uniqueBlogCategorySlug(string $value, ?int $ignoreId = null): string
    {
        return $this->resolveUniqueSlug(BlogCategory::query(), $value, $ignoreId);
    }

    private function uniqueBlogTagSlug(string $value, ?int $ignoreId = null): string
    {
        return $this->resolveUniqueSlug(BlogTag::query(), $value, $ignoreId);
    }

    private function resolveUniqueSlug($query, string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value);
        $candidate = $base !== '' ? $base : Str::random(8);
        $counter = 1;

        while ($query
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->where('slug', $candidate)
            ->exists()) {
            $candidate = $base.'-'.$counter;
            $counter++;
        }

        return $candidate;
    }

    private function mediaImages()
    {
        return MediaLibrary::query()
            ->images()
            ->latest()
            ->limit(100)
            ->get(['id', 'title', 'original_name', 'file_path']);
    }

    private function postPayload(array $validated): array
    {
        return Arr::only($validated, [
            'title',
            'slug',
            'meta_title',
            'meta_description',
            'excerpt',
            'content',
            'featured_image',
            'author_id',
            'status',
            'published_at',
            'views',
        ]);
    }
}
