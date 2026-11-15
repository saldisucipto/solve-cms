<?php

namespace App\Services\Seo;

use App\Services\SiteSettings;

/**
 * Composes the metadata array each public page hands to Inertia, which the
 * frontend turns into <title>, meta tags, canonical link and JSON-LD.
 */
class SeoPresenter
{
    public function __construct(private readonly SiteSettings $settings) {}

    /**
     * @param  array{
     *     title: string,
     *     description?: string|null,
     *     path?: string,
     *     canonical?: string|null,
     *     image?: string|null,
     *     type?: string,
     *     robots?: string|null,
     *     published_at?: string|null,
     *     updated_at?: string|null,
     *     author?: string|null,
     *     breadcrumbs?: array<int, array{name: string, url: string}>,
     *     paginate?: array{current: int} | null,
     * }  $data
     * @return array<string, mixed>
     */
    public function build(array $data): array
    {
        $siteName = (string) $this->settings->get('site_name', config('app.name'));
        $separator = (string) $this->settings->get('seo_title_separator', '|');
        $base = $this->settings->siteUrl();
        $defaultRobots = (string) $this->settings->get('seo_default_robots', 'index,follow');

        $title = trim((string) $data['title']);
        $fullTitle = $title === ''
            ? $siteName
            : "{$title} {$separator} {$siteName}";

        $description = $data['description'] ?? $this->settings->get('site_description');
        $path = $data['path'] ?? null;
        $canonical = $data['canonical'] ?? ($path === null ? $base.'/' : $base.'/'.ltrim($path, '/'));
        $robots = $data['robots'] ?? $defaultRobots;

        $paginate = $data['paginate'] ?? null;

        if ($paginate !== null && $paginate['current'] > 1) {
            $robots = 'noindex,follow';
        }

        $schemas = $this->schemas($data, $siteName, $base, $canonical, $this->breadcrumbs($data));

        return [
            'title' => $fullTitle,
            'rawTitle' => $title,
            'description' => $description,
            'canonical' => $canonical,
            'robots' => $robots,
            'siteName' => $siteName,
            'twitterHandle' => $this->settings->get('seo_twitter_handle'),
            'og' => [
                'type' => $data['type'] ?? 'website',
                'title' => $fullTitle,
                'description' => $description,
                'image' => $data['image'] ?? null,
                'url' => $canonical,
                'site_name' => $siteName,
            ],
            'schemas' => $schemas,
        ];
    }

    /**
     * Normalise the breadcrumb input so the schema builder always receives a
     * well-formed list.
     *
     * @param  array<string, mixed>  $data
     * @return array<int, array{name: string, url: string}>
     */
    private function breadcrumbs(array $data): array
    {
        $crumbs = $data['breadcrumbs'] ?? null;

        if (! is_array($crumbs)) {
            return [];
        }

        $normalised = [];

        foreach ($crumbs as $crumb) {
            if (is_array($crumb) && isset($crumb['name'], $crumb['url']) && is_string($crumb['name']) && is_string($crumb['url'])) {
                $normalised[] = ['name' => $crumb['name'], 'url' => $crumb['url']];
            }
        }

        return $normalised;
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, array{name: string, url: string}>  $breadcrumbs
     * @return array<int, array<string, mixed>>
     */
    private function schemas(array $data, string $siteName, string $base, string $canonical, array $breadcrumbs): array
    {
        $schemas = [];

        $organizationName = (string) $this->settings->get('seo_organization_name', $siteName);

        $schemas[] = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $organizationName,
            'url' => $base.'/',
        ];

        $schemas[] = [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => $siteName,
            'url' => $base.'/',
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => $base.'/search?q={search_term_string}',
                'query-input' => 'required name=search_term_string',
            ],
        ];

        $type = $data['type'] ?? 'website';

        if ($type === 'article') {
            $schemas[] = array_filter([
                '@context' => 'https://schema.org',
                '@type' => 'Article',
                'headline' => $data['title'],
                'description' => $data['description'] ?? null,
                'image' => $data['image'] ?? null,
                'datePublished' => $data['published_at'] ?? null,
                'dateModified' => $data['updated_at'] ?? null,
                'author' => isset($data['author']) ? [
                    '@type' => 'Person',
                    'name' => $data['author'],
                ] : null,
                'publisher' => [
                    '@type' => 'Organization',
                    'name' => $organizationName,
                ],
                'mainEntityOfPage' => $canonical,
            ], fn (mixed $value): bool => $value !== null);
        } elseif ($type === 'website' && $breadcrumbs === []) {
            $schemas[] = [
                '@context' => 'https://schema.org',
                '@type' => 'WebPage',
                'name' => $data['title'],
                'url' => $canonical,
            ];
        } elseif ($type !== 'website') {
            $schemas[] = [
                '@context' => 'https://schema.org',
                '@type' => 'WebPage',
                'name' => $data['title'],
                'description' => $data['description'] ?? null,
                'url' => $canonical,
            ];
        }

        if ($breadcrumbs !== []) {
            $itemList = [];

            foreach ($breadcrumbs as $index => $crumb) {
                $itemList[] = [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'name' => $crumb['name'],
                    'item' => $crumb['url'],
                ];
            }

            $schemas[] = [
                '@context' => 'https://schema.org',
                '@type' => 'BreadcrumbList',
                'itemListElement' => $itemList,
            ];
        }

        return $schemas;
    }
}
