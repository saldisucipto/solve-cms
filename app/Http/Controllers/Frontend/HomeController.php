<?php

namespace App\Http\Controllers\Frontend;

use App\Models\Category;
use App\Models\Post;
use App\Support\ContentCache;
use Illuminate\Support\Facades\Cache;
use Inertia\Response;

class HomeController extends FrontendController
{
    public function __invoke(): Response
    {
        $featured = Cache::remember(ContentCache::HOME_FEATURED, ContentCache::TTL, fn (): array => Post::published()
            ->with(['author', 'categories', 'featuredMedia'])
            ->where('is_featured', true)
            ->latest('published_at')
            ->limit(3)
            ->get()
            ->map(fn (Post $post): array => $this->presenter->postCard($post))
            ->values()
            ->all());

        $latest = Cache::remember(ContentCache::HOME_LATEST, ContentCache::TTL, fn (): array => Post::published()
            ->with(['author', 'categories', 'featuredMedia'])
            ->latest('published_at')
            ->limit(6)
            ->get()
            ->map(fn (Post $post): array => $this->presenter->postCard($post))
            ->values()
            ->all());

        $categories = Cache::remember(ContentCache::HOME_CATEGORIES, ContentCache::TTL, fn (): array => Category::ordered()
            ->withCount(['posts' => fn ($query) => $query->where('status', Post::STATUS_PUBLISHED)])
            ->get()
            ->map(fn (Category $category): array => [
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'posts_count' => $category->posts_count,
            ])
            ->all());

        $hero = $featured !== [] ? $featured : array_slice($latest, 0, 1);

        return $this->page('Frontend/Home', [
            'featured' => $hero,
            'latest' => $latest,
            'categories' => $categories,
            'seo' => $this->seo->build([
                'title' => '',
                'description' => $this->settings->get('site_description'),
                'path' => '/',
            ]),
        ]);
    }
}
