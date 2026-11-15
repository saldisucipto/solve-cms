<?php

namespace App\Http\Controllers\Frontend;

use App\Events\CommentSubmitted;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommentController extends FrontendController
{
    public function store(Request $request, string $slug): RedirectResponse
    {
        $post = Post::published()->where('slug', $slug)->firstOrFail();

        $data = $request->validate([
            'author_name' => ['required', 'string', 'max:255'],
            'author_email' => ['required', 'email', 'max:255'],
            'content' => ['required', 'string', 'min:3', 'max:5000'],
            'parent_id' => ['nullable', 'integer'],
        ]);

        if (isset($data['parent_id'])) {
            $parent = Comment::approved()
                ->where('post_id', $post->id)
                ->whereKey($data['parent_id'])
                ->first();

            if ($parent === null) {
                unset($data['parent_id']);
            }
        }

        $comment = Comment::create([
            'post_id' => $post->id,
            'parent_id' => $data['parent_id'] ?? null,
            'author_name' => $data['author_name'],
            'author_email' => $data['author_email'],
            'content' => $data['content'],
            'status' => Comment::STATUS_PENDING,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
        ]);

        CommentSubmitted::dispatch($comment);

        return back()->with(
            'success',
            'Thanks! Your comment is awaiting moderation.',
        );
    }
}
