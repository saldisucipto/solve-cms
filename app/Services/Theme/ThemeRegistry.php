<?php

namespace App\Services\Theme;

use Illuminate\Support\Facades\File;
use RuntimeException;

/**
 * Reads theme packages from disk. Only themes shipped inside the application's
 * theme directory are ever read, and no PHP from a theme is executed — a theme
 * is pure metadata plus optional CSS.
 */
class ThemeRegistry
{
    public function __construct(private readonly string $path) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        if (! File::isDirectory($this->path)) {
            return [];
        }

        $themes = [];

        foreach (File::directories($this->path) as $directory) {
            $manifest = $this->read($directory);

            if ($manifest !== null) {
                $themes[] = $manifest;
            }
        }

        usort($themes, fn (array $a, array $b): int => strcmp($a['name'], $b['name']));

        return $themes;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(string $slug): ?array
    {
        foreach ($this->all() as $theme) {
            if ($theme['slug'] === $slug) {
                return $theme;
            }
        }

        return null;
    }

    /**
     * The stylesheet contents declared by the theme, if any.
     */
    public function stylesheet(string $slug): ?string
    {
        $file = $this->path.'/'.$slug.'/styles/theme.css';

        if (! File::isFile($file)) {
            return null;
        }

        return File::get($file);
    }

    public function directory(string $slug): string
    {
        return $this->path.'/'.$slug;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function read(string $directory): ?array
    {
        $manifestPath = $directory.'/theme.json';

        if (! File::isFile($manifestPath)) {
            return null;
        }

        try {
            $data = json_decode(File::get($manifestPath), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return null;
        }

        if (! is_array($data)) {
            return null;
        }

        $slug = basename($directory);
        $name = $data['name'] ?? null;

        if (! is_string($name) || $name === '') {
            return null;
        }

        $templates = $data['templates'] ?? [];
        $defaults = $data['defaults'] ?? [];

        return [
            'slug' => $slug,
            'name' => $name,
            'version' => is_string($data['version'] ?? null) ? $data['version'] : '1.0.0',
            'author' => is_string($data['author'] ?? null) ? $data['author'] : null,
            'description' => is_string($data['description'] ?? null) ? $data['description'] : null,
            'metadata' => [
                'templates' => is_array($templates) ? $templates : [],
                'defaults' => is_array($defaults) ? $defaults : [],
            ],
            'has_stylesheet' => File::isFile($directory.'/styles/theme.css'),
        ];
    }

    /**
     * Guard against path traversal when a slug is used to build a path.
     */
    public function assertValidSlug(string $slug): void
    {
        if (preg_match('/^[a-z0-9][a-z0-9\-]*$/', $slug) !== 1) {
            throw new RuntimeException('Invalid theme slug.');
        }
    }
}
