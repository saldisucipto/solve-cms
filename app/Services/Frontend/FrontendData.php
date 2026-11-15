<?php

namespace App\Services\Frontend;

use App\Services\Navigation\MenuService;
use App\Services\SiteSettings;
use App\Services\Theme\ThemeService;
use Illuminate\Support\Facades\Gate;

/**
 * Builds the site-level props every public page needs, so they can be produced
 * from controllers and from the exception handler alike.
 *
 * Appearance values come from the active theme (with its manifest defaults),
 * and can be previewed from the customizer using unpublished draft settings.
 */
class FrontendData
{
    public function __construct(
        private readonly SiteSettings $settings,
        private readonly MenuService $menus,
        private readonly ThemeService $themes,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function site(): array
    {
        $theme = $this->themes->active();
        $appearance = $this->appearance();

        return [
            'name' => $this->settings->get('site_name', config('app.name')),
            'tagline' => $this->settings->get('site_tagline'),
            'description' => $this->settings->get('site_description'),
            'logo' => $this->settings->get('logo_path'),
            'favicon' => $this->settings->get('favicon_path'),
            'colors' => [
                'primary' => $appearance['primary_color'] ?? '#2563eb',
                'secondary' => $appearance['secondary_color'] ?? '#0f172a',
                'background' => $appearance['background_color'] ?? '#ffffff',
                'text' => $appearance['text_color'] ?? '#0f172a',
                'link' => $appearance['link_color'] ?? '#2563eb',
            ],
            'typography' => [
                'font' => $appearance['font_family'] ?? 'Instrument Sans',
                'heading' => $appearance['heading_font'] ?? 'Instrument Sans',
                'body' => $appearance['body_font'] ?? 'Instrument Sans',
                'size' => $appearance['font_size_base'] ?? 16,
                'lineHeight' => $appearance['line_height'] ?? 1.7,
            ],
            'containerWidth' => $appearance['container_width'] ?? 1152,
            'sidebarEnabled' => $appearance['sidebar_enabled'] ?? true,
            'blogLayout' => $appearance['blog_layout'] ?? 'grid',
            'homepageLayout' => $appearance['homepage_layout'] ?? 'featured',
            'headerLayout' => $appearance['header_layout'] ?? 'classic',
            'footerLayout' => $appearance['footer_layout'] ?? 'simple',
            'postCard' => $appearance['post_card'] ?? 'image-top',
            'showFeaturedSection' => $appearance['show_featured_section'] ?? true,
            'showSearch' => $appearance['show_search'] ?? true,
            'theme' => [
                'slug' => $theme?->slug,
                'name' => $theme?->name,
                'preview' => $this->isPreview(),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function menus(): array
    {
        return [
            'primary' => $this->menus->byLocation('primary'),
            'footer' => $this->menus->byLocation('footer'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function shared(): array
    {
        $theme = $this->themes->active();

        return [
            'site' => $this->site(),
            'menus' => $this->menus(),
            'themeCss' => $theme === null ? null : $this->themes->stylesheet($theme->slug),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function appearance(): array
    {
        $theme = $this->themes->active();

        if ($theme === null) {
            return [];
        }

        $settings = $this->isPreview()
            ? $this->themes->draft($theme->slug)
            : $this->themes->settings($theme->slug);

        return $settings;
    }

    private function isPreview(): bool
    {
        return request()->boolean('theme-preview')
            && auth()->check()
            && Gate::allows('appearance.customize');
    }
}
