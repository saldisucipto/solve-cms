<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

/**
 * Short-lived caches for the public listing pages. Every key is dropped the
 * moment posts, categories or tags change, so the site is never stale for long.
 */
class ContentCache
{
    public const TTL = 300;

    public const HOME_FEATURED = 'front.home.featured';

    public const HOME_LATEST = 'front.home.latest';

    public const HOME_CATEGORIES = 'front.home.categories';

    public const BLOG_SIDEBAR = 'front.blog.sidebar';

    /**
     * @return array<int, string>
     */
    public static function keys(): array
    {
        return [
            self::HOME_FEATURED,
            self::HOME_LATEST,
            self::HOME_CATEGORIES,
            self::BLOG_SIDEBAR,
        ];
    }

    public static function flush(): void
    {
        foreach (self::keys() as $key) {
            Cache::forget($key);
        }
    }
}
