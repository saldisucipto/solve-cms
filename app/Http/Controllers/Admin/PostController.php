<?php

namespace App\Http\Controllers\Admin;

use App\Events\ContentPublished;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePostRequest;
use App\Http\Requests\Admin\UpdatePostRequest;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Services\ActivityLogger;
use App\Services\Content\PostService;
use App\Services\Content\SlugService;
use App\Services\Seo\SeoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
    public function __construct(
        private readonly PostService $posts,
        private readonly SlugService $slugs,
        private readonly SeoService $seo,
        private readonly ActivityLogger $activity,
    ) {}

    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Post::class);

        $user = $request->user();

        $posts = Post::query()
            ->with(['author:id,name', 'categories:id,name'])
            ->when(! $user->hasPermission('posts.update'), fn ($query) => $query->where('user_id', $user->id))
            ->when($request->string('status')->value(), fn ($query, string $status) => $query->where('status', $status))
            ->when($request->string('search')->value(), fn ($query, string $search) => $query->where('title', 'like', "%{$search}%"))
            ->latest('updated_at')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Post $post): array => [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'status' => $post->status,
                'author' => $post->author?->name,
                'categories' => $post->categories->pluck('name')->all(),
                'published_at' => $post->published_at?->toIso8601String(),
                'updated_at' => $post->updated_at?->toIso8601String(),
            ]);

        return Inertia::render('Admin/Posts/Index', [
            'posts' => $posts,
            'filters' => [
                'status' => $request->string('status')->value(),
                'search' => $request->string('search')->value(),
            ],
            'can' => [
                'create' => $user->can('create', Post::class),
            ],
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', Post::class);

        return Inertia::render('Admin/Posts/Form', [
            'post' => null,
            'options' => $this->formOptions(),
        ]);
    }

    public function store(StorePostRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $post = new Post;
        $post->user_id = $request->user()->id;

        $this->persist($post, $data);

        if ($post->status === Post::STATUS_PUBLISHED) {
            ContentPublished::dispatch($post);
        }

        $this->activity->log('post.created', "Created post \"{$post->title}\"", $post);

        return redirect()
            ->route('admin.posts.index')
            ->with('success', 'Post created successfully.');
    }

    public function edit(Post $post): Response
    {
        Gate::authorize('edit', $post);

        $post->load(['categories:id', 'tags:id', 'seo']);

        return Inertia::render('Admin/Posts/Form', [
            'post' => [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'excerpt' => $post->excerpt,
                'content' => $post->content,
                'status' => $post->status,
                'is_featured' => $post->is_featured,
                'published_at' => $post->published_at?->toIso8601String(),
                'featured_media_id' => $post->featured_media_id,
                'category_ids' => $post->categories->pluck('id')->all(),
                'tag_ids' => $post->tags->pluck('id')->all(),
                'seo' => $post->seo === null ? null : [
                    'title' => $post->seo->title,
                    'description' => $post->seo->description,
                    'focus_keyword' => $post->seo->focus_keyword,
                    'canonical_url' => $post->seo->canonical_url,
                    'robots' => $post->seo->robots,
                    'og_title' => $post->seo->og_title,
                    'og_description' => $post->seo->og_description,
                    'og_image' => $post->seo->og_image,
                    'twitter_card' => $post->seo->twitter_card,
                ],
            ],
            'options' => $this->formOptions(),
        ]);
    }

    public function update(UpdatePostRequest $request, Post $post): RedirectResponse
    {
        $wasPublished = $post->status === Post::STATUS_PUBLISHED;

        $this->persist($post, $request->validated());

        if (! $wasPublished && $post->refresh()->status === Post::STATUS_PUBLISHED) {
            ContentPublished::dispatch($post);
        }

        $this->activity->log('post.updated', "Updated post \"{$post->title}\"", $post);

        return redirect()
            ->route('admin.posts.edit', $post)
            ->with('success', 'Post updated successfully.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        Gate::authorize('delete', $post);

        $title = $post->title;
        $post->delete();

        $this->activity->log('post.deleted', "Deleted post \"{$title}\"");

        return redirect()
            ->route('admin.posts.index')
            ->with('success', 'Post moved to trash.');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function persist(Post $post, array $data): void
    {
        $slugSource = $data['slug'] ?: $data['title'];

        $status = $this->posts->resolveStatus($data, request()->user());

        $this->posts->save(
            $post,
            [
                'title' => $data['title'],
                'slug' => $this->slugs->unique(Post::class, $slugSource, $post->id),
                'excerpt' => $data['excerpt'] ?? null,
                'content' => $data['content'] ?? null,
                'featured_media_id' => $data['featured_media_id'] ?? null,
                'is_featured' => $data['is_featured'] ?? false,
                'status' => $status['status'],
                'published_at' => $status['published_at'],
            ],
            $data['category_ids'] ?? [],
            $data['tag_ids'] ?? [],
        );

        if (isset($data['seo']) && is_array($data['seo'])) {
            $this->seo->save($post, $data['seo']);
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function formOptions(): array
    {
        return [
            'categories' => Category::ordered()->get(['id', 'name']),
            'tags' => Tag::orderBy('name')->get(['id', 'name']),
        ];
    }
}
