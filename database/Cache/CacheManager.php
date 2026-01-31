<?php

namespace Database\Cache;

use Database\Cache\Driver\FileCache;

class CacheManager
{
    protected CacheInterface $driver;

    public function __construct(array $config)
    {
        $driver = $config['driver'] ?? 'file';
        switch ($driver) {
            case 'file':
                $this->driver = new FileCache(
                    $config['path'] ?? BASE_PATH . '/storage/cache'
                );
            default:
                $this->driver = new FileCache(
                    $config['path'] ?? BASE_PATH . '/storage/cache'
                );
        }
    }

    public function driver(): CacheInterface
    {
        return $this->driver;
    }
}
