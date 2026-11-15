<?php

use App\Models\Media;
use App\Models\Post;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SiteSettingSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(SiteSettingSeeder::class);

    Storage::fake('public');

    $role = Role::where('name', 'super-admin')->firstOrFail();
    $this->admin = User::factory()->create(['email_verified_at' => now()]);
    $this->admin->roles()->attach($role);
});

describe('media library endpoint', function () {
    test('it lists images with the data the picker needs', function () {
        $media = Media::factory()->create(['title' => 'Hero shot', 'alt_text' => 'A hero']);

        $this->actingAs($this->admin)
            ->getJson('/admin/media/library')
            ->assertOk()
            ->assertJsonPath('items.0.id', $media->id)
            ->assertJsonPath('items.0.title', 'Hero shot')
            ->assertJsonPath('items.0.alt_text', 'A hero')
            ->assertJsonStructure(['items' => [['id', 'url', 'thumbnail', 'title', 'alt_text', 'mime_type', 'width', 'height']]]);
    });

    test('it excludes non-image files', function () {
        Media::factory()->document()->create(['original_name' => 'brief.pdf']);
        Media::factory()->create(['title' => 'Picture']);

        $response = $this->actingAs($this->admin)->getJson('/admin/media/library');

        $titles = collect($response->json('items'))->pluck('title');

        expect($titles)->toContain('Picture')
            ->and($titles)->not->toContain('brief.pdf');
    });

    test('it can be searched', function () {
        Media::factory()->create(['title' => 'Sunset over the sea']);
        Media::factory()->create(['title' => 'Office desk']);

        $this->actingAs($this->admin)
            ->getJson('/admin/media/library?search=Sunset')
            ->assertOk()
            ->assertJsonCount(1, 'items')
            ->assertJsonPath('items.0.title', 'Sunset over the sea');
    });

    test('a user without media permission cannot list the library', function () {
        $role = Role::where('name', 'support-agent')->firstOrFail();
        $agent = User::factory()->create(['email_verified_at' => now()]);
        $agent->roles()->attach($role);

        $this->actingAs($agent)->getJson('/admin/media/library')->assertForbidden();
    });
});

describe('media upload for the editor', function () {
    test('an upload returns json when requested', function () {
        $response = $this->actingAs($this->admin)->post('/admin/media', [
            'file' => UploadedFile::fake()->image('inline.jpg', 800, 600),
        ], ['Accept' => 'application/json']);

        $response->assertCreated()
            ->assertJsonStructure(['media' => ['id', 'url', 'title', 'alt_text', 'mime_type', 'width', 'height']]);

        $media = Media::query()->latest('id')->firstOrFail();

        Storage::disk('public')->assertExists($media->path);

        expect($response->json('media.id'))->toBe($media->id);
    });

    test('an upload still redirects for the regular media screen', function () {
        $this->actingAs($this->admin)
            ->post('/admin/media', ['file' => UploadedFile::fake()->image('form.jpg')])
            ->assertRedirect();
    });

    test('only images may be inserted by the editor', function () {
        $this->actingAs($this->admin)
            ->post('/admin/media', [
                'file' => UploadedFile::fake()->create('payload.exe', 10, 'application/x-msdownload'),
            ], ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('file');
    });
});

describe('featured image on posts', function () {
    test('a post stores the chosen featured image', function () {
        $media = Media::factory()->create();

        $this->actingAs($this->admin)->post('/admin/posts', [
            'title' => 'Post With Hero',
            'slug' => '',
            'content' => '<p>Body</p>',
            'status' => 'published',
            'is_featured' => false,
            'featured_media_id' => $media->id,
            'category_ids' => [],
            'tag_ids' => [],
        ])->assertSessionHasNoErrors();

        $post = Post::where('title', 'Post With Hero')->firstOrFail();

        expect($post->featured_media_id)->toBe($media->id)
            ->and($post->featuredMedia?->id)->toBe($media->id);
    });

    test('the featured image is offered to the editor form', function () {
        $post = Post::factory()->create();
        $media = Media::factory()->create();
        $post->update(['featured_media_id' => $media->id]);

        $this->actingAs($this->admin)
            ->get("/admin/posts/{$post->id}/edit")
            ->assertInertia(fn ($page) => $page->where('post.featured_media_id', $media->id));
    });

    test('the featured image is cleared when set to null', function () {
        $media = Media::factory()->create();
        $post = Post::factory()->create(['featured_media_id' => $media->id]);

        $this->actingAs($this->admin)->put("/admin/posts/{$post->id}", [
            'title' => $post->title,
            'slug' => $post->slug,
            'status' => 'draft',
            'is_featured' => false,
            'featured_media_id' => null,
            'category_ids' => [],
            'tag_ids' => [],
        ])->assertSessionHasNoErrors();

        expect($post->fresh()->featured_media_id)->toBeNull();
    });

    test('a featured image must exist', function () {
        $this->actingAs($this->admin)->post('/admin/posts', [
            'title' => 'Bad Image Post',
            'slug' => '',
            'status' => 'draft',
            'is_featured' => false,
            'featured_media_id' => 99999,
            'category_ids' => [],
            'tag_ids' => [],
        ])->assertSessionHasErrors('featured_media_id');
    });
});

describe('images inside post content', function () {
    test('an img tag stored in the content survives sanitation', function () {
        $media = Media::factory()->create();

        $this->actingAs($this->admin)->post('/admin/posts', [
            'title' => 'Post With Inline Image',
            'slug' => '',
            'content' => '<p>Before</p><img src="'.$media->url().'" alt="Inline" loading="lazy" /><p>After</p>',
            'status' => 'draft',
            'is_featured' => false,
            'category_ids' => [],
            'tag_ids' => [],
        ]);

        $content = Post::where('title', 'Post With Inline Image')->firstOrFail()->content;

        expect($content)->toContain('<img')
            ->and($content)->toContain('alt="Inline"')
            ->and($content)->toContain('Before')
            ->and($content)->toContain('After');
    });

    test('a script injected through an image tag is removed', function () {
        $this->actingAs($this->admin)->post('/admin/posts', [
            'title' => 'Post With Evil Image',
            'slug' => '',
            'content' => '<img src="x" onerror="alert(1)" alt="x"><script>alert(2)</script>',
            'status' => 'draft',
            'is_featured' => false,
            'category_ids' => [],
            'tag_ids' => [],
        ]);

        $content = Post::where('title', 'Post With Evil Image')->firstOrFail()->content;

        expect($content)->not->toContain('onerror')
            ->and($content)->not->toContain('<script');
    });
});
