<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\ActivityLogger;
use App\Services\Content\SlugService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function __construct(
        private readonly SlugService $slugs,
        private readonly ActivityLogger $activity,
    ) {}

    public function index(Request $request): Response
    {
        Gate::authorize('categories.manage');

        $categories = Category::query()
            ->with('parent:id,name')
            ->withCount('posts')
            ->ordered()
            ->get()
            ->map(fn (Category $category): array => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'order' => $category->order,
                'parent_id' => $category->parent_id,
                'parent' => $category->parent?->name,
                'posts_count' => $category->posts_count,
            ]);

        return Inertia::render('Admin/Categories/Index', [
            'categories' => $categories,
            'parents' => Category::ordered()->get(['id', 'name']),
        ]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $category = Category::create([
            'name' => $data['name'],
            'slug' => $this->slugs->unique(Category::class, $data['slug'] ?: $data['name']),
            'description' => $data['description'] ?? null,
            'parent_id' => $data['parent_id'] ?? null,
            'order' => $data['order'] ?? 0,
        ]);

        $this->activity->log('category.created', "Created category \"{$category->name}\"", $category);

        return back()->with('success', 'Category created.');
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $data = $request->validated();

        $category->update([
            'name' => $data['name'],
            'slug' => $this->slugs->unique(Category::class, $data['slug'] ?: $data['name'], $category->id),
            'description' => $data['description'] ?? null,
            'parent_id' => $data['parent_id'] ?? null,
            'order' => $data['order'] ?? 0,
        ]);

        $this->activity->log('category.updated', "Updated category \"{$category->name}\"", $category);

        return back()->with('success', 'Category updated.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        Gate::authorize('categories.manage');

        $name = $category->name;
        $category->delete();

        $this->activity->log('category.deleted', "Deleted category \"{$name}\"");

        return back()->with('success', 'Category deleted.');
    }
}
