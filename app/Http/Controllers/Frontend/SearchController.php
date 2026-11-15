<?php

namespace App\Http\Controllers\Frontend;

use App\Models\Page;
use App\Models\Post;
use Illuminate\Http\Request;
use Inertia\Response;

class SearchController extends FrontendController
{
    public function __invoke(Request $request): Response
    {
        $query = trim((string) $request->string('q')->value());

        $posts = collect();
        $pages = collect();

        if ($query !== '') {
            $posts = Post::published()
                ->with(['author', 'categories', 'featuredMedia'])
                ->where(fn ($inner) => $inner
                    ->where('title', 'like', "%{$query}%")
                    ->orWhere('excerpt', 'like', "%{$query}%")
                    ->orWhere('content', 'like', "%{$query}%"))
                ->latest('published_at')
                ->limit(20)
                ->get()
                ->map(fn (Post $post): array => $this->presenter->postCard($post));

            $pages = Page::published()
                ->where(fn ($inner) => $inner
                    ->where('title', 'like', "%{$query}%")
                    ->orWhere('content', 'like', "%{$query}%"))
                ->orderBy('title')
                ->limit(20)
                ->get(['id', 'title', 'slug', 'excerpt'])
                ->map(fn (Page $page): array => [
                    'title' => $page->title,
                    'slug' => $page->slug,
                    'excerpt' => $page->excerpt,
                    'url' => '/'.$page->slug,
                ]);
        }

        return $this->page('Frontend/Search', [
            'query' => $query,
            'posts' => $posts,
            'pages' => $pages,
            'total' => $posts->count() + $pages->count(),
            'seo' => $this->seo->build([
                'title' => $query === '' ? 'Search' : "Search: {$query}",
                'description' => 'Search results for '.$query,
                'path' => '/search',
                'robots' => 'noindex,follow',
            ]),
        ]);
    }
}
