<?php

namespace App\Models;

use App\Services\Navigation\MenuService;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;

/**
 * @property int $id
 * @property int $menu_id
 * @property int|null $parent_id
 * @property string $label
 * @property string|null $url
 * @property string $type
 * @property int|null $linkable_id
 * @property string|null $linkable_type
 * @property string $target
 * @property int $order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'menu_id', 'parent_id', 'label', 'url', 'type',
    'linkable_id', 'linkable_type', 'target', 'order',
])]
class MenuItem extends Model
{
    protected static function booted(): void
    {
        $invalidate = fn () => MenuService::flush();

        static::saved($invalidate);
        static::deleted($invalidate);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Menu, $this>
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    /**
     * @return BelongsTo<MenuItem, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * @return HasMany<MenuItem, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('order');
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function linkable(): MorphTo
    {
        return $this->morphTo();
    }

    public function resolvedUrl(): string
    {
        if ($this->type === 'custom' || $this->linkable === null) {
            return $this->url ?? '#';
        }

        $route = match (true) {
            $this->linkable instanceof Post => 'frontend.posts.show',
            $this->linkable instanceof Page => 'frontend.pages.show',
            $this->linkable instanceof Category => 'frontend.categories.show',
            $this->linkable instanceof Tag => 'frontend.tags.show',
            default => null,
        };

        if ($route === null || ! Route::has($route)) {
            return $this->url ?? '#';
        }

        return route($route, $this->linkable->slug, absolute: false);
    }
}
