<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Support\Collection;

class SiteSettings
{
    /**
     * @return Collection<string, string|null>
     */
    public function all(): Collection
    {
        return collect(SiteSetting::allCached());
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return SiteSetting::get($key, $default);
    }

    public function bool(string $key, bool $default = false): bool
    {
        $value = $this->get($key);

        return $value === null ? $default : filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    public function int(string $key, int $default = 0): int
    {
        $value = $this->get($key);

        return $value === null ? $default : (int) $value;
    }

    /**
     * @param  array<string, mixed>  $values
     */
    public function setMany(array $values, string $group = 'general'): void
    {
        foreach ($values as $key => $value) {
            SiteSetting::updateOrCreate(
                ['key' => $key],
                [
                    'group' => $group,
                    'value' => $value === null ? null : (string) $value,
                    'type' => is_bool($value) ? 'boolean' : (is_int($value) ? 'integer' : 'string'),
                ],
            );
        }
    }

    /**
     * The configured website URL used for canonical URLs and sitemaps.
     */
    public function siteUrl(): string
    {
        $url = $this->get('site_url');

        return rtrim(is_string($url) && $url !== '' ? $url : config('app.url'), '/');
    }
}
