<?php

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Post;
use App\Models\User;
use App\Services\Navigation\MenuService;
use App\Services\Theme\ThemeService;
use Database\Seeders\SiteSettingSeeder;
use Database\Seeders\ThemeSeeder;

beforeEach(function () {
    $this->seed(SiteSettingSeeder::class);
    $this->seed(ThemeSeeder::class);
});

describe('security headers', function () {
    test('the content security policy is sent with a nonce', function () {
        $response = $this->get('/');

        $csp = $response->headers->get('Content-Security-Policy');

        expect($csp)->toBeString()
            ->toContain("default-src 'self'")
            ->toContain("script-src 'self' 'nonce-")
            ->toContain("object-src 'none'");
    });

    test('the inline scripts use the same nonce as the header', function () {
        $response = $this->get('/');
        $html = $response->getContent();

        preg_match("/'nonce-([^']+)'/", (string) $response->headers->get('Content-Security-Policy'), $matches);
        $nonce = $matches[1] ?? null;

        expect($nonce)->not->toBeNull()
            ->and($html)->toContain('nonce="'.$nonce.'"');
    });

    test('inline style attributes are permitted so Vue bindings work', function () {
        // A nonce cannot cover style *attributes*; Vue and reka-ui depend on them.
        $csp = $this->get('/login')->headers->get('Content-Security-Policy');

        expect($csp)->toContain("style-src-attr 'unsafe-inline'")
            ->toContain("style-src 'self' 'unsafe-inline'");
    });

    test('scripts remain nonce-bound without unsafe-inline', function () {
        $csp = $this->get('/')->headers->get('Content-Security-Policy');

        expect($csp)->toContain("script-src 'self' 'nonce-")
            ->not->toContain("script-src 'self' 'unsafe-inline'");
    });

    test('the login page renders with a usable content security policy', function () {
        $response = $this->get('/login');

        $response->assertOk();

        expect($response->headers->get('Content-Security-Policy'))->not->toBeNull();
    });

    test('hardening headers are present and X-Powered-By is removed', function () {
        $response = $this->get('/');

        $response->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');

        expect($response->headers->has('X-Powered-By'))->toBeFalse();
    });

    test('the csp allows both http and websocket reverb origins', function () {
        config()->set('broadcasting.connections.reverb.options.host', 'ws.example.com');
        config()->set('broadcasting.connections.reverb.options.port', 443);
        config()->set('broadcasting.connections.reverb.options.scheme', 'https');

        $csp = $this->get('/')->headers->get('Content-Security-Policy');

        expect($csp)->toContain('https://ws.example.com:443')
            ->toContain('wss://ws.example.com:443');
    });

    test('the vite dev server origin is allowed while running locally', function () {
        $hotFile = public_path('hot');
        $previous = is_file($hotFile) ? (string) file_get_contents($hotFile) : null;

        file_put_contents($hotFile, 'http://127.0.0.1:5173');
        $this->app['env'] = 'local';

        try {
            $csp = $this->get('/login')->headers->get('Content-Security-Policy');

            expect($csp)->toContain('http://127.0.0.1:5173')
                ->toContain('ws://127.0.0.1:5173');
        } finally {
            if ($previous === null) {
                @unlink($hotFile);
            } else {
                file_put_contents($hotFile, $previous);
            }

            $this->app['env'] = 'testing';
        }
    });

    test('the vite dev origin is not allowed outside local development', function () {
        $csp = $this->get('/')->headers->get('Content-Security-Policy');

        expect($csp)->not->toContain('127.0.0.1:5173');
    });
});

describe('error pages', function () {
    test('an unknown public path renders the themed 404', function () {
        $this->get('/no-such-page-here')
            ->assertNotFound()
            ->assertInertia(fn ($page) => $page->component('Frontend/NotFound'));
    });

    test('error pages are noindex', function () {
        $html = $this->get('/no-such-page-here')->getContent();

        expect($html)->toContain('<meta name="robots" content="noindex,follow">');
    });

    test('admin 404s are not replaced by the public error page', function () {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)
            ->get('/admin/no-such-screen')
            ->assertNotFound();
    });
});

describe('caching', function () {
    test('published content appears immediately after the cache is invalidated', function () {
        $this->get('/'); // warm the cache

        Post::factory()->published()->create([
            'title' => 'Cache Busting Post',
            'user_id' => null,
        ]);

        Post::query()->latest('id')->first();

        $this->get('/')->assertInertia(fn ($page) => $page
            ->where('latest', fn ($latest) => collect($latest)->contains(
                fn ($post) => $post['title'] === 'Cache Busting Post'
            )));
    });

    test('menu changes are reflected without waiting for the cache to expire', function () {
        $menu = Menu::create(['name' => 'Main', 'slug' => 'main', 'location' => 'primary']);

        $this->get('/')->assertInertia(fn ($page) => $page->where('menus.primary', []));

        MenuItem::create([
            'menu_id' => $menu->id,
            'label' => 'Fresh Link',
            'type' => 'custom',
            'url' => '/fresh',
            'target' => '_self',
            'order' => 0,
        ]);

        $this->get('/')->assertInertia(fn ($page) => $page
            ->where('menus.primary.0.label', 'Fresh Link')
            ->where('menus.primary.0.url', '/fresh'));
    });

    test('the menu service caches repeated lookups', function () {
        MenuService::flush();

        $this->get('/');
        $first = MenuService::cacheKey('primary');

        expect(cache()->has($first))->toBeTrue();
    });
});

describe('production configuration', function () {
    test('the health check endpoint responds', function () {
        $this->get('/up')->assertOk();
    });

    test('theme settings changes flush the public cache', function () {
        $themes = app(ThemeService::class);
        $themes->saveDraft('solve-default', ['primary_color' => '#123456']);
        $themes->publish('solve-default');

        $this->get('/')->assertInertia(fn ($page) => $page
            ->where('site.colors.primary', '#123456'));
    });
});
