<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use Illuminate\Http\Request;

class BlogCommentController extends Controller
{
    public function store(BlogPost $post, Request $request)
    {
        $validated = $request->validate([
            'body' => ['required', 'string', 'min:3', 'max:2000'],
        ]);

        $post->comments()->create([
            'user_id' => $request->user()->id,
            'body' => $validated['body'],
        ]);

        return back()->with('success', 'Your comment has been posted.');
    }
}
