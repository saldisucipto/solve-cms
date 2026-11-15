<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\Content\ContentPresenter;
use App\Services\Frontend\FrontendData;
use App\Services\Seo\SeoPresenter;
use App\Services\SiteSettings;
use Inertia\Response;

abstract class FrontendController extends Controller
{
    public function __construct(
        protected readonly SiteSettings $settings,
        protected readonly SeoPresenter $seo,
        protected readonly ContentPresenter $presenter,
        protected readonly FrontendData $frontend,
    ) {}

    /**
     * @param  array<string, mixed>  $props
     */
    protected function page(string $component, array $props): Response
    {
        return inertia($component, [
            ...$props,
            ...$this->frontend->shared(),
        ]);
    }

    /**
     * Pagination metadata for SEO: the canonical stays on the unpaginated URL
     * and every page after the first is set to `noindex,follow`.
     *
     * @return array{current: int}
     */
    protected function paginationMeta(mixed $paginator): array
    {
        return ['current' => (int) $paginator->currentPage()];
    }
}
