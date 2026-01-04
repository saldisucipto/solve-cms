<?php

namespace App\Helpers;

use App\Core\Session;

class FlashSession
{
    static function set(string $key, string $value): void
    {
        Session::set('flash_' . $key, $value);
    }

    static function get(string $key): ?string
    {
        $value = Session::get("flash_{$key}");
        Session::forget("flash_{$key}");
        return $value;
    }

    static function has(string $key): bool
    {
        return Session::get("flash_{$key}") !== null;
    }
}
