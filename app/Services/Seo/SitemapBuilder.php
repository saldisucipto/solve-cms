<?php

namespace App\Services\Seo;

use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use App\Models\Tag;
use App\Services\SiteSettings;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Route;

/**
 * Builds the XML sitemap. Both the public `/sitemap.xml` endpoint and the admin
 * Sitemap screen read from here, so what the dashboard shows is exactly what
 * crawlers receive.
 */
class SitemapBuilder
{
    public function __construct(private readonly SiteSettings $settings) {}

    /**
     * @return array<int, array{loc: string, lastmod: string|null, changefreq: string, priority: string, type: string}>
     */
    public function entries(): array
    {
        $base = $this->settings->siteUrl();

        $entries = [];
        $seen = [];

        $add = function (string $url, ?CarbonInterface $lastmod, string $changefreq, string $priority, string $type) use (&$entries, &$seen): void {
            if (isset($seen[$url])) {
                return;
            }

            $seen[$url] = true;
            $entries[] = [
                'loc' => $url,
                'lastmod' => $lastmod?->toAtomString(),
                'changefreq' => $changefreq,
                'priority' => $priority,
                'type' => $type,
            ];
        };

        $add($base.'/', now(), 'daily', '1.0', 'home');

        if (Route::has('frontend.pages.show')) {
            Page::published()->orderBy('updated_at', 'desc')->get()->each(
                fn (Page $page) => $add(route('frontend.pages.show', $page->slug, absolute: true), $page->updated_at, 'monthly', '0.8', 'page')
            );
        }

        if (Route::has('frontend.posts.show')) {
            Post::published()->orderBy('updated_at', 'desc')->get()->each(
                fn (Post $post) => $add(route('frontend.posts.show', $post->slug, absolute: true), $post->updated_at, 'weekly', '0.7', 'post')
            );
        }

        if (Route::has('frontend.categories.show')) {
            Category::ordered()->get()->each(
                fn (Category $category) => $add(route('frontend.categories.show', $category->slug, absolute: true), $category->updated_at, 'weekly', '0.5', 'category')
            );
        }

        if (Route::has('frontend.tags.show')) {
            Tag::orderBy('name')->get()->each(
                fn (Tag $tag) => $add(route('frontend.tags.show', $tag->slug, absolute: true), $tag->updated_at, 'weekly', '0.4', 'tag')
            );
        }

        return $entries;
    }

    /**
     * @return array<string, int>
     */
    public function counts(): array
    {
        $counts = ['home' => 0, 'page' => 0, 'post' => 0, 'category' => 0, 'tag' => 0];

        foreach ($this->entries() as $entry) {
            $counts[$entry['type']] = ($counts[$entry['type']] ?? 0) + 1;
        }

        return $counts;
    }

    /**
     * @param  array<int, array{loc: string, lastmod: string|null, changefreq: string, priority: string}>  $entries
     */
    public function render(array $entries): string
    {
        $lines = [
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">',
        ];

        foreach ($entries as $entry) {
            $lines[] = '  <url>';
            $lines[] = '    <loc>'.e($entry['loc']).'</loc>';

            if ($entry['lastmod'] !== null) {
                $lines[] = '    <lastmod>'.e($entry['lastmod']).'</lastmod>';
            }

            $lines[] = '    <changefreq>'.e($entry['changefreq']).'</changefreq>';
            $lines[] = '    <priority>'.e($entry['priority']).'</priority>';
            $lines[] = '  </url>';
        }

        $lines[] = '</urlset>';

        return implode("\n", $lines)."\n";
    }

    public function enabled(): bool
    {
        return $this->settings->bool('seo_sitemap_enabled', true);
    }

    public function url(): string
    {
        return $this->settings->siteUrl().'/sitemap.xml';
    }
}
