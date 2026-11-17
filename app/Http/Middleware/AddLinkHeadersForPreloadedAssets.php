<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

/**
 * Emits `Link: rel=preload` headers for Vite entry bundles on full page loads.
 *
 * The framework default attaches every preloaded asset at once. With one entry
 * per page that inflates the response header block past Nginx's FastCGI buffer
 * (a hard 502 with `upstream sent too big header`), and only on hard refreshes
 * because Inertia XHR responses are JSON and never receive these headers.
 *
 * Two guards keep the header small: `limit` caps the number of entries, and
 * `except` skips paths where the preload set is largest and the win is smallest
 * (the authenticated admin area, which never navigates cross-entry anyway).
 */
class AddLinkHeadersForPreloadedAssets
{
    /**
     * Maximum number of preload entries written to the header.
     */
    private const LIMIT = 6;

    /**
     * Path patterns excluded from preloading entirely.
     *
     * @var list<string>
     */
    private const EXCEPT = ['admin', 'admin/*'];

    public function handle(Request $request, Closure $next): Response
    {
        return tap($next($request), function (Response $response) use ($request): void {
            if (! $this->shouldPreload($request)) {
                return;
            }

            $assets = Vite::preloadedAssets();

            if ($assets === []) {
                return;
            }

            $response->headers->set('Link', (new Collection($assets))
                ->take(self::LIMIT)
                ->map(fn (array $attributes, string $url): string => "<{$url}>; ".implode('; ', $attributes))
                ->join(', '), false);
        });
    }

    private function shouldPreload(Request $request): bool
    {
        return $request->isMethod('GET')
            && ! $request->is(self::EXCEPT);
    }
}
