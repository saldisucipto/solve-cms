<?php

namespace App\Services;

use App\Models\Settings;

class SettingService
{
    protected static array $settings = [];
    protected static bool $loaded = false;

    public static function load(): void
    {
        if (self::$loaded) {
            return;
        }

        // ambil semua data dari database
        $settings = new Settings();
        $data =  $settings->all();
        foreach ($data as $value) {
            // simpan ke array value ['key' => 'value']
            self::$settings[$value->key] = $value->value;
        }
        self::$loaded = true;
    }

    public static function get(string $key, $default = null)
    {
        self::load();
        return self::$settings[$key] ?? $default;
    }

    public static function getAllSettings(): array
    {
        self::load();
        if (empty(self::$settings)) {
            return [];
        }
        return self::$settings;
    }

    public static function set(string $key, array $value)
    {
        // Update DB 
        // Update cache array juga
    }
}
