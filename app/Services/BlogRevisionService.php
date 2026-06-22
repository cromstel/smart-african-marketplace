<?php

namespace App\Services;

use App\Models\BlogPost;

class BlogRevisionService
{
    public function snapshot(BlogPost $post, ?int $userId): void
    {
        $post->revisions()->create([
            'user_id' => $userId,
            'title' => $post->title,
            'slug' => $post->slug,
            'excerpt' => $post->excerpt,
            'content' => $post->content,
            'meta_title' => $post->meta_title,
            'meta_description' => $post->meta_description,
        ]);
    }
}
