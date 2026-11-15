<?php

namespace App\Services\Content;

use App\Models\Post;
use App\Models\PostRevision;
use App\Models\User;
use Illuminate\Support\Carbon;

class PostService
{
    public function __construct(private readonly HtmlSanitizer $sanitizer) {}

    /**
     * Persist the post and capture a revision of its editable content.
     *
     * @param  array<string, mixed>  $attributes
     * @param  array<int, int>  $categoryIds
     * @param  array<int, int>  $tagIds
     */
    public function save(Post $post, array $attributes, array $categoryIds = [], array $tagIds = []): Post
    {
        if (array_key_exists('content', $attributes)) {
            $attributes['content'] = $this->sanitizer->clean($attributes['content']);
        }

        $post->fill($attributes);
        $post->save();

        $post->categories()->sync($categoryIds);
        $post->tags()->sync($tagIds);

        $this->recordRevision($post);

        return $post->refresh();
    }

    public function recordRevision(Post $post, ?User $user = null): ?PostRevision
    {
        $latest = $post->revisions()->max('revision');

        if ($latest !== null) {
            $previous = $post->revisions()->orderByDesc('revision')->first();

            if ($previous !== null
                && $previous->title === $post->title
                && $previous->content === $post->content
                && $previous->excerpt === $post->excerpt) {
                return null;
            }
        }

        return $post->revisions()->create([
            'user_id' => $user !== null ? $user->id : $post->user_id,
            'title' => $post->title,
            'excerpt' => $post->excerpt,
            'content' => $post->content,
            'revision' => ($latest ?? 0) + 1,
        ]);
    }

    /**
     * Resolve the status that should be stored for the given submission.
     *
     * @param  array<string, mixed>  $data
     * @return array{status: string, published_at: Carbon|null}
     */
    public function resolveStatus(array $data, User $user): array
    {
        $status = $data['status'] ?? Post::STATUS_DRAFT;
        $canPublish = $user->hasPermission('posts.publish');

        if (in_array($status, [Post::STATUS_PUBLISHED, Post::STATUS_SCHEDULED], true) && ! $canPublish) {
            $status = Post::STATUS_PENDING;
        }

        $publishedAt = null;

        if ($status === Post::STATUS_PUBLISHED) {
            $publishedAt = $data['published_at'] ?? now();
        }

        if ($status === Post::STATUS_SCHEDULED) {
            $publishedAt = $data['published_at'] ?? now()->addDay();
        }

        return ['status' => $status, 'published_at' => $publishedAt];
    }
}
