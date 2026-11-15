<?php

namespace App\Http\Controllers\Frontend;

use App\Models\Page;
use Inertia\Response;

class PageController extends FrontendController
{
    public function __invoke(string $slug): Response
    {
        $page = Page::published()
            ->with(['seo', 'featuredMedia'])
            ->where('slug', $slug)
            ->firstOrFail();

        return $this->page('Frontend/Page', [
            'page' => [
                'id' => $page->id,
                'title' => $page->title,
                'slug' => $page->slug,
                'content' => $page->content,
                'excerpt' => $page->excerpt,
                'image' => $page->featuredMedia?->url(),
                'image_alt' => $page->featuredMedia?->alt_text ?: $page->title,
                'template' => $page->template,
                'updated_at' => $page->updated_at?->toIso8601String(),
            ],
            'seo' => $this->seo->build([
                'title' => $page->seo?->title ?: $page->title,
                'description' => $page->seo?->description ?: $page->excerpt,
                'path' => '/'.$page->slug,
                'canonical' => $page->seo?->canonical_url,
                'image' => $page->seo?->og_image ?: $page->featuredMedia?->url(),
                'robots' => $page->seo?->robots,
                'breadcrumbs' => [
                    ['name' => 'Home', 'url' => $this->settings->siteUrl().'/'],
                    ['name' => $page->title, 'url' => $this->settings->siteUrl().'/'.$page->slug],
                ],
            ]),
        ]);
    }
}
