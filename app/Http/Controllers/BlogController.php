<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Services\SeoService;

class BlogController extends Controller
{
    public function index()
    {
        $posts = BlogPost::where('status', 'published')
            ->where('published_at', '<=', now())
            ->latest('published_at')
            ->paginate(12);

        $seo = app(SeoService::class);
        $seo->setTitle('Blog - African Culture, Food & Lifestyle')
            ->setDescription('Explore articles about African culture, traditional foods, fashion, beauty, and lifestyle from across the continent.')
            ->setKeywords(['african blog', 'african culture', 'african food', 'african lifestyle', 'african fashion'])
            ->setCanonical(route('blog.index'));

        return view('pages.blog.index', compact('posts'));
    }

    public function show(BlogPost $post)
    {
        if ($post->status !== 'published' || $post->published_at > now()) {
            abort(404);
        }

        $post->increment('views');

        $relatedPosts = BlogPost::where('status', 'published')
            ->where('id', '!=', $post->id)
            ->latest('published_at')
            ->take(3)
            ->get();

        $comments = $post->comments()->with('user')->get();

        $seo = app(SeoService::class);
        $seo->setTitle($post->meta_title ?: $post->title)
            ->setDescription($post->meta_description ?: ($post->excerpt ?? substr(strip_tags($post->content), 0, 160)))
            ->setKeywords([$post->title, 'african blog', 'african culture'])
            ->setCanonical(route('blog.show', $post->slug))
            ->setType('article');

        if ($post->featured_image) {
            $seo->setImage(asset('storage/'.$post->featured_image));
        }

        $seo->addStructuredData($seo->generateArticleSchema($post));

        $breadcrumbs = [
            ['name' => 'Home', 'url' => route('home')],
            ['name' => 'Blog', 'url' => route('blog.index')],
            ['name' => $post->title, 'url' => route('blog.show', $post->slug)],
        ];
        $seo->addStructuredData($seo->generateBreadcrumbSchema($breadcrumbs));

        return view('pages.blog.show', compact('post', 'relatedPosts', 'comments'));
    }
}
