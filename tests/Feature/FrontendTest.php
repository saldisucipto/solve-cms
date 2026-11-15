<?php

use App\Models\Category;
use App\Models\Comment;
use App\Models\Page;
use App\Models\Post;
use App\Models\SiteSetting;
use App\Models\Tag;
use Database\Seeders\SiteSettingSeeder;
use Illuminate\Support\Facades\Cache;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(SiteSettingSeeder::class);
});

test('the homepage renders without any published content', function () {
    $this->get('/')->assertOk();
});

test('the homepage lists published posts but not drafts', function () {
    $published = Post::factory()->published()->create(['title' => 'Published Home Post', 'user_id' => null]);
    Post::factory()->create(['title' => 'Draft Home Post', 'user_id' => null]);

    $response = $this->get('/');

    $response->assertOk()
        ->assertSee('Published Home Post')
        ->assertDontSee('Draft Home Post');
});

test('the blog index renders published posts', function () {
    Post::factory()->published()->create(['title' => 'Blog Listing Post', 'user_id' => null]);

    $this->get('/blog')->assertOk()->assertSee('Blog Listing Post');
});

test('a single post renders its content and comments', function () {
    $post = Post::factory()->published()->create([
        'title' => 'Single Post Title',
        'slug' => 'single-post-title',
        'content' => '<p>Body paragraph text.</p>',
        'user_id' => null,
    ]);

    Comment::factory()->approved()->create([
        'post_id' => $post->id,
        'author_name' => 'Approved Commenter',
        'content' => 'This is an approved comment.',
    ]);

    Comment::factory()->create([
        'post_id' => $post->id,
        'author_name' => 'Pending Commenter',
        'status' => Comment::STATUS_PENDING,
    ]);

    $this->get('/blog/single-post-title')
        ->assertOk()
        ->assertSee('Single Post Title')
        ->assertSee('Body paragraph text.')
        ->assertSee('Approved Commenter')
        ->assertDontSee('Pending Commenter');
});

test('a draft post is not publicly reachable', function () {
    Post::factory()->create(['slug' => 'hidden-draft', 'status' => Post::STATUS_DRAFT, 'user_id' => null]);

    $this->get('/blog/hidden-draft')->assertNotFound();
});

test('a scheduled post is not public until its publish time', function () {
    Post::factory()->scheduled()->create(['slug' => 'future-post', 'user_id' => null]);

    $this->get('/blog/future-post')->assertNotFound();
});

test('a published page renders at the site root', function () {
    Page::factory()->published()->create([
        'title' => 'About Page',
        'slug' => 'about-test',
        'content' => '<p>About page body.</p>',
    ]);

    $this->get('/about-test')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Frontend/Page')
            ->where('page.slug', 'about-test')
            ->where('page.content', '<p>About page body.</p>'));
});

test('a draft page is not reachable', function () {
    Page::factory()->create(['slug' => 'draft-page', 'status' => Page::STATUS_DRAFT]);

    $this->get('/draft-page')->assertNotFound();
});

test('category archive lists its posts', function () {
    $category = Category::factory()->create(['name' => 'Guides', 'slug' => 'guides']);
    $post = Post::factory()->published()->create(['title' => 'Categorised Post', 'user_id' => null]);
    $post->categories()->attach($category);

    $this->get('/category/guides')->assertOk()->assertSee('Categorised Post');
});

test('tag archive lists its posts', function () {
    $tag = Tag::factory()->create(['name' => 'Laravel', 'slug' => 'laravel']);
    $post = Post::factory()->published()->create(['title' => 'Tagged Post', 'user_id' => null]);
    $post->tags()->attach($tag);

    $this->get('/tag/laravel')->assertOk()->assertSee('Tagged Post');
});

test('search finds matching posts', function () {
    Post::factory()->published()->create([
        'title' => 'A Post About Inertia',
        'slug' => 'post-about-inertia',
        'content' => '<p>Inertia makes single page apps.</p>',
        'user_id' => null,
    ]);

    $this->get('/search?q=Inertia')->assertOk()->assertSee('A Post About Inertia');
});

test('search with no matches returns an empty result set', function () {
    $this->get('/search?q=zzzznotfound')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Frontend/Search')
            ->where('query', 'zzzznotfound')
            ->where('total', 0)
            ->where('posts', []));
});

test('the 404 page renders the themed not found component', function () {
    $this->get('/definitely-not-a-page')
        ->assertNotFound()
        ->assertSee('Page not found');
});

test('a visitor can submit a comment that waits for moderation', function () {
    $post = Post::factory()->published()->create(['slug' => 'comment-target', 'user_id' => null]);

    $this->post("/blog/{$post->slug}/comments", [
        'author_name' => 'Visitor Name',
        'author_email' => 'visitor@example.com',
        'content' => 'A thoughtful comment.',
    ])->assertRedirect();

    $this->assertDatabaseHas('comments', [
        'post_id' => $post->id,
        'author_name' => 'Visitor Name',
        'status' => Comment::STATUS_PENDING,
    ]);
});

test('comment submission validates required fields', function () {
    $post = Post::factory()->published()->create(['slug' => 'validate-comment', 'user_id' => null]);

    $this->post("/blog/{$post->slug}/comments", [
        'author_name' => '',
        'author_email' => 'not-an-email',
        'content' => '',
    ])->assertSessionHasErrors(['author_name', 'author_email', 'content']);
});

test('seo metadata is rendered on the server for a post', function () {
    $post = Post::factory()->published()->create([
        'title' => 'SEO Rendered Post',
        'slug' => 'seo-rendered-post',
        'excerpt' => 'A description for the rendered post.',
        'user_id' => null,
    ]);

    $post->seo()->create([
        'title' => 'Custom SEO Title',
        'description' => 'Custom meta description.',
        'robots' => 'index,follow',
    ]);

    $html = $this->get('/blog/seo-rendered-post')->getContent();

    expect($html)
        ->toContain('<title>Custom SEO Title | Solve CMS</title>')
        ->toContain('<meta name="description" content="Custom meta description.">')
        ->toContain('<link rel="canonical" href="'.config('app.url').'/blog/seo-rendered-post">')
        ->toContain('application/ld+json')
        ->toContain('"@type":"Article"');
});

test('seo canonical uses the configured website url', function () {
    SiteSetting::where('key', 'site_url')->update(['value' => 'https://solve.example.com']);
    Cache::forget(SiteSetting::CACHE_KEY);

    Page::factory()->published()->create(['slug' => 'canonical-page', 'title' => 'Canonical Page']);

    $html = $this->get('/canonical-page')->getContent();

    expect($html)->toContain('href="https://solve.example.com/canonical-page"');
});

test('a second results page is noindex', function () {
    Post::factory()->published()->count(12)->create(['user_id' => null]);

    $html = $this->get('/blog?page=2')->getContent();

    expect($html)->toContain('<meta name="robots" content="noindex,follow">');
});

test('reserved paths are not captured by the page route', function () {
    $this->get('/login')->assertOk();
    $this->get('/blog')->assertOk();
    $this->get('/search')->assertOk();
});
