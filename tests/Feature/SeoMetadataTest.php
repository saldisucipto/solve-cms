<?php

use App\Models\Post;
use App\Models\Role;
use App\Models\SeoMetadata;
use App\Models\User;
use App\Services\Seo\SeoService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('seo metadata is saved for a post', function () {
    $post = Post::factory()->create();
    $service = new SeoService;

    $service->save($post, [
        'title' => 'Custom SEO Title',
        'description' => 'Custom meta description.',
        'focus_keyword' => 'laravel',
        'robots' => 'index,follow',
    ]);

    $this->assertDatabaseHas('seo_metadata', [
        'model_type' => Post::class,
        'model_id' => $post->id,
        'title' => 'Custom SEO Title',
        'focus_keyword' => 'laravel',
    ]);
});

test('seo metadata is updated when saved again', function () {
    $post = Post::factory()->create();
    $service = new SeoService;

    $service->save($post, ['title' => 'First Title', 'robots' => 'index,follow']);
    $service->save($post, ['title' => 'Updated Title', 'robots' => 'index,follow']);

    expect(SeoMetadata::where('model_type', Post::class)->where('model_id', $post->id)->count())->toBe(1);

    $this->assertDatabaseHas('seo_metadata', [
        'model_id' => $post->id,
        'title' => 'Updated Title',
    ]);
});

test('seo metadata is deleted when empty data is saved', function () {
    $post = Post::factory()->create();
    $service = new SeoService;

    $service->save($post, ['title' => 'Title', 'robots' => 'index,follow']);
    $service->save($post, []);

    expect(SeoMetadata::where('model_id', $post->id)->count())->toBe(0);
});

test('admin can save post with seo via http', function () {
    $role = Role::where('name', 'super-admin')->firstOrFail();
    $admin = User::factory()->create(['email_verified_at' => now()]);
    $admin->roles()->attach($role);

    $this->actingAs($admin)
        ->post('/admin/posts', [
            'title' => 'SEO Post Test',
            'slug' => '',
            'status' => 'draft',
            'is_featured' => false,
            'category_ids' => [],
            'tag_ids' => [],
            'seo' => [
                'title' => 'SEO Title',
                'description' => 'SEO description',
                'robots' => 'index,follow',
            ],
        ]);

    $post = Post::where('title', 'SEO Post Test')->firstOrFail();

    $this->assertDatabaseHas('seo_metadata', [
        'model_type' => Post::class,
        'model_id' => $post->id,
        'title' => 'SEO Title',
    ]);
});
