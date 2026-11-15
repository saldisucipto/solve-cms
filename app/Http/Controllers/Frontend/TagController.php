<?php

namespace App\Http\Controllers\Frontend;

use App\Models\Post;
use App\Models\Tag;
use Inertia\Response;

class TagController extends FrontendController
{
    public function __invoke(string $slug): Response
    {
        $tag = Tag::where('slug', $slug)->firstOrFail();

        $posts = Post::published()
            ->with(['author', 'categories', 'featuredMedia'])
            ->whereHas('tags', fn ($query) => $query->where('tags.id', $tag->id))
            ->latest('published_at')
            ->paginate($this->settings->int('posts_per_page', 10))
            ->withQueryString()
            ->through(fn (Post $post): array => $this->presenter->postCard($post));

        return $this->page('Frontend/Archive', [
            'heading' => '#'.$tag->name,
            'subheading' => $tag->description,
            'kind' => 'tag',
            'posts' => $posts,
            'seo' => $this->seo->build([
                'title' => $tag->seo?->title ?: $tag->name,
                'description' => $tag->seo?->description ?: $tag->description,
                'path' => '/tag/'.$tag->slug,
                'canonical' => $tag->seo?->canonical_url,
                'robots' => $tag->seo?->robots,
                'paginate' => $this->paginationMeta($posts),
                'breadcrumbs' => [
                    ['name' => 'Home', 'url' => $this->settings->siteUrl().'/'],
                    ['name' => 'Blog', 'url' => $this->settings->siteUrl().'/blog'],
                    ['name' => $tag->name, 'url' => $this->settings->siteUrl().'/tag/'.$tag->slug],
                ],
            ]),
        ]);
    }
}
