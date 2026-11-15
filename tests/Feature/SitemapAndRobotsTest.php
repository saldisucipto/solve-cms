<?php

use App\Models\Page;
use App\Models\Post;
use App\Models\Role;
use App\Models\User;
use App\Services\SiteSettings;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SiteSettingSeeder;

beforeEach(function () {
    $this->seed(SiteSettingSeeder::class);
    $this->seed(RolePermissionSeeder::class);
});

test('sitemap returns valid xml with the homepage', function () {
    $response = $this->get('/sitemap.xml');

    $response->assertOk()
        ->assertHeader('Content-Type', 'application/xml; charset=UTF-8');

    expect($response->getContent())
        ->toContain('<urlset')
        ->toContain('<loc>'.config('app.url').'/</loc>');
});

test('sitemap does not list draft content', function () {
    Post::factory()->create(['slug' => 'a-draft-post', 'status' => Post::STATUS_DRAFT]);
    Page::factory()->create(['slug' => 'a-draft-page', 'status' => Page::STATUS_DRAFT]);

    $content = $this->get('/sitemap.xml')->getContent();

    expect($content)->not->toContain('a-draft-post')
        ->and($content)->not->toContain('a-draft-page');
});

test('sitemap has no duplicate entries', function () {
    $content = $this->get('/sitemap.xml')->getContent();

    preg_match_all('/<loc>(.*?)<\/loc>/', $content, $matches);

    expect($matches[1])->toBe(array_values(array_unique($matches[1])));
});

test('sitemap returns 404 when disabled', function () {
    app(SiteSettings::class)->setMany(['seo_sitemap_enabled' => '0'], 'seo');

    $this->get('/sitemap.xml')->assertNotFound();
});

test('the admin sitemap page lists the same entries as the xml', function () {
    $role = Role::where('name', 'super-admin')->firstOrFail();
    $admin = User::factory()->create(['email_verified_at' => now()]);
    $admin->roles()->attach($role);

    Post::factory()->published()->create(['title' => 'Sitemap Page Post', 'user_id' => null]);

    $xml = $this->get('/sitemap.xml')->getContent();
    preg_match_all('/<loc>(.*?)<\/loc>/', $xml, $matches);

    $this->actingAs($admin)
        ->get('/admin/seo/sitemap')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Seo/Sitemap')
            ->where('enabled', true)
            ->where('total', count($matches[1]))
            ->has('entries'));
});

test('the admin sitemap page reports when generation is disabled', function () {
    app(SiteSettings::class)->setMany(['seo_sitemap_enabled' => '0'], 'seo');

    $role = Role::where('name', 'super-admin')->firstOrFail();
    $admin = User::factory()->create(['email_verified_at' => now()]);
    $admin->roles()->attach($role);

    $this->actingAs($admin)
        ->get('/admin/seo/sitemap')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('enabled', false));
});

test('a user without seo.view cannot open the admin sitemap page', function () {
    $role = Role::where('name', 'support-agent')->firstOrFail();
    $agent = User::factory()->create(['email_verified_at' => now()]);
    $agent->roles()->attach($role);

    $this->actingAs($agent)->get('/admin/seo/sitemap')->assertForbidden();
});

test('robots txt is served dynamically and points to the sitemap', function () {
    $response = $this->get('/robots.txt');

    $response->assertOk();

    $body = $response->getContent();

    expect($body)->toContain('User-agent: *')
        ->toContain('Disallow: /admin')
        ->toContain('Sitemap:');
});

test('robots txt omits the sitemap line when sitemap is disabled', function () {
    app(SiteSettings::class)->setMany(['seo_sitemap_enabled' => '0'], 'seo');

    $body = $this->get('/robots.txt')->getContent();

    expect($body)->not->toContain('Sitemap:');
});
