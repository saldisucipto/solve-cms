<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\Theme;
use App\Services\ActivityLogger;
use App\Services\SiteSettings;
use App\Services\Theme\ThemeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class CustomizerController extends Controller
{
    public function __construct(
        private readonly ThemeService $themes,
        private readonly SiteSettings $settings,
        private readonly ActivityLogger $activity,
    ) {}

    public function index(): Response
    {
        Gate::authorize('appearance.customize');

        $theme = $this->themes->active();

        abort_if($theme === null, 404, 'Activate a theme before customising it.');

        return Inertia::render('Admin/Customizer/Index', [
            'theme' => [
                'slug' => $theme->slug,
                'name' => $theme->name,
                'templates' => $theme->metadata['templates'] ?? [],
            ],
            'settings' => $this->themes->draft($theme->slug),
            'defaults' => $theme->defaults(),
            'hasDraft' => $this->themes->hasDraft($theme->slug),
            'general' => [
                'site_name' => $this->settings->get('site_name', config('app.name')),
                'site_tagline' => $this->settings->get('site_tagline'),
                'site_description' => $this->settings->get('site_description'),
                'logo_path' => $this->settings->get('logo_path'),
                'favicon_path' => $this->settings->get('favicon_path'),
            ],
            'images' => Media::query()
                ->where('mime_type', 'like', 'image/%')
                ->latest()
                ->limit(100)
                ->get(['id', 'title', 'path', 'alt_text'])
                ->map(fn (Media $media): array => [
                    'id' => $media->id,
                    'label' => $media->title ?? $media->original_name,
                    'url' => $media->url(),
                ]),
            'previewUrl' => url('/').'?theme-preview=1',
        ]);
    }

    public function saveDraft(Request $request): RedirectResponse
    {
        Gate::authorize('appearance.customize');

        $theme = $this->themes->active();

        abort_if($theme === null, 404);

        $data = $this->validatePayload($request);

        $this->themes->saveDraft($theme->slug, $data['appearance']);

        return back()->with('success', 'Draft saved. Preview it or publish when ready.');
    }

    public function publish(Request $request): RedirectResponse
    {
        Gate::authorize('appearance.customize');

        $theme = $this->themes->active();

        abort_if($theme === null, 404);

        $data = $this->validatePayload($request);

        $this->themes->saveDraft($theme->slug, $data['appearance']);
        $this->themes->publish($theme->slug);

        $this->activity->log('theme.published', "Published customisations for \"{$theme->name}\"");

        return back()->with('success', 'Customisations published to the live site.');
    }

    public function reset(): RedirectResponse
    {
        Gate::authorize('appearance.customize');

        $theme = $this->themes->active();

        abort_if($theme === null, 404);

        $this->themes->reset($theme->slug);

        $this->activity->log('theme.reset', "Reset \"{$theme->name}\" to its defaults");

        return back()->with('success', 'Theme reset to defaults.');
    }

    /**
     * @return array{appearance: array<string, mixed>}
     */
    private function validatePayload(Request $request): array
    {
        $data = $request->validate([
            'site_name' => ['required', 'string', 'max:255'],
            'site_tagline' => ['nullable', 'string', 'max:255'],
            'site_description' => ['nullable', 'string', 'max:500'],
            'logo_path' => ['nullable', 'string', 'max:2048'],
            'favicon_path' => ['nullable', 'string', 'max:2048'],

            'appearance' => ['required', 'array'],
            'appearance.primary_color' => ['required', 'string', 'max:20'],
            'appearance.secondary_color' => ['required', 'string', 'max:20'],
            'appearance.background_color' => ['required', 'string', 'max:20'],
            'appearance.text_color' => ['required', 'string', 'max:20'],
            'appearance.link_color' => ['required', 'string', 'max:20'],
            'appearance.font_family' => ['required', 'string', 'max:100'],
            'appearance.heading_font' => ['required', 'string', 'max:100'],
            'appearance.body_font' => ['required', 'string', 'max:100'],
            'appearance.font_size_base' => ['required', 'integer', 'min:12', 'max:24'],
            'appearance.line_height' => ['required', 'numeric', 'min:1', 'max:2.5'],
            'appearance.container_width' => ['required', 'integer', 'min:640', 'max:1920'],
            'appearance.header_layout' => ['required', 'string', 'max:50'],
            'appearance.footer_layout' => ['required', 'string', 'max:50'],
            'appearance.sidebar_enabled' => ['boolean'],
            'appearance.blog_layout' => ['required', 'string', 'max:50'],
            'appearance.homepage_layout' => ['required', 'string', 'max:50'],
            'appearance.post_card' => ['required', 'string', 'max:50'],
            'appearance.show_featured_section' => ['boolean'],
            'appearance.show_search' => ['boolean'],
        ]);

        // General settings are global; appearance belongs to the theme.
        $this->settings->setMany([
            'site_name' => $data['site_name'],
            'site_tagline' => $data['site_tagline'] ?? null,
            'site_description' => $data['site_description'] ?? null,
            'logo_path' => $data['logo_path'] ?? null,
            'favicon_path' => $data['favicon_path'] ?? null,
        ], 'general');

        return ['appearance' => $this->onlyAppearanceKeys($data['appearance'])];
    }

    /**
     * @param  array<string, mixed>  $appearance
     * @return array<string, mixed>
     */
    private function onlyAppearanceKeys(array $appearance): array
    {
        return array_intersect_key($appearance, array_flip(ThemeService::APPEARANCE_KEYS));
    }
}
