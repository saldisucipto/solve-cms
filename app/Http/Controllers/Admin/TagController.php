<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTagRequest;
use App\Http\Requests\Admin\UpdateTagRequest;
use App\Models\Tag;
use App\Services\ActivityLogger;
use App\Services\Content\SlugService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class TagController extends Controller
{
    public function __construct(
        private readonly SlugService $slugs,
        private readonly ActivityLogger $activity,
    ) {}

    public function index(Request $request): Response
    {
        Gate::authorize('tags.manage');

        $tags = Tag::query()
            ->withCount('posts')
            ->orderBy('name')
            ->paginate(30)
            ->withQueryString()
            ->through(fn (Tag $tag): array => [
                'id' => $tag->id,
                'name' => $tag->name,
                'slug' => $tag->slug,
                'description' => $tag->description,
                'posts_count' => $tag->posts_count,
            ]);

        return Inertia::render('Admin/Tags/Index', [
            'tags' => $tags,
        ]);
    }

    public function store(StoreTagRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $tag = Tag::create([
            'name' => $data['name'],
            'slug' => $this->slugs->unique(Tag::class, $data['slug'] ?: $data['name']),
            'description' => $data['description'] ?? null,
        ]);

        $this->activity->log('tag.created', "Created tag \"{$tag->name}\"", $tag);

        return back()->with('success', 'Tag created.');
    }

    public function update(UpdateTagRequest $request, Tag $tag): RedirectResponse
    {
        $data = $request->validated();

        $tag->update([
            'name' => $data['name'],
            'slug' => $this->slugs->unique(Tag::class, $data['slug'] ?: $data['name'], $tag->id),
            'description' => $data['description'] ?? null,
        ]);

        $this->activity->log('tag.updated', "Updated tag \"{$tag->name}\"", $tag);

        return back()->with('success', 'Tag updated.');
    }

    public function destroy(Tag $tag): RedirectResponse
    {
        Gate::authorize('tags.manage');

        $name = $tag->name;
        $tag->delete();

        $this->activity->log('tag.deleted', "Deleted tag \"{$name}\"");

        return back()->with('success', 'Tag deleted.');
    }
}
