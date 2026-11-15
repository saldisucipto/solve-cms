<?php

namespace App\Http\Controllers\Frontend;

use App\Models\Comment;
use App\Models\Post;
use Inertia\Response;

class PostController extends FrontendController
{
    public function __invoke(string $slug): Response
    {
        $post = Post::published()
            ->with(['author', 'categories', 'tags', 'featuredMedia', 'seo'])
            ->where('slug', $slug)
            ->firstOrFail();

        $comments = Comment::approved()
            ->where('post_id', $post->id)
            ->orderBy('created_at')
            ->get();

        $related = Post::published()
            ->with(['author', 'categories', 'featuredMedia'])
            ->whereKeyNot($post->id)
            ->when(
                $post->categories->isNotEmpty(),
                fn ($query) => $query->whereHas('categories', fn ($inner) => $inner
                    ->whereIn('categories.id', $post->categories->pluck('id'))),
            )
            ->latest('published_at')
            ->limit(3)
            ->get();

        return $this->page('Frontend/Post', [
            'post' => $this->presenter->postFull($post),
            'comments' => $this->presenter->commentThread($comments),
            'commentsCount' => $comments->count(),
            'related' => $related->map(fn (Post $item): array => $this->presenter->postCard($item))->values(),
            'seo' => $this->seo->build([
                'title' => $post->seo?->title ?: $post->title,
                'description' => $post->seo?->description ?: $post->excerpt,
                'path' => '/blog/'.$post->slug,
                'canonical' => $post->seo?->canonical_url,
                'image' => $post->seo?->og_image ?: $post->featuredMedia?->url(),
                'type' => 'article',
                'robots' => $post->seo?->robots,
                'published_at' => $post->published_at?->toIso8601String(),
                'updated_at' => $post->updated_at?->toIso8601String(),
                'author' => $post->author?->name,
                'breadcrumbs' => [
                    ['name' => 'Home', 'url' => $this->settings->siteUrl().'/'],
                    ['name' => 'Blog', 'url' => $this->settings->siteUrl().'/blog'],
                    ['name' => $post->title, 'url' => $this->settings->siteUrl().'/blog/'.$post->slug],
                ],
            ]),
        ]);
    }
}
