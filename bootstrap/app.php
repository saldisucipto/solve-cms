<?php

use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\HandleRedirects;
use App\Http\Middleware\SecurityHeaders;
use App\Services\Frontend\FrontendData;
use App\Services\Seo\SeoPresenter;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: ['127.0.0.1']);
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->alias([
            'active' => EnsureUserIsActive::class,
        ]);

        $middleware->append(SecurityHeaders::class);
        $middleware->append(HandleRedirects::class);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->respond(function (Response $response, Throwable $e, Request $request): Response {
            $isPublicGet = ! $request->expectsJson()
                && ! $request->is('admin*')
                && ! $request->is('api/*')
                && ! $request->is('build/*')
                && ! $request->is('storage/*');

            if (! $isPublicGet) {
                return $response;
            }

            $status = $response->getStatusCode();

            $pages = [
                403 => ['title' => 'Access denied', 'message' => 'You do not have permission to view this page.'],
                404 => ['title' => 'Page not found', 'message' => 'The page you are looking for does not exist or has been moved.'],
                419 => ['title' => 'Session expired', 'message' => 'Your session expired for security. Please reload and try again.'],
                429 => ['title' => 'Too many requests', 'message' => 'You are sending requests too quickly. Please wait a moment.'],
                500 => ['title' => 'Something went wrong', 'message' => 'An unexpected error occurred. Please try again later.'],
                503 => ['title' => 'Temporarily unavailable', 'message' => 'The site is down for maintenance. Please check back soon.'],
            ];

            if (! isset($pages[$status])) {
                return $response;
            }

            $component = $status === 404 ? 'Frontend/NotFound' : 'Frontend/Error';

            $props = [
                ...app(FrontendData::class)->shared(),
                'seo' => app(SeoPresenter::class)->build([
                    'title' => $pages[$status]['title'],
                    'path' => '/'.ltrim($request->path(), '/'),
                    'robots' => 'noindex,follow',
                ]),
            ];

            if ($component === 'Frontend/Error') {
                $props['status'] = $status;
                $props['title'] = $pages[$status]['title'];
                $props['message'] = $pages[$status]['message'];
            }

            return Inertia::render($component, $props)->toResponse($request)->setStatusCode($status);
        });
    })->create();
