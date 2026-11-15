<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * @property int $id
 * @property string $group
 * @property string $key
 * @property string|null $value
 * @property string $type
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['group', 'key', 'value', 'type'])]
class SiteSetting extends Model
{
    public const CACHE_KEY = 'solve.site-settings';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'value' => 'string',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    /**
     * @return array<string, string|null>
     */
    public static function allCached(): array
    {
        /** @var array<string, string|null> $settings */
        $settings = Cache::rememberForever(
            self::CACHE_KEY,
            fn (): array => self::query()->pluck('value', 'key')->all(),
        );

        return $settings;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return self::allCached()[$key] ?? $default;
    }
}
