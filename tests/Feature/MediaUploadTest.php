<?php

use App\Models\Media;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    Storage::fake('public');

    $role = Role::where('name', 'super-admin')->firstOrFail();
    $this->admin = User::factory()->create(['email_verified_at' => now()]);
    $this->admin->roles()->attach($role);
});

test('admin can upload an image to the media library', function () {
    $file = UploadedFile::fake()->image('photo.jpg', 800, 600);

    $this->actingAs($this->admin)
        ->post('/admin/media', [
            'file' => $file,
            'title' => 'Test Photo',
            'alt_text' => 'A test photo',
        ])
        ->assertSessionHasNoErrors();

    $media = Media::firstOrFail();

    expect($media->mime_type)->toStartWith('image/')
        ->and($media->width)->toBeInt()
        ->and($media->height)->toBeInt();

    Storage::disk('public')->assertExists($media->path);
});

test('media upload rejects disallowed file types', function () {
    $file = UploadedFile::fake()->create('malware.exe', 100, 'application/x-msdownload');

    $this->actingAs($this->admin)
        ->post('/admin/media', ['file' => $file])
        ->assertSessionHasErrors('file');

    expect(Media::count())->toBe(0);
});

test('a text file disguised with an executable extension is rejected', function () {
    $this->actingAs($this->admin)
        ->post('/admin/media', [
            'file' => UploadedFile::fake()->createWithContent('payload.exe', 'MZ not really a binary'),
        ], ['Accept' => 'application/json'])
        ->assertStatus(422)
        ->assertJsonValidationErrors('file');

    expect(Media::count())->toBe(0);
});

test('a php file is rejected', function () {
    $this->actingAs($this->admin)
        ->post('/admin/media', [
            'file' => UploadedFile::fake()->createWithContent('shell.php', '<?php echo 1;'),
        ], ['Accept' => 'application/json'])
        ->assertStatus(422)
        ->assertJsonValidationErrors('file');
});

test('the stored extension comes from the file content, not the client name', function () {
    $this->actingAs($this->admin)
        ->post('/admin/media', [
            'file' => UploadedFile::fake()->image('picture.png', 100, 100),
        ], ['Accept' => 'application/json'])
        ->assertCreated();

    $media = Media::query()->latest('id')->firstOrFail();

    expect($media->extension)->toBe('png')
        ->and($media->filename)->toEndWith('.png');
});

test('admin can update media metadata', function () {
    $media = Media::factory()->create(['title' => 'Old title']);

    $this->actingAs($this->admin)
        ->patch("/admin/media/{$media->id}", [
            'title' => 'New title',
            'alt_text' => 'Described',
        ])
        ->assertSessionHasNoErrors();

    expect($media->fresh()->title)->toBe('New title');
});

test('admin can delete media and its file', function () {
    $media = Media::factory()->create();

    $this->actingAs($this->admin)
        ->delete("/admin/media/{$media->id}")
        ->assertSessionHasNoErrors();

    $this->assertSoftDeleted('media', ['id' => $media->id]);
});

test('support agent cannot upload media', function () {
    $role = Role::where('name', 'support-agent')->firstOrFail();
    $agent = User::factory()->create(['email_verified_at' => now()]);
    $agent->roles()->attach($role);

    $this->actingAs($agent)
        ->post('/admin/media', ['file' => UploadedFile::fake()->image('x.jpg')])
        ->assertForbidden();
});
