<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

/**
 * Adds a per-request CSP nonce and the standard hardening headers.
 *
 * Scripts stay nonce-bound (no `unsafe-inline`). Inline style *attributes* are
 * allowed because a nonce cannot cover them and Vue bindings plus reka-ui
 * primitives depend on them. The Vite dev server and the Reverb WebSocket
 * origins are added in local development so hot reload and Echo can connect.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $nonce = base64_encode(random_bytes(16));

        $request->attributes->set('csp_nonce', $nonce);
        view()->share('cspNonce', $nonce);
        Vite::useCspNonce($nonce);

        $response = $next($request);

        $response->headers->set('Content-Security-Policy', $this->policy($nonce));
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $response->headers->remove('X-Powered-By');

        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }

    private function policy(string $nonce): string
    {
        // Only the fetch origin matters for assets; sockets are connect-src only.
        $devHttp = $this->viteDevHttpOrigin();

        $script = $this->join(["'self'", "'nonce-{$nonce}'"], $devHttp);
        $style = $this->join(["'self'", "'unsafe-inline'"], $devHttp);

        // In production `<style>` elements are nonce-bound; the Vite dev server
        // injects stylesheets at runtime, so local must allow its origin.
        $styleElements = app()->environment('local')
            ? $this->join(["'self'", "'unsafe-inline'"], $devHttp)
            : $this->join(["'self'", "'nonce-{$nonce}'"], $devHttp);

        $connect = $this->join(
            ["'self'"],
            array_merge($this->viteDevOrigins(), $this->reverbOrigins()),
        );

        $directives = [
            "default-src 'self'",
            "script-src {$script}",
            "style-src {$style}",
            "style-src-elem {$styleElements}",
            "style-src-attr 'unsafe-inline'",
            "img-src {$this->join(["'self'", 'data:', 'https:'], $devHttp)}",
            "font-src {$this->join(["'self'", 'data:'], $devHttp)}",
            "connect-src {$connect}",
            "form-action 'self'",
            "frame-ancestors 'self'",
            "base-uri 'self'",
            "object-src 'none'",
        ];

        return implode('; ', $directives);
    }

    /**
     * @param  array<int, string>  $base
     * @param  array<int, string>  $extra
     */
    private function join(array $base, array $extra = []): string
    {
        return implode(' ', array_unique(array_filter(array_merge($base, $extra))));
    }

    /**
     * The Vite dev server origin as served over HTTP, read from the `hot` file
     * it writes. Empty outside local development.
     *
     * @return array<int, string>
     */
    private function viteDevHttpOrigin(): array
    {
        $origins = $this->viteDevOrigins();

        return $origins === [] ? [] : [$origins[0]];
    }

    /**
     * HTTP and WebSocket variants of the Vite dev server origin.
     *
     * @return array<int, string>
     */
    private function viteDevOrigins(): array
    {
        if (! app()->environment('local')) {
            return [];
        }

        $hotFile = public_path('hot');

        if (! is_file($hotFile)) {
            return [];
        }

        return $this->httpAndWebSocketVariants(trim((string) file_get_contents($hotFile)));
    }

    /**
     * Origins Echo connects to (http for the API, ws for the socket).
     *
     * @return array<int, string>
     */
    private function reverbOrigins(): array
    {
        $scheme = (string) config('broadcasting.connections.reverb.options.scheme', 'http');
        $host = config('broadcasting.connections.reverb.options.host');
        $port = config('broadcasting.connections.reverb.options.port');

        if (! is_string($host) || $host === '') {
            return [];
        }

        return $this->httpAndWebSocketVariants(
            $port !== null && $port !== ''
                ? "{$scheme}://{$host}:{$port}"
                : "{$scheme}://{$host}",
        );
    }

    /**
     * @return array<int, string>
     */
    private function httpAndWebSocketVariants(string $url): array
    {
        $parts = parse_url($url);

        if (! is_array($parts) || ! isset($parts['host'])) {
            return [];
        }

        $host = $parts['host'];
        $httpPort = isset($parts['port']) ? ':'.$parts['port'] : '';
        $secure = ($parts['scheme'] ?? 'http') === 'https';
        $wsPort = $httpPort !== '' ? $httpPort : ($secure ? ':443' : ':80');

        return [
            'http'.($secure ? 's' : '')."://{$host}{$httpPort}",
            'ws'.($secure ? 's' : '')."://{$host}{$wsPort}",
        ];
    }
}
