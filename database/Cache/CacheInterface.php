<?php

namespace Database\Cache;

interface CacheInterface
{
    /**
     * Ini adalah sebuah interface wajib untuk menegelola cache
     */
    // Fungsi get untuk ambil data sesuai dengan keynya
    public function get(string $key, mixed $default = null): mixed;
    // Fungsi put unruk menetapkan value cache baru, akan bertahan selama 60 menit
    public function put(string $key, mixed $value, int $ttl = 60): bool;
    // Fungsi has ini akan mengembalikan value sesuai dengan pengecekan
    public function has(string $key): bool;
    // Fungsi forget untuk menghapus data dari cache sesuai dengan key
    public function forget(string $key): bool;
    // Fungsi flush untuk mengahpus semua data dari cache
    public function flush(): bool;
}
