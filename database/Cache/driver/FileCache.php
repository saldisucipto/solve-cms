<?php

namespace Database\Cache\Driver;

use Database\Cache\CacheInterface;

class FileCache implements CacheInterface
{

    protected string $path;

    public function __construct(string $path)
    {
        $this->path = rtrim($path, '/') . '/';

        if (!is_dir($this->path)) {
            if (!mkdir($this->path, 0777, true) && !is_dir($this->path)) {
                throw new \RuntimeException('Cache directory not writable: ' . $this->path);
            }
        }
    }

    protected function fileName(string $key)
    {
        return $this->path . md5($key) . '.cache';
    }

    public function put(string $key, mixed $value, int $ttl = 60): bool
    {
        $payload = [
            'expires_at' => time() + $ttl,
            'value' => $value,
        ];
        return file_put_contents($this->fileName($key), serialize($payload)) !== false;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $file = $this->fileName($key);

        if (!file_exists($file)) {
            return $default;
        }

        $payload = unserialize(file_get_contents($file));

        if ($payload['expires_at'] < time()) {
            unlink($file);
            return $default;
        }

        return $payload['value'];
    }

    public function has(string $key): bool
    {
        $file = $this->fileName($key);

        if (!file_exists($file)) {
            return false;
        }

        $payload = unserialize(file_get_contents($file));

        if ($payload['expires_at'] < time()) {
            unlink($file);
            return false;
        }

        return true;
    }

    public function forget(string $key): bool
    {
        $file = $this->fileName($key);
        return file_exists($file) ? unlink($file) : false;
    }

    public function flush(): bool
    {
        foreach (glob($this->path . '*cache') as $file) {
            unlink($file);
        }
        return true;
    }
}
