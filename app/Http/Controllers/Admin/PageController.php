<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePageRequest;
use App\Http\Requests\Admin\UpdatePageRequest;
use App\Models\Page;
use App\Services\ActivityLogger;
use App\Services\Content\HtmlSanitizer;
use App\Services\Content\SlugService;
use App\Services\Seo\SeoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    public function __construct(
        private readonly SlugService $slugs,
        private readonly SeoService $seo,
        private readonly ActivityLogger $activity,
        private readonly HtmlSanitizer $sanitizer,
    ) {}

    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Page::class);

        $pages = Page::query()
            ->with(['author:id,name', 'parent:id,title'])
            ->when($request->string('status')->value(), fn ($query, string $status) => $query->where('status', $status))
            ->when($request->string('search')->value(), fn ($query, string $search) => $query->where('title', 'like', "%{$search}%"))
            ->orderBy('order')
            ->latest('updated_at')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Page $page): array => [
                'id' => $page->id,
                'title' => $page->title,
                'slug' => $page->slug,
                'status' => $page->status,
                'template' => $page->template,
                'parent' => $page->parent?->title,
                'author' => $page->author?->name,
                'updated_at' => $page->updated_at?->toIso8601String(),
            ]);

        return Inertia::render('Admin/Pages/Index', [
            'pages' => $pages,
            'filters' => [
                'status' => $request->string('status')->value(),
                'search' => $request->string('search')->value(),
            ],
            'can' => [
                'create' => $request->user()->can('create', Page::class),
            ],
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', Page::class);

        return Inertia::render('Admin/Pages/Form', [
            'page' => null,
            'options' => $this->formOptions(),
        ]);
    }

    public function store(StorePageRequest $request): RedirectResponse
    {
        $page = new Page;
        $page->user_id = $request->user()->id;

        $this->persist($page, $request->validated());

        $this->activity->log('page.created', "Created page \"{$page->title}\"", $page);

        return redirect()
            ->route('admin.pages.index')
            ->with('success', 'Page created successfully.');
    }

    public function edit(Page $page): Response
    {
        Gate::authorize('update', $page);

        $page->load('seo');

        return Inertia::render('Admin/Pages/Form', [
            'page' => [
                'id' => $page->id,
                'title' => $page->title,
                'slug' => $page->slug,
                'excerpt' => $page->excerpt,
                'content' => $page->content,
                'template' => $page->template,
                'status' => $page->status,
                'parent_id' => $page->parent_id,
                'order' => $page->order,
                'featured_media_id' => $page->featured_media_id,
                'seo' => $page->seo === null ? null : [
                    'title' => $page->seo->title,
                    'description' => $page->seo->description,
                    'focus_keyword' => $page->seo->focus_keyword,
                    'canonical_url' => $page->seo->canonical_url,
                    'robots' => $page->seo->robots,
                ],
            ],
            'options' => $this->formOptions(),
        ]);
    }

    public function update(UpdatePageRequest $request, Page $page): RedirectResponse
    {
        $this->persist($page, $request->validated());

        $this->activity->log('page.updated', "Updated page \"{$page->title}\"", $page);

        return redirect()
            ->route('admin.pages.edit', $page)
            ->with('success', 'Page updated successfully.');
    }

    public function destroy(Page $page): RedirectResponse
    {
        Gate::authorize('delete', $page);

        $title = $page->title;
        $page->delete();

        $this->activity->log('page.deleted', "Deleted page \"{$title}\"");

        return redirect()
            ->route('admin.pages.index')
            ->with('success', 'Page moved to trash.');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function persist(Page $page, array $data): void
    {
        $slugSource = $data['slug'] ?: $data['title'];
        $status = $data['status'];

        $page->fill([
            'title' => $data['title'],
            'slug' => $this->slugs->unique(Page::class, $slugSource, $page->id),
            'excerpt' => $data['excerpt'] ?? null,
            'content' => $this->sanitizer->clean($data['content'] ?? null),
            'template' => $data['template'],
            'status' => $status,
            'parent_id' => $data['parent_id'] ?? null,
            'order' => $data['order'] ?? 0,
            'featured_media_id' => $data['featured_media_id'] ?? null,
            'published_at' => $status === Page::STATUS_PUBLISHED
                ? ($page->published_at ?? now())
                : null,
        ]);

        $page->save();

        if (isset($data['seo']) && is_array($data['seo'])) {
            $this->seo->save($page, $data['seo']);
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function formOptions(): array
    {
        $current = request()->route('page');
        $currentId = $current instanceof Page ? $current->id : null;

        $parents = Page::query()->orderBy('title');

        if ($currentId !== null) {
            $parents->whereKeyNot($currentId);
        }

        return [
            'parents' => $parents->get(['id', 'title'])->values(),
            'templates' => [
                ['value' => 'default', 'label' => 'Default'],
                ['value' => 'full-width', 'label' => 'Full Width'],
                ['value' => 'sidebar', 'label' => 'With Sidebar'],
                ['value' => 'landing', 'label' => 'Landing Page'],
            ],
        ];
    }
}
