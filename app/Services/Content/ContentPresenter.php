<?php

namespace App\Services\Content;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Support\Collection;

class ContentPresenter
{
    /**
     * @return array<string, mixed>
     */
    public function postCard(Post $post): array
    {
        return [
            'id' => $post->id,
            'title' => $post->title,
            'slug' => $post->slug,
            'excerpt' => $post->excerpt ?? $this->excerptFromContent($post->content),
            'image' => $post->featuredMedia?->url(),
            'image_alt' => $post->featuredMedia?->alt_text ?: $post->title,
            'published_at' => $post->published_at?->toIso8601String(),
            'published_human' => $post->published_at?->format('M j, Y'),
            'author' => $post->author?->name,
            'reading_time' => $this->readingTime($post->content),
            'categories' => $post->categories->map(fn ($category): array => [
                'name' => $category->name,
                'slug' => $category->slug,
            ])->all(),
            'url' => route('frontend.posts.show', $post->slug, absolute: false),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function postFull(Post $post): array
    {
        $post->loadMissing(['author', 'categories', 'tags', 'featuredMedia', 'seo']);

        return [
            ...$this->postCard($post),
            'content' => $post->content,
            'tags' => $post->tags->map(fn ($tag): array => [
                'name' => $tag->name,
                'slug' => $tag->slug,
            ])->all(),
            'updated_at' => $post->updated_at?->toIso8601String(),
        ];
    }

    /**
     * @param  Collection<int, Comment>  $comments
     * @return array<int, array<string, mixed>>
     */
    public function commentThread(Collection $comments, ?int $parentId = null): array
    {
        return $comments
            ->where('parent_id', $parentId)
            ->map(fn (Comment $comment): array => [
                'id' => $comment->id,
                'author_name' => $comment->author_name,
                'content' => $comment->content,
                'created_at' => $comment->created_at?->format('M j, Y \a\t H:i'),
                'replies' => $this->commentThread($comments, $comment->id),
            ])
            ->values()
            ->all();
    }

    private function excerptFromContent(?string $content): string
    {
        $text = html_entity_decode(strip_tags((string) $content), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = trim(preg_replace('/\s+/', ' ', $text) ?? '');

        return mb_strimwidth($text, 0, 180, '…');
    }

    private function readingTime(?string $content): int
    {
        $text = trim(strip_tags((string) $content));
        $words = $text === '' ? 0 : count(preg_split('/\s+/', $text) ?: []);

        return max(1, (int) ceil($words / 200));
    }
}
