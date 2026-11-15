<?php

namespace App\Services\Theme;

use App\Models\Theme;
use App\Models\ThemeSetting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ThemeService
{
    public const CACHE_ACTIVE = 'theme.active';

    /**
     * Appearance keys the customizer manages. Anything outside this list is
     * ignored when settings are saved.
     *
     * @var list<string>
     */
    public const APPEARANCE_KEYS = [
        'primary_color', 'secondary_color', 'background_color', 'text_color', 'link_color',
        'font_family', 'heading_font', 'body_font', 'font_size_base', 'line_height',
        'container_width', 'header_layout', 'footer_layout',
        'sidebar_enabled', 'blog_layout', 'homepage_layout', 'post_card',
        'show_featured_section', 'show_search',
    ];

    public function __construct(private readonly ThemeRegistry $registry) {}

    /**
     * Register every theme found on disk, then return them with activation state.
     *
     * @return Collection<int, Theme>
     */
    public function sync(): Collection
    {
        foreach ($this->registry->all() as $manifest) {
            Theme::updateOrCreate(
                ['slug' => $manifest['slug']],
                [
                    'name' => $manifest['name'],
                    'version' => $manifest['version'],
                    'author' => $manifest['author'],
                    'description' => $manifest['description'],
                    'metadata' => $manifest['metadata'],
                ],
            );
        }

        // Remove records for themes that no longer exist on disk (never the active one).
        $slugs = collect($this->registry->all())->pluck('slug')->all();
        Theme::query()->whereNotIn('slug', $slugs)->where('is_active', false)->delete();

        return Theme::query()->orderBy('name')->get();
    }

    public function active(): ?Theme
    {
        // Cache only the slug: serialising a model into the cache is fragile
        // across deployments and CLI contexts, a string is not.
        $slug = Cache::rememberForever(
            self::CACHE_ACTIVE,
            fn (): ?string => Theme::query()->where('is_active', true)->value('slug'),
        );

        if (! is_string($slug)) {
            return null;
        }

        return Theme::query()->where('slug', $slug)->first();
    }

    public function activeSlug(): ?string
    {
        return $this->active()?->slug;
    }

    public function activate(string $slug): bool
    {
        $this->registry->assertValidSlug($slug);

        if ($this->registry->find($slug) === null) {
            return false;
        }

        DB::transaction(function () use ($slug): void {
            Theme::query()->where('is_active', true)->update(['is_active' => false]);

            Theme::updateOrCreate(
                ['slug' => $slug],
                ['is_active' => true],
            );

            // Ensure the newly activated theme has a published settings row.
            $theme = Theme::query()->where('slug', $slug)->first();
            $published = ThemeSetting::query()
                ->where('theme_slug', $slug)
                ->where('scope', ThemeSetting::SCOPE_PUBLISHED)
                ->exists();

            if (! $published && $theme !== null) {
                ThemeSetting::create([
                    'theme_slug' => $slug,
                    'scope' => ThemeSetting::SCOPE_PUBLISHED,
                    'settings' => $theme->defaults(),
                ]);
            }
        });

        $this->flush();

        return true;
    }

    public function deactivate(string $slug): bool
    {
        $this->registry->assertValidSlug($slug);

        Theme::query()->where('slug', $slug)->update(['is_active' => false]);
        $this->flush();

        return $this->active() === null;
    }

    /**
     * Resolve appearance settings for a theme: saved values fall back to the
     * manifest defaults, and unknown keys are dropped.
     *
     * @return array<string, mixed>
     */
    public function settings(string $slug): array
    {
        $theme = Theme::query()->where('slug', $slug)->first();

        if ($theme === null) {
            return [];
        }

        $saved = ThemeSetting::query()
            ->where('theme_slug', $slug)
            ->where('scope', ThemeSetting::SCOPE_PUBLISHED)
            ->value('settings');

        return $this->normalise(array_merge($theme->defaults(), is_array($saved) ? $saved : []));
    }

    /**
     * Draft settings for the customizer preview.
     *
     * @return array<string, mixed>
     */
    public function draft(string $slug): array
    {
        $theme = Theme::query()->where('slug', $slug)->first();

        if ($theme === null) {
            return [];
        }

        $draft = ThemeSetting::query()
            ->where('theme_slug', $slug)
            ->where('scope', ThemeSetting::SCOPE_DRAFT)
            ->value('settings');

        if ($draft === null) {
            return $this->settings($slug);
        }

        $merged = array_merge($theme->defaults(), is_array($draft) ? $draft : []);

        return $this->normalise($merged);
    }

    /**
     * @param  array<string, mixed>  $values
     */
    public function saveDraft(string $slug, array $values): void
    {
        $this->store($slug, ThemeSetting::SCOPE_DRAFT, $values);
    }

    public function publish(string $slug): void
    {
        $draft = ThemeSetting::query()
            ->where('theme_slug', $slug)
            ->where('scope', ThemeSetting::SCOPE_DRAFT)
            ->first();

        if ($draft === null) {
            return;
        }

        ThemeSetting::updateOrCreate(
            ['theme_slug' => $slug, 'scope' => ThemeSetting::SCOPE_PUBLISHED],
            ['settings' => $draft->settings],
        );

        $draft->delete();
        $this->flush();
    }

    public function reset(string $slug): void
    {
        ThemeSetting::query()
            ->where('theme_slug', $slug)
            ->where('scope', ThemeSetting::SCOPE_DRAFT)
            ->delete();

        $theme = Theme::query()->where('slug', $slug)->first();

        ThemeSetting::updateOrCreate(
            ['theme_slug' => $slug, 'scope' => ThemeSetting::SCOPE_PUBLISHED],
            ['settings' => $theme?->defaults() ?? []],
        );

        $this->flush();
    }

    public function hasDraft(string $slug): bool
    {
        return ThemeSetting::query()
            ->where('theme_slug', $slug)
            ->where('scope', ThemeSetting::SCOPE_DRAFT)
            ->exists();
    }

    public function stylesheet(string $slug): ?string
    {
        return $this->registry->stylesheet($slug);
    }

    public function flush(): void
    {
        Cache::forget(self::CACHE_ACTIVE);
    }

    /**
     * @param  array<string, mixed>  $values
     */
    private function store(string $slug, string $scope, array $values): void
    {
        ThemeSetting::updateOrCreate(
            ['theme_slug' => $slug, 'scope' => $scope],
            ['settings' => $this->normalise($values)],
        );

        if ($scope === ThemeSetting::SCOPE_PUBLISHED) {
            $this->flush();
        }
    }

    /**
     * Keep only known appearance keys and coerce obvious types.
     *
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    private function normalise(array $values): array
    {
        $clean = [];

        foreach (self::APPEARANCE_KEYS as $key) {
            if (! array_key_exists($key, $values)) {
                continue;
            }

            $value = $values[$key];

            $clean[$key] = match ($key) {
                'container_width', 'font_size_base' => (int) $value,
                'line_height' => (float) $value,
                'sidebar_enabled', 'show_featured_section', 'show_search' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
                default => is_string($value) ? $value : (string) $value,
            };
        }

        return $clean;
    }
}
