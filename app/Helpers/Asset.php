<?php

namespace App\Helpers;

class Asset
{
    static function url(string $path): string
    {
        return '/' . ltrim($path, '/');
    }
}
