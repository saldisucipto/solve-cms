<?php

namespace App\Http\Controllers\Frontend;

use App\Models\Category;
use App\Models\Post;
use Inertia\Response;

class CategoryController extends FrontendController
{
    public function __invoke(string $slug): Response
    {
        $category = Category::where('slug', $slug)->firstOrFail();

        $posts = Post::published()
            ->with(['author', 'categories', 'featuredMedia'])
            ->whereHas('categories', fn ($query) => $query->where('categories.id', $category->id))
            ->latest('published_at')
            ->paginate($this->settings->int('posts_per_page', 10))
            ->withQueryString()
            ->through(fn (Post $post): array => $this->presenter->postCard($post));

        return $this->page('Frontend/Archive', [
            'heading' => $category->name,
            'subheading' => $category->description,
            'kind' => 'category',
            'posts' => $posts,
            'seo' => $this->seo->build([
                'title' => $category->seo?->title ?: $category->name,
                'description' => $category->seo?->description ?: $category->description,
                'path' => '/category/'.$category->slug,
                'canonical' => $category->seo?->canonical_url,
                'robots' => $category->seo?->robots,
                'paginate' => $this->paginationMeta($posts),
                'breadcrumbs' => [
                    ['name' => 'Home', 'url' => $this->settings->siteUrl().'/'],
                    ['name' => 'Blog', 'url' => $this->settings->siteUrl().'/blog'],
                    ['name' => $category->name, 'url' => $this->settings->siteUrl().'/category/'.$category->slug],
                ],
            ]),
        ]);
    }
}
