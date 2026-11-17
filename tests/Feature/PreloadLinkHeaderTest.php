<?php

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Vite;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $manifest = public_path('build/testing-manifest.json');

    if (! is_dir(dirname($manifest))) {
        mkdir(dirname($manifest), 0755, true);
    }

    $entries = [];

    foreach (['resources/css/app.css', 'resources/js/app.js'] as $entry) {
        $entries[$entry] = [
            'file' => 'assets/app-test.js',
            'src' => $entry,
            'isEntry' => true,
        ];
    }

    foreach (glob(resource_path('js/pages/**/*.vue')) ?: [] as $page) {
        $entry = 'resources/js/pages/'.str_replace(resource_path('js/pages').'/', '', $page);

        $entries[$entry] = [
            'file' => 'assets/'.md5($entry).'.js',
            'src' => $entry,
            'isEntry' => true,
        ];
    }

    file_put_contents($manifest, json_encode($entries));

    Vite::useManifestFilename('testing-manifest.json');
});

function preloadAdmin(): User
{
    $role = Role::where('name', 'super-admin')->firstOrFail();
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->roles()->attach($role);

    return $user;
}

test('admin pages do not receive preload link headers', function () {
    $this->actingAs(preloadAdmin())
        ->get('/admin')
        ->assertOk()
        ->assertHeaderMissing('Link');
});

test('the link header is capped on public pages', function () {
    $response = $this->get('/');

    $response->assertOk();

    $link = (string) $response->headers->get('Link');

    expect(substr_count($link, 'rel="preload"'))->toBeLessThanOrEqual(6);
});
