<?php

namespace App\Services\Navigation;

use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class MenuService
{
    private const CACHE_TTL = 300;

    public static function cacheKey(string $location): string
    {
        return "menu.{$location}";
    }

    public static function flush(): void
    {
        foreach (['primary', 'footer'] as $location) {
            Cache::forget(self::cacheKey($location));
        }
    }

    /**
     * Resolve a menu by its location into a nested array for the frontend.
     *
     * @return array<int, array<string, mixed>>
     */
    public function byLocation(string $location): array
    {
        /** @var array<int, array<string, mixed>> $tree */
        $tree = Cache::remember(
            self::cacheKey($location),
            self::CACHE_TTL,
            fn (): array => $this->resolve($location),
        );

        return $tree;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function resolve(string $location): array
    {
        $menu = Menu::query()
            ->where('location', $location)
            ->with('items')
            ->first();

        if ($menu === null) {
            return [];
        }

        return $this->tree($menu->items);
    }

    /**
     * @param  Collection<int, MenuItem>  $items
     * @return array<int, array<string, mixed>>
     */
    private function tree(Collection $items, ?int $parentId = null): array
    {
        return $items
            ->where('parent_id', $parentId)
            ->map(fn (MenuItem $item): array => [
                'label' => $item->label,
                'url' => $item->resolvedUrl(),
                'target' => $item->target,
                'children' => $this->tree($items, $item->id),
            ])
            ->values()
            ->all();
    }
}
