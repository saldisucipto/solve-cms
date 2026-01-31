<?php

namespace App\Helpers;

use Database\Cache\CacheInterface;
use Database\Cache\CacheManager;
use App\Core\Config;

class Cache
{
    protected static CacheInterface $driver;
    private static array $config;

    public function __construct()
    {
        self::$config = Config::get('cache');
    }

    protected static function driver(): CacheInterface
    {
        $config = new self();
        if (!isset(static::$driver)) {
            static::$driver = (new CacheManager([
                'driver' => self::$config['driver'],
                'path'   => self::$config['storage'],
            ]))->driver();
        }
        return static::$driver;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return static::driver()->get($key, $default);
    }

    public static function put(string $key, mixed $value, int $ttl = 60): bool
    {
        return static::driver()->put($key, $value, $ttl);
    }

    public static function has(string $key): bool
    {
        return static::driver()->has($key);
    }

    public static function forget(string $key): bool
    {
        return static::driver()->forget($key);
    }

    public static function flush(): bool
    {
        return static::driver()->flush();
    }

    /**
     * Cache::remember()
     */
    public static function remember(string $key, int $ttl, callable $callback): mixed
    {
        if (static::has($key)) {
            return static::get($key);
        }

        $value = $callback();
        static::put($key, $value, $ttl);

        return $value;
    }
}
