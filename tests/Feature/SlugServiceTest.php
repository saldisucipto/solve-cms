<?php

use App\Models\Post;
use App\Services\Content\SlugService;

test('unique slug returns base when no conflict', function () {
    $service = new SlugService;

    expect($service->unique(Post::class, 'Hello World'))->toBe('hello-world');
});

test('unique slug increments when base already exists', function () {
    Post::factory()->create(['slug' => 'hello-world']);

    $service = new SlugService;

    expect($service->unique(Post::class, 'Hello World'))->toBe('hello-world-2');
});

test('unique slug continues incrementing past 2', function () {
    Post::factory()->create(['slug' => 'hello-world']);
    Post::factory()->create(['slug' => 'hello-world-2']);

    $service = new SlugService;

    expect($service->unique(Post::class, 'Hello World'))->toBe('hello-world-3');
});

test('unique slug ignores the given id when updating', function () {
    $post = Post::factory()->create(['slug' => 'hello-world']);

    $service = new SlugService;

    expect($service->unique(Post::class, 'Hello World', $post->id))->toBe('hello-world');
});

test('empty slug falls back to random string', function () {
    $service = new SlugService;
    $result = $service->unique(Post::class, '');

    expect(strlen($result))->toBeGreaterThan(0);
});
