<?php

use App\Models\Post;
use App\Models\Role;
use App\Models\User;
use App\Services\Content\HtmlSanitizer;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->sanitizer = new HtmlSanitizer;
});

test('allowed formatting is preserved', function () {
    $html = '<h2>Title</h2><p><strong>Bold</strong> and <em>italic</em></p><ul><li>One</li></ul>';

    expect($this->sanitizer->clean($html))->toBe($html);
});

test('script tags are removed with their contents', function () {
    $result = $this->sanitizer->clean('<p>Safe</p><script>alert(1)</script>');

    expect($result)->not->toContain('script')
        ->and($result)->not->toContain('alert')
        ->and($result)->toContain('Safe');
});

test('inline event handlers are stripped', function () {
    $result = $this->sanitizer->clean('<p onclick="steal()">Text</p>');

    expect($result)->not->toContain('onclick')
        ->and($result)->toContain('Text');
});

test('javascript urls are removed from links', function () {
    $result = $this->sanitizer->clean('<a href="javascript:alert(1)">Click</a>');

    expect($result)->not->toContain('javascript:')
        ->and($result)->toContain('Click');
});

test('disallowed tags are unwrapped but text is kept', function () {
    $result = $this->sanitizer->clean('<p><font color="red">Hello</font></p>');

    expect($result)->not->toContain('<font')
        ->and($result)->toContain('Hello');
});

test('blank target links gain a safe rel attribute', function () {
    $result = $this->sanitizer->clean('<a href="https://example.com" target="_blank">Out</a>');

    expect($result)->toContain('rel="noopener noreferrer"');
});

test('images without a source are dropped', function () {
    $result = $this->sanitizer->clean('<p>Before</p><img alt="x"><p>After</p>');

    expect($result)->not->toContain('<img')
        ->and($result)->toContain('Before')
        ->and($result)->toContain('After');
});

describe('inline styles for images', function () {
    test('size and wrapping styles are kept on images', function () {
        $html = '<img src="/storage/a.png" alt="A" style="width: 50%; float: left; margin: 0 1rem 1rem 0;">';

        $result = $this->sanitizer->clean($html);

        expect($result)->toContain('width: 50%')
            ->and($result)->toContain('float: left')
            ->and($result)->toContain('margin: 0 1rem 1rem 0');
    });

    test('disallowed properties are dropped', function () {
        $html = '<img src="/storage/a.png" style="width: 30%; background-image: url(https://evil.test/x.png); position: fixed;">';

        $result = $this->sanitizer->clean($html);

        expect($result)->toContain('width: 30%')
            ->and($result)->not->toContain('background-image')
            ->and($result)->not->toContain('position');
    });

    test('style values cannot smuggle a url or expression', function () {
        $html = '<img src="/a.png" style="width: expression(alert(1)); float: url(javascript:alert(2));">';

        $result = $this->sanitizer->clean($html);

        expect($result)->not->toContain('expression')
            ->and($result)->not->toContain('javascript')
            ->and($result)->not->toContain('style=');
    });

    test('style is not allowed on arbitrary elements', function () {
        $result = $this->sanitizer->clean('<p style="position: fixed; top: 0;">Text</p>');

        expect($result)->not->toContain('style=')
            ->and($result)->toContain('Text');
    });

    test('a saved post keeps the image size and wrap', function () {
        $this->seed(RolePermissionSeeder::class);

        $role = Role::where('name', 'super-admin')->firstOrFail();
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->roles()->attach($role);

        $this->actingAs($admin)->post('/admin/posts', [
            'title' => 'Styled Image Post',
            'slug' => '',
            'content' => '<p>Lead.</p><img src="/storage/a.png" alt="A" style="width: 40%; float: right; margin: 0 0 1rem 1rem;"><p>Body text wrapping beside it.</p>',
            'status' => 'draft',
            'is_featured' => false,
            'category_ids' => [],
            'tag_ids' => [],
        ]);

        $content = Post::where('title', 'Styled Image Post')->firstOrFail()->content;

        expect($content)->toContain('width: 40%')
            ->and($content)->toContain('float: right');
    });
});

test('post content is sanitized when saved', function () {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(RolePermissionSeeder::class);

    $role = Role::where('name', 'super-admin')->firstOrFail();
    $admin = User::factory()->create(['email_verified_at' => now()]);
    $admin->roles()->attach($role);

    $this->actingAs($admin)->post('/admin/posts', [
        'title' => 'Sanitize Me',
        'slug' => '',
        'content' => '<p>Keep</p><script>alert("xss")</script>',
        'status' => 'draft',
        'is_featured' => false,
        'category_ids' => [],
        'tag_ids' => [],
    ]);

    $post = Post::where('title', 'Sanitize Me')->firstOrFail();

    expect($post->content)->not->toContain('<script')
        ->and($post->content)->toContain('Keep');
});
