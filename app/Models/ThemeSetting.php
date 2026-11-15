<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $theme_slug
 * @property string $scope
 * @property array<string, mixed> $settings
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['theme_slug', 'scope', 'settings'])]
class ThemeSetting extends Model
{
    public const SCOPE_DRAFT = 'draft';

    public const SCOPE_PUBLISHED = 'published';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'settings' => 'array',
        ];
    }
}
