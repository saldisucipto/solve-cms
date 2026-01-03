<?php

namespace App\Core;

use App\Core\Auth;

class Middleware
{
    static function auth(): void
    {
        if (!Auth::check()) {
            header('Location: /admin/login');
            exit;
        }
    }
}
