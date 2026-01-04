<?php

namespace App\Middleware;

use App\Core\Auth;
use App\Core\MiddlewareInterface;

class AuthMiddleware implements MiddlewareInterface
{

    public function handle(): void
    {
        if (!Auth::check()) {
            header('Location: /login');
            exit;
        }
    }
}
