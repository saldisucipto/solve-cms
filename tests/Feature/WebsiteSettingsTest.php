<?php

use App\Models\ActivityLog;
use App\Models\Media;
use App\Models\Role;
use App\Models\SiteSetting;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SiteSettingSeeder;
use Database\Seeders\ThemeSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(SiteSettingSeeder::class);
    $this->seed(ThemeSeeder::class);

    Storage::fake('public');

    $role = Role::where('name', 'super-admin')->firstOrFail();
    $this->admin = User::factory()->create(['email_verified_at' => now()]);
    $this->admin->roles()->attach($role);
});

describe('website settings', function () {
    test('the settings page loads with the current values', function () {
        $this->actingAs($this->admin)
            ->get('/admin/settings')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Settings/Index')
                ->where('settings.site_name', 'Solve CMS')
                ->has('timezones'));
    });

    test('settings can be saved', function () {
        $this->actingAs($this->admin)
            ->put('/admin/settings', [
                'site_name' => 'My Studio',
                'site_tagline' => 'Craft and code',
                'site_description' => 'A studio site.',
                'site_url' => 'https://studio.example.com',
                'posts_per_page' => 8,
                'timezone' => 'Asia/Jakarta',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('site_settings', ['key' => 'site_name', 'value' => 'My Studio']);
        $this->assertDatabaseHas('site_settings', ['key' => 'posts_per_page', 'value' => '8']);
    });

    test('saving settings is recorded in the activity log', function () {
        $this->actingAs($this->admin)->put('/admin/settings', [
            'site_name' => 'Logged Studio',
            'site_url' => 'https://example.com',
            'posts_per_page' => 10,
            'timezone' => 'UTC',
        ]);

        $this->assertDatabaseHas('activity_logs', ['event' => 'settings.updated']);
    });

    test('settings are validated', function () {
        $this->actingAs($this->admin)
            ->put('/admin/settings', [
                'site_name' => '',
                'site_url' => 'not-a-url',
                'posts_per_page' => 500,
                'timezone' => 'Mars/Olympus',
            ])
            ->assertSessionHasErrors(['site_name', 'site_url', 'posts_per_page', 'timezone']);
    });

    test('an administrator without settings.manage cannot open settings', function () {
        $role = Role::where('name', 'author')->firstOrFail();
        $author = User::factory()->create(['email_verified_at' => now()]);
        $author->roles()->attach($role);

        $this->actingAs($author)->get('/admin/settings')->assertForbidden();
    });
});

describe('branding uploads', function () {
    test('a logo can be uploaded and is stored in the settings', function () {
        $this->actingAs($this->admin)
            ->post('/admin/settings/branding', [
                'type' => 'logo',
                'file' => UploadedFile::fake()->image('logo.png', 400, 120),
            ])
            ->assertSessionHasNoErrors();

        $media = Media::query()->latest('id')->firstOrFail();

        Storage::disk('public')->assertExists($media->path);

        $this->assertDatabaseHas('site_settings', ['key' => 'logo_path']);

        expect(SiteSetting::get('logo_path'))->toContain('/storage/');
    });

    test('a favicon can be uploaded', function () {
        $this->actingAs($this->admin)
            ->post('/admin/settings/branding', [
                'type' => 'favicon',
                'file' => UploadedFile::fake()->image('favicon.png', 64, 64),
            ])
            ->assertSessionHasNoErrors();

        expect(SiteSetting::get('favicon_path'))->not->toBeNull();
    });

    test('only images are accepted', function () {
        $this->actingAs($this->admin)
            ->post('/admin/settings/branding', [
                'type' => 'logo',
                'file' => UploadedFile::fake()->create('script.exe', 10, 'application/x-msdownload'),
            ])
            ->assertSessionHasErrors('file');

        expect(SiteSetting::get('logo_path'))->toBeNull();
    });

    test('the logo can be removed', function () {
        SiteSetting::updateOrCreate(
            ['key' => 'logo_path'],
            ['group' => 'general', 'value' => '/storage/media/logo.png', 'type' => 'string'],
        );
        Cache::forget(SiteSetting::CACHE_KEY);

        $this->actingAs($this->admin)
            ->delete('/admin/settings/branding', ['type' => 'logo'])
            ->assertSessionHasNoErrors();

        expect(SiteSetting::get('logo_path'))->toBeNull();
    });

    test('the public logo is used by the public site only', function () {
        SiteSetting::updateOrCreate(
            ['key' => 'logo_path'],
            ['group' => 'general', 'value' => '/storage/media/logo.png', 'type' => 'string'],
        );
        Cache::forget(SiteSetting::CACHE_KEY);

        $this->get('/')
            ->assertInertia(fn ($page) => $page->where('site.logo', '/storage/media/logo.png'));

        // The dashboard keeps its own logo, so it must stay empty here.
        $this->actingAs($this->admin)
            ->get('/admin')
            ->assertInertia(fn ($page) => $page->where('branding.logo', null));
    });

    test('a user without permission cannot upload branding', function () {
        $role = Role::where('name', 'editor')->firstOrFail();
        $editor = User::factory()->create(['email_verified_at' => now()]);
        $editor->roles()->attach($role);

        $this->actingAs($editor)
            ->post('/admin/settings/branding', [
                'type' => 'logo',
                'file' => UploadedFile::fake()->image('logo.png'),
            ])
            ->assertForbidden();
    });
});

describe('separate dashboard and public logos', function () {
    test('uploading a public logo leaves the dashboard logo untouched', function () {
        $this->actingAs($this->admin)->post('/admin/settings/branding', [
            'type' => 'dashboard_logo',
            'file' => UploadedFile::fake()->image('dash.png', 200, 60),
        ])->assertSessionHasNoErrors();

        $dashboard = SiteSetting::get('dashboard_logo_path');

        $this->actingAs($this->admin)->post('/admin/settings/branding', [
            'type' => 'logo',
            'file' => UploadedFile::fake()->image('public.png', 300, 80),
        ])->assertSessionHasNoErrors();

        expect(SiteSetting::get('dashboard_logo_path'))->toBe($dashboard)
            ->and(SiteSetting::get('logo_path'))->not->toBe($dashboard);
    });

    test('the dashboard uses the dashboard logo and the site uses the public logo', function () {
        Cache::forget(SiteSetting::CACHE_KEY);

        $this->actingAs($this->admin)->post('/admin/settings/branding', [
            'type' => 'dashboard_logo',
            'file' => UploadedFile::fake()->image('dash.png', 200, 60),
        ]);

        $this->actingAs($this->admin)->post('/admin/settings/branding', [
            'type' => 'logo',
            'file' => UploadedFile::fake()->image('public.png', 300, 80),
        ]);

        Cache::forget(SiteSetting::CACHE_KEY);

        $this->actingAs($this->admin)
            ->get('/admin')
            ->assertInertia(fn ($page) => $page->where('branding.logo', SiteSetting::get('dashboard_logo_path')));

        $this->get('/')
            ->assertInertia(fn ($page) => $page->where('site.logo', SiteSetting::get('logo_path')));
    });

    test('each logo can be removed without affecting the other', function () {
        foreach (['dashboard_logo', 'logo'] as $type) {
            $this->actingAs($this->admin)->post('/admin/settings/branding', [
                'type' => $type,
                'file' => UploadedFile::fake()->image($type.'.png', 200, 60),
            ]);
        }

        Cache::forget(SiteSetting::CACHE_KEY);

        $this->actingAs($this->admin)
            ->delete('/admin/settings/branding', ['type' => 'dashboard_logo'])
            ->assertSessionHasNoErrors();

        expect(SiteSetting::get('dashboard_logo_path'))->toBeNull()
            ->and(SiteSetting::get('logo_path'))->not->toBeNull();
    });

    test('an unknown branding type is rejected', function () {
        $this->actingAs($this->admin)
            ->post('/admin/settings/branding', [
                'type' => 'not_a_logo',
                'file' => UploadedFile::fake()->image('x.png'),
            ])
            ->assertSessionHasErrors('type');

        $this->actingAs($this->admin)
            ->delete('/admin/settings/branding', ['type' => 'not_a_logo'])
            ->assertNotFound();
    });

    test('the settings page exposes both logo values', function () {
        $this->actingAs($this->admin)->post('/admin/settings/branding', [
            'type' => 'dashboard_logo',
            'file' => UploadedFile::fake()->image('dash.png'),
        ]);

        Cache::forget(SiteSetting::CACHE_KEY);

        $this->actingAs($this->admin)
            ->get('/admin/settings')
            ->assertInertia(fn ($page) => $page
                ->has('settings.dashboard_logo_path')
                ->has('settings.logo_path'));
    });
});

describe('activity log', function () {
    test('the activity page lists recorded actions', function () {
        ActivityLog::create([
            'user_id' => $this->admin->id,
            'event' => 'post.created',
            'description' => 'Created post "Hello"',
            'ip_address' => '127.0.0.1',
        ]);

        $this->actingAs($this->admin)
            ->get('/admin/activity')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Activity/Index')
                ->where('logs.data.0.event', 'post.created')
                ->has('events')
                ->has('users'));
    });

    test('activity can be filtered by event and search term', function () {
        ActivityLog::create(['user_id' => $this->admin->id, 'event' => 'post.created', 'description' => 'Created post "Alpha"']);
        ActivityLog::create(['user_id' => $this->admin->id, 'event' => 'page.deleted', 'description' => 'Deleted page "Beta"']);

        $this->actingAs($this->admin)
            ->get('/admin/activity?event=post.created')
            ->assertInertia(fn ($page) => $page
                ->has('logs.data', 1)
                ->where('logs.data.0.event', 'post.created'));

        $this->actingAs($this->admin)
            ->get('/admin/activity?search=Beta')
            ->assertInertia(fn ($page) => $page
                ->has('logs.data', 1)
                ->where('logs.data.0.event', 'page.deleted'));
    });

    test('an editor without activity.view cannot read the log', function () {
        $role = Role::where('name', 'editor')->firstOrFail();
        $editor = User::factory()->create(['email_verified_at' => now()]);
        $editor->roles()->attach($role);

        $this->actingAs($editor)->get('/admin/activity')->assertForbidden();
    });

    test('guests are redirected to the login page', function () {
        $this->get('/admin/activity')->assertRedirect('/login');
    });
});
