<?php

namespace App\Http\Middleware;

use App\Models\Redirect;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class HandleRedirects
{
    /**
     * @var list<string>
     */
    private const IGNORED_PREFIXES = ['admin', 'up', '_inertia', 'storage', 'build'];

    /**
     * @var list<string>
     */
    private const IGNORED_PATHS = ['sitemap.xml', 'robots.txt'];

    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethod('GET') && ! $request->isMethod('HEAD')) {
            return $next($request);
        }

        $path = '/'.ltrim($request->path(), '/');

        if ($this->shouldSkip($path)) {
            return $next($request);
        }

        /** @var array<string, array{destination: string, status_code: int}> $map */
        $map = Cache::rememberForever(
            Redirect::CACHE_KEY,
            fn (): array => Redirect::query()
                ->active()
                ->get(['source', 'destination', 'status_code'])
                ->mapWithKeys(fn (Redirect $redirect): array => [
                    $redirect->source => [
                        'destination' => $redirect->destination,
                        'status_code' => $redirect->status_code,
                    ],
                ])
                ->all(),
        );

        $match = $map[$path] ?? null;

        if ($match === null) {
            return $next($request);
        }

        if ($this->isSelfRedirect($path, $match['destination'])) {
            return $next($request);
        }

        Redirect::query()
            ->where('source', $path)
            ->first()
            ?->registerHit();

        return redirect($match['destination'], $match['status_code']);
    }

    private function shouldSkip(string $path): bool
    {
        $trimmed = ltrim($path, '/');

        if (in_array($trimmed, self::IGNORED_PATHS, true)) {
            return true;
        }

        foreach (self::IGNORED_PREFIXES as $prefix) {
            if ($trimmed === $prefix || str_starts_with($trimmed, $prefix.'/')) {
                return true;
            }
        }

        return false;
    }

    private function isSelfRedirect(string $path, string $destination): bool
    {
        $target = parse_url($destination, PHP_URL_PATH);

        if ($target === null || $target === false) {
            return false;
        }

        return '/'.ltrim($target, '/') === $path;
    }
}
