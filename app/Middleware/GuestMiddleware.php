<?php

namespace App\Middleware;

use App\Core\Auth;
use App\Core\MiddlewareInterface;

class GuestMiddleware implements MiddlewareInterface
{
    public function handle(): void
    {
        if (Auth::check()) {
            header('Location: /admin');
            exit;
        }
    }
}
