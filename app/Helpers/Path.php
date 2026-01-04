<?php

namespace App\Helpers;

class Path
{
    public static function base(string $path = ''): string
    {
        return dirname(__DIR__, 2) . ($path ? DIRECTORY_SEPARATOR . $path : '');
    }

    public static function public(string $path = ''): string
    {
        return self::base('public' . ($path ? DIRECTORY_SEPARATOR . $path : ''));
    }

    public static function storage(string $path = ''): string
    {
        return self::base('storage' . ($path ? DIRECTORY_SEPARATOR . $path : ''));
    }

    public static function resource(string $path = ''): string
    {
        return self::base('resources' . ($path ? DIRECTORY_SEPARATOR . $path : ''));
    }
}
