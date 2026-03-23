<?php

namespace App\Core;

class Config
{
    public static function get(string $key, $default = null)
    {
        $segments = explode('.', $key);
        $file = array_shift($segments);

        $path = self::base_path("config/{$file}.php");

        if (!file_exists($path)) {
            return $default;
        }

        $config = require $path;

        foreach ($segments as $segment) {
            if (!isset($config[$segment])) {
                return $default;
            }
            $config = $config[$segment];
        }

        return $config;
    }


    protected static function base_path($path = '')
    {
        return dirname(__DIR__, 2) . ($path ? '/' . $path : '');
    }
}
