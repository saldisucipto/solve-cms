<?php

namespace App\Core;

class Session
{
    static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    static function get($key)
    {
        return $_SESSION[$key] ?? null;
    }

    static function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    static function forget($key)
    {
        unset($_SESSION[$key]);
    }
}
