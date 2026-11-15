<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $slug
 * @property string $name
 * @property string $version
 * @property string|null $author
 * @property string|null $description
 * @property array<string, mixed>|null $metadata
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['slug', 'name', 'version', 'author', 'description', 'metadata', 'is_active'])]
class Theme extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<ThemeSetting, $this>
     */
    public function settings(): HasMany
    {
        return $this->hasMany(ThemeSetting::class, 'theme_slug', 'slug');
    }

    /**
     * Available template choices declared by the theme, e.g. ["grid", "list"].
     *
     * @return array<int, string>
     */
    public function templatesFor(string $key): array
    {
        $templates = $this->metadata['templates'] ?? [];

        $values = is_array($templates) ? ($templates[$key] ?? []) : [];

        return array_values(array_filter((array) $values, 'is_string'));
    }

    /**
     * @return array<string, mixed>
     */
    public function defaults(): array
    {
        $defaults = $this->metadata['defaults'] ?? [];

        return is_array($defaults) ? $defaults : [];
    }
}
