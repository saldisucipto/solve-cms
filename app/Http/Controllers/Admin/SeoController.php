<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Post;
use App\Models\Redirect;
use App\Models\SeoMetadata;
use App\Services\ActivityLogger;
use App\Services\Seo\SeoAnalyzer;
use App\Services\Seo\SitemapBuilder;
use App\Services\SiteSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class SeoController extends Controller
{
    public function __construct(
        private readonly SiteSettings $settings,
        private readonly SeoAnalyzer $analyzer,
        private readonly ActivityLogger $activity,
    ) {}

    public function index(): Response
    {
        Gate::authorize('seo.view');

        $posts = Post::query()->with('seo')->get();
        $pages = Page::query()->with('seo')->get();

        $missingTitle = $posts->filter(fn (Post $post): bool => blank($post->seo?->title))->count()
            + $pages->filter(fn (Page $page): bool => blank($page->seo?->title))->count();

        $missingDescription = $posts->filter(fn (Post $post): bool => blank($post->seo?->description))->count()
            + $pages->filter(fn (Page $page): bool => blank($page->seo?->description))->count();

        $missingKeyword = $posts->filter(fn (Post $post): bool => blank($post->seo?->focus_keyword))->count()
            + $pages->filter(fn (Page $page): bool => blank($page->seo?->focus_keyword))->count();

        $total = $posts->count() + $pages->count();

        return Inertia::render('Admin/Seo/Overview', [
            'stats' => [
                'content' => $total,
                'missing_title' => $missingTitle,
                'missing_description' => $missingDescription,
                'missing_keyword' => $missingKeyword,
                'redirects' => Redirect::count(),
                'active_redirects' => Redirect::active()->count(),
            ],
            'urls' => [
                'sitemap' => url('/sitemap.xml'),
                'robots' => url('/robots.txt'),
            ],
            'sitemapEnabled' => $this->settings->bool('seo_sitemap_enabled', true),
            'recentRedirects' => Redirect::query()
                ->latest()
                ->limit(5)
                ->get(['id', 'source', 'destination', 'status_code', 'hits'])
                ->map(fn (Redirect $redirect): array => [
                    'id' => $redirect->id,
                    'source' => $redirect->source,
                    'destination' => $redirect->destination,
                    'status_code' => $redirect->status_code,
                    'hits' => $redirect->hits,
                ]),
        ]);
    }

    public function settings(): Response
    {
        Gate::authorize('seo.view');

        return Inertia::render('Admin/Seo/Settings', [
            'settings' => [
                'site_url' => $this->settings->get('site_url', config('app.url')),
                'seo_title_separator' => $this->settings->get('seo_title_separator', '|'),
                'seo_default_robots' => $this->settings->get('seo_default_robots', 'index,follow'),
                'seo_organization_name' => $this->settings->get('seo_organization_name', ''),
                'seo_twitter_handle' => $this->settings->get('seo_twitter_handle', ''),
                'seo_google_verification' => $this->settings->get('seo_google_verification', ''),
                'seo_sitemap_enabled' => $this->settings->bool('seo_sitemap_enabled', true),
            ],
            'can' => [
                'manage' => request()->user()->hasPermission('seo.technical'),
            ],
        ]);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        Gate::authorize('seo.technical');

        $data = $request->validate([
            'site_url' => ['required', 'url', 'max:255'],
            'seo_title_separator' => ['required', 'string', 'max:5'],
            'seo_default_robots' => ['required', 'string', 'max:100'],
            'seo_organization_name' => ['nullable', 'string', 'max:255'],
            'seo_twitter_handle' => ['nullable', 'string', 'max:100'],
            'seo_google_verification' => ['nullable', 'string', 'max:255'],
            'seo_sitemap_enabled' => ['boolean'],
        ]);

        $this->settings->setMany([
            'site_url' => $data['site_url'],
            'seo_title_separator' => $data['seo_title_separator'],
            'seo_default_robots' => $data['seo_default_robots'],
            'seo_organization_name' => $data['seo_organization_name'] ?? null,
            'seo_twitter_handle' => $data['seo_twitter_handle'] ?? null,
            'seo_google_verification' => $data['seo_google_verification'] ?? null,
            'seo_sitemap_enabled' => $request->boolean('seo_sitemap_enabled') ? '1' : '0',
        ], 'seo');

        $this->activity->log('seo.settings_updated', 'Updated technical SEO settings');

        return back()->with('success', 'SEO settings saved.');
    }

    public function sitemap(): Response
    {
        Gate::authorize('seo.view');

        $builder = app(SitemapBuilder::class);
        $entries = $builder->entries();

        return Inertia::render('Admin/Seo/Sitemap', [
            'enabled' => $builder->enabled(),
            'url' => $builder->url(),
            'robotsUrl' => url('/robots.txt'),
            'counts' => $builder->counts(),
            'total' => count($entries),
            'entries' => array_slice(array_map(fn (array $entry): array => [
                'loc' => $entry['loc'],
                'lastmod' => $entry['lastmod'],
                'changefreq' => $entry['changefreq'],
                'priority' => $entry['priority'],
                'type' => $entry['type'],
            ], $entries), 0, 200),
            'truncated' => count($entries) > 200,
        ]);
    }

    public function analysis(Request $request): Response
    {
        Gate::authorize('seo.view');

        $type = $request->string('type')->value() ?: 'all';
        $issue = $request->string('issue')->value();

        $items = collect();

        if ($type === 'all' || $type === 'post') {
            $items = $items->merge(
                Post::query()->with('seo')->orderByDesc('updated_at')->get()->map(
                    fn (Post $post): array => $this->auditItem(
                        'post',
                        $post->id,
                        $post->title,
                        $post->status,
                        $post->seo,
                        $post->content,
                        route('admin.posts.edit', $post->id),
                    )
                )
            );
        }

        if ($type === 'all' || $type === 'page') {
            $items = $items->merge(
                Page::query()->with('seo')->orderByDesc('updated_at')->get()->map(
                    fn (Page $page): array => $this->auditItem(
                        'page',
                        $page->id,
                        $page->title,
                        $page->status,
                        $page->seo,
                        $page->content,
                        route('admin.pages.edit', $page->id),
                    )
                )
            );
        }

        if ($issue !== '') {
            $items = $items->filter(fn (array $item): bool => in_array($issue, $item['issues'], true));
        }

        return Inertia::render('Admin/Seo/Analysis', [
            'items' => $items->sortByDesc('issues_count')->values(),
            'filters' => ['type' => $type, 'issue' => $issue],
            'issueTypes' => [
                'missing_title' => 'Missing SEO title',
                'missing_description' => 'Missing meta description',
                'missing_keyword' => 'Missing focus keyword',
                'short_content' => 'Short content',
                'no_canonical' => 'Uses default canonical',
            ],
        ]);
    }

    public function analyze(Request $request): JsonResponse
    {
        Gate::authorize('seo.manage');

        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:1000'],
            'focus_keyword' => ['nullable', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'canonical_url' => ['nullable', 'string', 'max:2048'],
            'featured_image_alt' => ['nullable', 'string', 'max:255'],
            'ignore_seo_id' => ['nullable', 'integer'],
        ]);

        return response()->json($this->analyzer->analyze($data));
    }

    /**
     * @return array<string, mixed>
     */
    private function auditItem(
        string $type,
        int $id,
        string $title,
        string $status,
        ?SeoMetadata $seo,
        ?string $content,
        string $editUrl,
    ): array {
        $issues = [];

        if (blank($seo?->title)) {
            $issues[] = 'missing_title';
        }

        if (blank($seo?->description)) {
            $issues[] = 'missing_description';
        }

        if (blank($seo?->focus_keyword)) {
            $issues[] = 'missing_keyword';
        }

        $plain = trim(preg_replace('/\s+/', ' ', strip_tags((string) $content)) ?? '');
        $words = $plain === '' ? 0 : count(explode(' ', $plain));

        if ($words < 300) {
            $issues[] = 'short_content';
        }

        if (blank($seo?->canonical_url)) {
            $issues[] = 'no_canonical';
        }

        return [
            'type' => $type,
            'id' => $id,
            'title' => $title,
            'status' => $status,
            'words' => $words,
            'issues' => $issues,
            'issues_count' => count($issues),
            'edit_url' => $editUrl,
        ];
    }
}
