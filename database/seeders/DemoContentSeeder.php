<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::where('email', 'admin@solvecms.test')->firstOrFail();

        $categories = collect([
            'Engineering',
            'Product',
            'SEO',
        ])->map(fn (string $name) => Category::updateOrCreate(
            ['slug' => Str::slug($name)],
            ['name' => $name, 'description' => "Articles about {$name}.", 'order' => 0],
        ));

        $tags = collect(['laravel', 'vue', 'inertia', 'seo', 'content'])
            ->map(fn (string $name) => Tag::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => Str::title($name)],
            ));

        $posts = [
            [
                'title' => 'Welcome to Solve CMS',
                'excerpt' => 'An introduction to your new SEO-first content management system.',
                'content' => '<p>Solve CMS combines content management, theming and technical SEO in a single Laravel application.</p><h2>What you get</h2><p>Publish posts and pages, organise them with categories and tags, and control every piece of metadata that search engines read.</p>',
                'is_featured' => true,
            ],
            [
                'title' => 'How the SEO Analyzer Works',
                'excerpt' => 'Practical guidance on the checks Solve CMS runs against your content.',
                'content' => '<p>The SEO analyzer inspects your actual content and reports actionable improvements.</p><h2>Title length</h2><p>Keep your SEO title between 30 and 60 characters so search engines display it fully.</p>',
                'is_featured' => false,
            ],
            [
                'title' => 'Building Themes for Solve CMS',
                'excerpt' => 'Understand the theme directory structure and how templates resolve.',
                'content' => '<p>Each theme declares its metadata and ships its own layouts, templates and assets.</p><h2>Template resolution</h2><p>The active theme decides which template renders each public route.</p>',
                'is_featured' => false,
            ],
            [
                'title' => 'A Draft in Progress',
                'excerpt' => 'This post demonstrates draft status.',
                'content' => '<p>Drafts stay invisible on the public website until you publish them.</p>',
                'is_featured' => false,
                'status' => Post::STATUS_DRAFT,
            ],
        ];

        foreach ($posts as $index => $data) {
            $post = Post::updateOrCreate(
                ['slug' => Str::slug($data['title'])],
                [
                    'user_id' => $author->id,
                    'title' => $data['title'],
                    'excerpt' => $data['excerpt'],
                    'content' => $data['content'],
                    'status' => $data['status'] ?? Post::STATUS_PUBLISHED,
                    'is_featured' => $data['is_featured'],
                    'published_at' => ($data['status'] ?? Post::STATUS_PUBLISHED) === Post::STATUS_PUBLISHED
                        ? now()->subDays(count($posts) - $index)
                        : null,
                ],
            );

            $post->categories()->syncWithoutDetaching([
                $categories[$index % $categories->count()]->id,
            ]);

            $post->tags()->syncWithoutDetaching([
                $tags[$index % $tags->count()]->id,
                $tags[($index + 2) % $tags->count()]->id,
            ]);
        }

        $about = Page::updateOrCreate(
            ['slug' => 'about'],
            [
                'user_id' => $author->id,
                'title' => 'About',
                'excerpt' => 'Learn more about this website.',
                'content' => '<p>This page is managed from the Solve CMS dashboard.</p>',
                'status' => Page::STATUS_PUBLISHED,
                'published_at' => now()->subWeek(),
            ],
        );

        $contact = Page::updateOrCreate(
            ['slug' => 'contact'],
            [
                'user_id' => $author->id,
                'title' => 'Contact',
                'excerpt' => 'Get in touch with the team.',
                'content' => '<p>Start a live chat from any page on this website and an agent will reply.</p>',
                'status' => Page::STATUS_PUBLISHED,
                'published_at' => now()->subWeek(),
            ],
        );

        $firstPost = Post::where('slug', 'welcome-to-solve-cms')->first();

        if ($firstPost) {
            $comment = Comment::updateOrCreate(
                ['post_id' => $firstPost->id, 'author_email' => 'visitor@example.com'],
                [
                    'author_name' => 'A Curious Visitor',
                    'content' => 'This looks like a solid starting point. Nice work!',
                    'status' => Comment::STATUS_APPROVED,
                    'approved_at' => now(),
                ],
            );

            Comment::updateOrCreate(
                ['post_id' => $firstPost->id, 'author_email' => 'author@solvecms.test'],
                [
                    'parent_id' => $comment->id,
                    'author_name' => 'Solve Administrator',
                    'content' => 'Thanks for reading. More guides are on the way.',
                    'status' => Comment::STATUS_APPROVED,
                    'approved_at' => now(),
                ],
            );
        }

        $menu = Menu::updateOrCreate(
            ['slug' => 'primary'],
            ['name' => 'Primary Navigation', 'location' => 'primary'],
        );

        $menu->items()->delete();

        $items = [
            ['label' => 'Home', 'url' => '/', 'order' => 0],
            ['label' => 'Blog', 'url' => '/blog', 'order' => 1],
            ['label' => $about->title, 'type' => 'page', 'linkable' => $about, 'order' => 2],
            ['label' => $contact->title, 'type' => 'page', 'linkable' => $contact, 'order' => 3],
        ];

        foreach ($items as $item) {
            MenuItem::create([
                'menu_id' => $menu->id,
                'label' => $item['label'],
                'url' => $item['url'] ?? null,
                'type' => $item['type'] ?? 'custom',
                'linkable_id' => isset($item['linkable']) ? $item['linkable']->id : null,
                'linkable_type' => isset($item['linkable']) ? $item['linkable']::class : null,
                'order' => $item['order'],
            ]);
        }

        Menu::updateOrCreate(
            ['slug' => 'footer'],
            ['name' => 'Footer Navigation', 'location' => 'footer'],
        );
    }
}
