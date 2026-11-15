<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Theme;
use App\Services\Theme\ThemeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ThemeController extends Controller
{
    public function __construct(private readonly ThemeService $themes) {}

    public function index(): Response
    {
        Gate::authorize('themes.view');

        $activeSlug = $this->themes->activeSlug();

        $themes = $this->themes->sync()->map(fn (Theme $theme): array => [
            'slug' => $theme->slug,
            'name' => $theme->name,
            'version' => $theme->version,
            'author' => $theme->author,
            'description' => $theme->description,
            'is_active' => $theme->slug === $activeSlug,
            'has_stylesheet' => $this->themes->stylesheet($theme->slug) !== null,
            'templates' => $theme->metadata['templates'] ?? [],
            'has_draft' => $this->themes->hasDraft($theme->slug),
        ])->values();

        return Inertia::render('Admin/Themes/Index', [
            'themes' => $themes,
            'can' => [
                'manage' => request()->user()->hasPermission('themes.manage'),
                'customize' => request()->user()->hasPermission('appearance.customize'),
            ],
            'previewUrl' => url('/').'?theme-preview=1',
        ]);
    }

    public function activate(string $slug): RedirectResponse
    {
        Gate::authorize('themes.manage');

        if (! $this->themes->activate($slug)) {
            return back()->with('error', 'That theme could not be found.');
        }

        return back()->with('success', 'Theme activated.');
    }

    public function deactivate(string $slug): RedirectResponse
    {
        Gate::authorize('themes.manage');

        if (! $this->themes->deactivate($slug)) {
            return back()->with('error', 'Another theme is still active.');
        }

        return back()->with('success', 'Theme deactivated.');
    }
}
