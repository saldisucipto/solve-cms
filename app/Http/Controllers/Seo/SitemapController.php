<?php

namespace App\Http\Controllers\Seo;

use App\Http\Controllers\Controller;
use App\Services\Seo\SitemapBuilder;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __construct(private readonly SitemapBuilder $sitemap) {}

    public function __invoke(): Response
    {
        abort_unless($this->sitemap->enabled(), 404);

        return response(
            $this->sitemap->render($this->sitemap->entries()),
            200,
            ['Content-Type' => 'application/xml; charset=UTF-8'],
        );
    }
}
