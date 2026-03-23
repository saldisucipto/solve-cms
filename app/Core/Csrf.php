<?php

namespace App\Core;

class Csrf
{
    static function token(): string
    {
        if (!Session::get('_csrf')) {
            Session::set('_csrf', bin2hex(random_bytes(32)));
        }
        return Session::get('_csrf');
    }

    static function validate(string $token): bool
    {
        $storedToken = trim(Session::get('_csrf'));
        if (!$token || !$storedToken) {
            return false;
        }
        return hash_equals($storedToken, $token);
    }
}
