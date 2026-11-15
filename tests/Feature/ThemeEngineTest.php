<?php

use App\Models\Role;
use App\Models\Theme;
use App\Models\User;
use App\Services\Theme\ThemeRegistry;
use App\Services\Theme\ThemeService;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SiteSettingSeeder;
use Database\Seeders\ThemeSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(SiteSettingSeeder::class);
    $this->seed(ThemeSeeder::class);

    $this->themes = app(ThemeService::class);

    $role = Role::where('name', 'super-admin')->firstOrFail();
    $this->admin = User::factory()->create(['email_verified_at' => now()]);
    $this->admin->roles()->attach($role);
});

describe('theme registry', function () {
    test('discovers the themes shipped with the application', function () {
        $slugs = collect(app(ThemeRegistry::class)->all())->pluck('slug');

        expect($slugs)->toContain('solve-default')
            ->and($slugs)->toContain('solve-minimal');
    });

    test('reads theme metadata and defaults', function () {
        $manifest = app(ThemeRegistry::class)->find('solve-default');

        expect($manifest)->not->toBeNull()
            ->and($manifest['name'])->toBe('Solve Default')
            ->and($manifest['metadata']['defaults'])->toHaveKey('primary_color')
            ->and($manifest['metadata']['templates']['blog'])->toContain('grid');
    });

    test('rejects an invalid theme slug', function () {
        $this->expectException(RuntimeException::class);

        app(ThemeRegistry::class)->assertValidSlug('../evil');
    });
});

describe('theme activation', function () {
    test('the default theme is active after seeding', function () {
        expect($this->themes->activeSlug())->toBe('solve-default');
    });

    test('activating another theme switches the active theme', function () {
        expect($this->themes->activate('solve-minimal'))->toBeTrue()
            ->and($this->themes->activeSlug())->toBe('solve-minimal');
    });

    test('only one theme is active at a time', function () {
        $this->themes->activate('solve-minimal');

        expect(Theme::where('is_active', true)->count())->toBe(1);
    });

    test('activating an unknown theme fails', function () {
        expect($this->themes->activate('does-not-exist'))->toBeFalse();
    });

    test('an admin can activate a theme over http', function () {
        $this->actingAs($this->admin)
            ->post('/admin/themes/solve-minimal/activate')
            ->assertSessionHasNoErrors();

        expect($this->themes->activeSlug())->toBe('solve-minimal');
    });

    test('an author cannot activate a theme', function () {
        $role = Role::where('name', 'author')->firstOrFail();
        $author = User::factory()->create(['email_verified_at' => now()]);
        $author->roles()->attach($role);

        $this->actingAs($author)
            ->post('/admin/themes/solve-minimal/activate')
            ->assertForbidden();
    });
});

describe('theme customizer', function () {
    test('saving a draft does not change the published settings', function () {
        $this->themes->saveDraft('solve-default', ['primary_color' => '#ff0000']);

        expect($this->themes->draft('solve-default')['primary_color'])->toBe('#ff0000')
            ->and($this->themes->settings('solve-default')['primary_color'])->toBe('#2563eb');
    });

    test('publishing promotes the draft to the live settings', function () {
        $this->themes->saveDraft('solve-default', ['primary_color' => '#ff0000']);
        $this->themes->publish('solve-default');

        expect($this->themes->settings('solve-default')['primary_color'])->toBe('#ff0000')
            ->and($this->themes->hasDraft('solve-default'))->toBeFalse();
    });

    test('resetting restores the manifest defaults', function () {
        $this->themes->saveDraft('solve-default', ['primary_color' => '#ff0000', 'header_layout' => 'centered']);
        $this->themes->publish('solve-default');
        $this->themes->reset('solve-default');

        $settings = $this->themes->settings('solve-default');

        expect($settings['primary_color'])->toBe('#2563eb')
            ->and($settings['header_layout'])->toBe('classic');
    });

    test('unknown appearance keys are discarded', function () {
        $this->themes->saveDraft('solve-default', ['primary_color' => '#123456', 'evil_key' => 'x']);

        expect($this->themes->draft('solve-default'))->not->toHaveKey('evil_key');
    });

    test('an admin can save a draft over http', function () {
        $this->actingAs($this->admin)
            ->put('/admin/customizer/draft', [
                'site_name' => 'Solve CMS',
                'appearance' => [
                    'primary_color' => '#010203',
                    'secondary_color' => '#0f172a',
                    'background_color' => '#ffffff',
                    'text_color' => '#0f172a',
                    'link_color' => '#2563eb',
                    'font_family' => 'Instrument Sans',
                    'heading_font' => 'Instrument Sans',
                    'body_font' => 'Instrument Sans',
                    'font_size_base' => 16,
                    'line_height' => 1.7,
                    'container_width' => 1100,
                    'header_layout' => 'centered',
                    'footer_layout' => 'simple',
                    'sidebar_enabled' => true,
                    'blog_layout' => 'list',
                    'homepage_layout' => 'featured',
                    'post_card' => 'image-top',
                    'show_featured_section' => true,
                    'show_search' => true,
                ],
            ])
            ->assertSessionHasNoErrors();

        expect($this->themes->draft('solve-default')['primary_color'])->toBe('#010203');
    });

    test('the customizer rejects out of range values', function () {
        $this->actingAs($this->admin)
            ->put('/admin/customizer/draft', [
                'site_name' => 'Solve CMS',
                'appearance' => ['container_width' => 100, 'primary_color' => '#000000'],
            ])
            ->assertSessionHasErrors('appearance.container_width');
    });

    test('only a user with permission can open the customizer', function () {
        $role = Role::where('name', 'support-agent')->firstOrFail();
        $agent = User::factory()->create(['email_verified_at' => now()]);
        $agent->roles()->attach($role);

        $this->actingAs($agent)
            ->get('/admin/customizer')
            ->assertForbidden();
    });
});

describe('frontend theme application', function () {
    test('the public site exposes the active theme settings', function () {
        $this->get('/')->assertInertia(fn ($page) => $page
            ->where('site.theme.slug', 'solve-default')
            ->where('site.headerLayout', 'classic')
            ->where('site.colors.primary', '#2563eb'));
    });

    test('publishing customisations changes the public site', function () {
        $this->themes->saveDraft('solve-default', ['primary_color' => '#abcdef']);
        $this->themes->publish('solve-default');

        $this->get('/')->assertInertia(fn ($page) => $page
            ->where('site.colors.primary', '#abcdef'));
    });

    test('switching theme changes the public site appearance', function () {
        $this->themes->activate('solve-minimal');

        $this->get('/')->assertInertia(fn ($page) => $page
            ->where('site.theme.slug', 'solve-minimal')
            ->where('site.headerLayout', 'centered')
            ->where('site.blogLayout', 'list'));
    });

    test('a guest never sees unpublished draft settings', function () {
        $this->themes->saveDraft('solve-default', ['primary_color' => '#ff0000']);

        $this->get('/')->assertInertia(fn ($page) => $page
            ->where('site.colors.primary', '#2563eb')
            ->where('site.theme.preview', false));
    });

    test('an admin previewing the site sees the draft', function () {
        $this->themes->saveDraft('solve-default', ['primary_color' => '#ff0000']);

        $this->actingAs($this->admin)
            ->get('/?theme-preview=1')
            ->assertInertia(fn ($page) => $page
                ->where('site.colors.primary', '#ff0000')
                ->where('site.theme.preview', true));
    });

    test('a non privileged user cannot preview draft settings', function () {
        $this->themes->saveDraft('solve-default', ['primary_color' => '#ff0000']);

        $role = Role::where('name', 'author')->firstOrFail();
        $author = User::factory()->create(['email_verified_at' => now()]);
        $author->roles()->attach($role);

        $this->actingAs($author)
            ->get('/?theme-preview=1')
            ->assertInertia(fn ($page) => $page
                ->where('site.colors.primary', '#2563eb')
                ->where('site.theme.preview', false));
    });

    test('theme stylesheet is exposed to the public layout', function () {
        $this->get('/')->assertInertia(fn ($page) => $page
            ->where('themeCss', fn ($css) => is_string($css) && str_contains($css, 'fe-theme-solve-default')));
    });
});
