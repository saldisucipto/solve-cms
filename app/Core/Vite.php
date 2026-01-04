<?php

namespace App\Core;

use App\Helpers\Path;

class Vite
{
    public static function asset(string $path): string
    {
        if (Config::get('app.env') === 'development') {
            return "http://localhost:5173/{$path}";
        }

        $manifestPath = Path::public('assets/manifest.json');

        if (!file_exists($manifestPath)) {
            return '';
        }

        $manifest = json_decode(file_get_contents($manifestPath), true);

        return '/assets/' . $manifest[$path]['file'];
    }
}
