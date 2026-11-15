<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSiteSettingsRequest;
use App\Http\Requests\Admin\UploadBrandingRequest;
use App\Services\ActivityLogger;
use App\Services\Media\MediaService;
use App\Services\SiteSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class SiteSettingsController extends Controller
{
    public function __construct(
        private readonly SiteSettings $settings,
        private readonly MediaService $media,
        private readonly ActivityLogger $activity,
    ) {}

    public function index(): Response
    {
        Gate::authorize('settings.manage');

        return Inertia::render('Admin/Settings/Index', [
            'settings' => [
                'site_name' => $this->settings->get('site_name', config('app.name')),
                'site_tagline' => $this->settings->get('site_tagline'),
                'site_description' => $this->settings->get('site_description'),
                'site_url' => $this->settings->get('site_url', config('app.url')),
                'posts_per_page' => $this->settings->int('posts_per_page', 10),
                'timezone' => $this->settings->get('timezone', config('app.timezone')),
                'logo_path' => $this->settings->get('logo_path'),
                'dashboard_logo_path' => $this->settings->get('dashboard_logo_path'),
                'favicon_path' => $this->settings->get('favicon_path'),
            ],
            'timezones' => \DateTimeZone::listIdentifiers(),
        ]);
    }

    public function update(UpdateSiteSettingsRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $this->settings->setMany([
            'site_name' => $data['site_name'],
            'site_tagline' => $data['site_tagline'] ?? null,
            'site_description' => $data['site_description'] ?? null,
            'site_url' => $data['site_url'],
            'posts_per_page' => (string) $data['posts_per_page'],
            'timezone' => $data['timezone'],
        ], 'general');

        $this->activity->log('settings.updated', 'Updated website settings');

        return back()->with('success', 'Website settings saved.');
    }

    public function uploadBranding(UploadBrandingRequest $request): RedirectResponse
    {
        Gate::authorize('settings.manage');

        $type = $request->string('type')->value();
        $key = $this->settingKey($type);

        $media = $this->media->store(
            $request->file('file'),
            $request->user()->id,
            ['title' => $this->label($type)],
        );

        $this->settings->setMany([$key => $media->url()], 'general');

        $this->activity->log(
            'settings.branding_uploaded',
            'Uploaded a new '.$this->label($type),
            $media,
        );

        return back()->with('success', $this->label($type).' updated.');
    }

    public function destroyBranding(Request $request): RedirectResponse
    {
        Gate::authorize('settings.manage');

        $type = $request->string('type')->value();

        abort_unless(array_key_exists($type, self::BRANDING), 404);

        $this->settings->setMany([$this->settingKey($type) => null], 'general');

        $this->activity->log('settings.branding_removed', 'Removed the '.$this->label($type));

        return back()->with('success', $this->label($type).' removed.');
    }

    /**
     * The dashboard and the public website each get their own logo.
     */
    private const BRANDING = [
        'logo' => ['key' => 'logo_path', 'label' => 'public site logo'],
        'dashboard_logo' => ['key' => 'dashboard_logo_path', 'label' => 'dashboard logo'],
        'favicon' => ['key' => 'favicon_path', 'label' => 'favicon'],
    ];

    private function settingKey(string $type): string
    {
        return self::BRANDING[$type]['key'];
    }

    private function label(string $type): string
    {
        return ucfirst(self::BRANDING[$type]['label']);
    }
}
