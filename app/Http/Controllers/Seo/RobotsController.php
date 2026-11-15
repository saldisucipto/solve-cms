<?php

namespace App\Http\Controllers\Seo;

use App\Http\Controllers\Controller;
use App\Services\SiteSettings;
use Illuminate\Http\Response;

class RobotsController extends Controller
{
    public function __construct(private readonly SiteSettings $settings) {}

    public function __invoke(): Response
    {
        $lines = [
            'User-agent: *',
            'Disallow: /admin',
            'Disallow: /logout',
            'Allow: /',
        ];

        if ($this->settings->bool('seo_sitemap_enabled', true)) {
            $lines[] = '';
            $lines[] = 'Sitemap: '.$this->settings->siteUrl().'/sitemap.xml';
        }

        return response(implode("\n", $lines)."\n", 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
