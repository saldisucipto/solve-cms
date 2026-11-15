<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * @property int $id
 * @property string $source
 * @property string $destination
 * @property int $status_code
 * @property int $hits
 * @property bool $is_active
 * @property Carbon|null $last_hit_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['source', 'destination', 'status_code', 'hits', 'is_active', 'last_hit_at'])]
class Redirect extends Model
{
    public const CACHE_KEY = 'seo.redirects';

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status_code' => 'integer',
            'hits' => 'integer',
            'is_active' => 'boolean',
            'last_hit_at' => 'datetime',
        ];
    }

    /**
     * @param  Builder<Redirect>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function registerHit(): void
    {
        $this->increment('hits', 1, ['last_hit_at' => now()]);
    }
}
