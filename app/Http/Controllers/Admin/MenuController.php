<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreMenuItemRequest;
use App\Http\Requests\Admin\StoreMenuRequest;
use App\Models\Category;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\Post;
use App\Models\Tag;
use App\Services\ActivityLogger;
use App\Services\Content\SlugService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class MenuController extends Controller
{
    public function __construct(
        private readonly SlugService $slugs,
        private readonly ActivityLogger $activity,
    ) {}

    public function index(): Response
    {
        Gate::authorize('menus.manage');

        $menus = Menu::query()
            ->with(['items' => fn ($query) => $query->orderBy('order')])
            ->orderBy('name')
            ->get()
            ->map(fn (Menu $menu): array => [
                'id' => $menu->id,
                'name' => $menu->name,
                'slug' => $menu->slug,
                'location' => $menu->location,
                'items' => $this->tree($menu->items),
            ]);

        return Inertia::render('Admin/Menus/Index', [
            'menus' => $menus,
            'linkables' => [
                'pages' => Page::published()->orderBy('title')->get(['id', 'title']),
                'posts' => Post::published()->latest('published_at')->limit(50)->get(['id', 'title']),
                'categories' => Category::ordered()->get(['id', 'name']),
                'tags' => Tag::orderBy('name')->get(['id', 'name']),
            ],
        ]);
    }

    public function store(StoreMenuRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $menu = Menu::create([
            'name' => $data['name'],
            'slug' => $this->slugs->unique(Menu::class, $data['slug'] ?: $data['name']),
            'location' => $data['location'] ?? null,
        ]);

        $this->activity->log('menu.created', "Created menu \"{$menu->name}\"", $menu);

        return back()->with('success', 'Menu created.');
    }

    public function update(StoreMenuRequest $request, Menu $menu): RedirectResponse
    {
        $data = $request->validated();

        $menu->update([
            'name' => $data['name'],
            'slug' => $this->slugs->unique(Menu::class, $data['slug'] ?: $data['name'], $menu->id),
            'location' => $data['location'] ?? null,
        ]);

        $this->activity->log('menu.updated', "Updated menu \"{$menu->name}\"", $menu);

        return back()->with('success', 'Menu updated.');
    }

    public function destroy(Menu $menu): RedirectResponse
    {
        Gate::authorize('menus.manage');

        $name = $menu->name;
        $menu->delete();

        $this->activity->log('menu.deleted', "Deleted menu \"{$name}\"");

        return back()->with('success', 'Menu deleted.');
    }

    public function storeItem(StoreMenuItemRequest $request, Menu $menu): RedirectResponse
    {
        $data = $request->validated();
        $class = $request->linkableClass();

        $item = $menu->items()->create([
            'parent_id' => $data['parent_id'] ?? null,
            'label' => $data['label'],
            'type' => $data['type'],
            'url' => $class === null ? ($data['url'] ?? null) : null,
            'linkable_id' => $class === null ? null : $data['linkable_id'],
            'linkable_type' => $class,
            'target' => $data['target'],
            'order' => $data['order'] ?? 0,
        ]);

        $this->activity->log('menu.item_created', "Added \"{$item->label}\" to menu \"{$menu->name}\"", $item);

        return back()->with('success', 'Menu item added.');
    }

    public function updateItem(StoreMenuItemRequest $request, Menu $menu, MenuItem $item): RedirectResponse
    {
        $this->ensureBelongsToMenu($menu, $item);

        $data = $request->validated();
        $class = $request->linkableClass();

        $item->update([
            'parent_id' => $data['parent_id'] ?? null,
            'label' => $data['label'],
            'type' => $data['type'],
            'url' => $class === null ? ($data['url'] ?? null) : null,
            'linkable_id' => $class === null ? null : $data['linkable_id'],
            'linkable_type' => $class,
            'target' => $data['target'],
            'order' => $data['order'] ?? 0,
        ]);

        $this->activity->log('menu.item_updated', "Updated \"{$item->label}\"", $item);

        return back()->with('success', 'Menu item updated.');
    }

    public function destroyItem(Menu $menu, MenuItem $item): RedirectResponse
    {
        Gate::authorize('menus.manage');
        $this->ensureBelongsToMenu($menu, $item);

        $label = $item->label;
        $item->delete();

        $this->activity->log('menu.item_deleted', "Removed \"{$label}\" from menu \"{$menu->name}\"");

        return back()->with('success', 'Menu item removed.');
    }

    private function ensureBelongsToMenu(Menu $menu, MenuItem $item): void
    {
        abort_unless($item->menu_id === $menu->id, 404);
    }

    /**
     * @param  Collection<int, MenuItem>  $items
     * @return array<int, array<string, mixed>>
     */
    private function tree(Collection $items, ?int $parentId = null): array
    {
        return $items
            ->where('parent_id', $parentId)
            ->map(fn (MenuItem $item): array => [
                'id' => $item->id,
                'label' => $item->label,
                'type' => $item->type,
                'url' => $item->url,
                'linkable_id' => $item->linkable_id,
                'target' => $item->target,
                'order' => $item->order,
                'resolved_url' => $item->type === 'custom' ? ($item->url ?? '#') : '#',
                'children' => $this->tree($items, $item->id),
            ])
            ->values()
            ->all();
    }
}
