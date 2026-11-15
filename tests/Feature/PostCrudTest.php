<?php

use App\Models\Post;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

function adminUser(): User
{
    $role = Role::where('name', 'super-admin')->firstOrFail();
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->roles()->attach($role);

    return $user;
}

test('admin can create a post', function () {
    $admin = adminUser();

    $this->actingAs($admin)
        ->post('/admin/posts', [
            'title' => 'My First Post',
            'slug' => '',
            'excerpt' => 'A short summary.',
            'content' => '<p>Body content here.</p>',
            'status' => 'draft',
            'is_featured' => false,
            'category_ids' => [],
            'tag_ids' => [],
        ])
        ->assertRedirect('/admin/posts');

    $this->assertDatabaseHas('posts', ['title' => 'My First Post', 'status' => 'draft']);
});

test('slug is auto-generated from title when left empty', function () {
    $admin = adminUser();

    $this->actingAs($admin)
        ->post('/admin/posts', [
            'title' => 'Auto Slug Post',
            'slug' => '',
            'status' => 'draft',
            'is_featured' => false,
            'category_ids' => [],
            'tag_ids' => [],
        ]);

    $this->assertDatabaseHas('posts', ['slug' => 'auto-slug-post']);
});

test('slug uniqueness is enforced on create', function () {
    $admin = adminUser();
    Post::factory()->create(['slug' => 'duplicate-slug']);

    $this->actingAs($admin)
        ->post('/admin/posts', [
            'title' => 'Duplicate Slug',
            'slug' => 'duplicate-slug',
            'status' => 'draft',
            'is_featured' => false,
            'category_ids' => [],
            'tag_ids' => [],
        ]);

    $this->assertDatabaseHas('posts', ['slug' => 'duplicate-slug-2']);
});

test('admin can update a post', function () {
    $admin = adminUser();
    $post = Post::factory()->create(['user_id' => $admin->id]);

    $this->actingAs($admin)
        ->put("/admin/posts/{$post->id}", [
            'title' => 'Updated Title',
            'slug' => '',
            'status' => 'published',
            'is_featured' => false,
            'category_ids' => [],
            'tag_ids' => [],
        ])
        ->assertRedirect("/admin/posts/{$post->id}/edit");

    $this->assertDatabaseHas('posts', ['id' => $post->id, 'title' => 'Updated Title', 'status' => 'published']);
});

test('admin can delete a post', function () {
    $admin = adminUser();
    $post = Post::factory()->create();

    $this->actingAs($admin)
        ->delete("/admin/posts/{$post->id}")
        ->assertRedirect('/admin/posts');

    $this->assertSoftDeleted('posts', ['id' => $post->id]);
});

test('title is required when creating post', function () {
    $admin = adminUser();

    $this->actingAs($admin)
        ->post('/admin/posts', [
            'title' => '',
            'status' => 'draft',
            'is_featured' => false,
            'category_ids' => [],
            'tag_ids' => [],
        ])
        ->assertSessionHasErrors('title');
});

test('post revision is recorded on save', function () {
    $admin = adminUser();

    $this->actingAs($admin)
        ->post('/admin/posts', [
            'title' => 'Revision Test Post',
            'slug' => '',
            'content' => 'First content.',
            'status' => 'draft',
            'is_featured' => false,
            'category_ids' => [],
            'tag_ids' => [],
        ]);

    $post = Post::where('title', 'Revision Test Post')->firstOrFail();

    $this->assertDatabaseHas('post_revisions', ['post_id' => $post->id, 'revision' => 1]);
});
