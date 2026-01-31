<?php

namespace App\Core;

use App\Helpers\Path;

class Vite
{
    public static function asset(string $path)
    {
        $manifestPath = Path::public('assets/.vite/manifest.json');

        if (!file_exists($manifestPath)) {
            return [];
        }

        // Merubah json ke array asosiativ melalu file manifest
        $manifest = json_decode(file_get_contents($manifestPath), true);

        if (!isset($manifest)) {
            return [];
        }

        foreach ($manifest as $entry) {
            if (($entry['src'] ?? null) === str_replace('resources/', '', $path)) {
                return [
                    'js'  => '/assets/' . $entry['file'],
                    'css' => array_map(
                        fn($css) => '/assets/' . $css,
                        $entry['css'] ?? []
                    ),
                ];
            }
        }
    }
}
