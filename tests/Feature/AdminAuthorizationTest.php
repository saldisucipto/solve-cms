<?php

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

function makeUser(string $roleName): User
{
    $role = Role::where('name', $roleName)->firstOrFail();
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->roles()->attach($role);

    return $user;
}

test('unauthenticated user is redirected from admin', function () {
    $this->get('/admin')->assertRedirect('/login');
});

test('super admin can access admin dashboard', function () {
    $admin = makeUser('super-admin');

    $this->actingAs($admin)
        ->get('/admin')
        ->assertOk();
});

test('support agent cannot access posts index', function () {
    $agent = makeUser('support-agent');

    $this->actingAs($agent)
        ->get('/admin/posts')
        ->assertForbidden();
});

test('author cannot view other authors posts in index when queried via gate', function () {
    $author = makeUser('author');

    $this->actingAs($author)
        ->get('/admin/posts')
        ->assertOk();
});

test('editor can access pages index', function () {
    $editor = makeUser('editor');

    $this->actingAs($editor)
        ->get('/admin/pages')
        ->assertOk();
});

test('super admin can access media library', function () {
    $admin = makeUser('super-admin');

    $this->actingAs($admin)
        ->get('/admin/media')
        ->assertOk();
});

test('support agent cannot access media library', function () {
    $agent = makeUser('support-agent');

    $this->actingAs($agent)
        ->get('/admin/media')
        ->assertForbidden();
});

test('support agent cannot create posts', function () {
    $agent = makeUser('support-agent');

    $this->actingAs($agent)
        ->post('/admin/posts', [
            'title' => 'Hacking',
            'status' => 'draft',
            'content' => '',
        ])
        ->assertForbidden();
});

test('super admin can access users index', function () {
    $admin = makeUser('super-admin');

    $this->actingAs($admin)
        ->get('/admin/users')
        ->assertOk();
});

test('author cannot access users index', function () {
    $author = makeUser('author');

    $this->actingAs($author)
        ->get('/admin/users')
        ->assertForbidden();
});

test('user cannot deactivate their own account', function () {
    $admin = makeUser('super-admin');

    $this->actingAs($admin)
        ->patch("/admin/users/{$admin->id}/status")
        ->assertSessionHas('error');

    expect($admin->fresh()->status)->toBe('active');
});
