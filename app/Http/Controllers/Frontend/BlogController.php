<?php

namespace App\Http\Controllers\Frontend;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Support\ContentCache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Response;

class BlogController extends FrontendController
{
    public function __invoke(Request $request): Response
    {
        $posts = Post::published()
            ->with(['author', 'categories', 'featuredMedia'])
            ->latest('published_at')
            ->paginate($this->settings->int('posts_per_page', 10))
            ->withQueryString()
            ->through(fn (Post $post): array => $this->presenter->postCard($post));

        return $this->page('Frontend/Blog', [
            'posts' => $posts,
            'sidebar' => $this->sidebar(),
            'seo' => $this->seo->build([
                'title' => 'Blog',
                'description' => 'All articles published on '.$this->settings->get('site_name', config('app.name')).'.',
                'path' => '/blog',
                'paginate' => $this->paginationMeta($posts),
            ]),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function sidebar(): array
    {
        return Cache::remember(ContentCache::BLOG_SIDEBAR, ContentCache::TTL, fn (): array => [
            'categories' => Category::ordered()->withCount([
                'posts' => fn ($query) => $query->where('status', Post::STATUS_PUBLISHED),
            ])->get()->map(fn (Category $category): array => [
                'name' => $category->name,
                'slug' => $category->slug,
                'posts_count' => $category->posts_count,
            ])->all(),
            'tags' => Tag::query()
                ->withCount(['posts' => fn ($query) => $query->where('status', Post::STATUS_PUBLISHED)])
                ->whereHas('posts', fn ($query) => $query->where('status', Post::STATUS_PUBLISHED))
                ->orderBy('name')
                ->get()
                ->map(fn (Tag $tag): array => [
                    'name' => $tag->name,
                    'slug' => $tag->slug,
                ])->all(),
            'recent' => Post::published()
                ->latest('published_at')
                ->limit(4)
                ->get(['id', 'title', 'slug', 'published_at'])
                ->map(fn (Post $post): array => [
                    'title' => $post->title,
                    'slug' => $post->slug,
                    'published_human' => $post->published_at?->format('M j, Y'),
                ])->all(),
        ]);
    }
}
