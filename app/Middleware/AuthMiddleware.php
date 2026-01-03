<?php

namespace App\Middleware;

use App\Core\Auth;
use App\Core\MiddlewareInterface;

class AuthMiddleware extends MiddlewareInterface
{

    public function handle()
    {
        if (!Auth::check()) {
            header('Location: /login');
            exit;
        }
    }
}
