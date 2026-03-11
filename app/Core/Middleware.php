<?php

namespace App\Core;

use Exception;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Middleware\GuestMiddleware;

class Middleware
{

    protected static array $map = [
        'auth' => AuthMiddleware::class,
        'permission' => PermissionMiddleware::class,
        'csrf' => CsrfMiddleware::class,
        'guest' => GuestMiddleware::class,
    ];

    public static function resolve(string $middleware)
    {
        // role:admin
        if (str_contains($middleware, ':')) {
            [$name, $param] = explode(':', $middleware, 2);
        } else {
            $name = $middleware;
            $param = null;
        }

        if (!isset(self::$map[$name])) {
            throw new Exception("Middleware [$name] not registered");
        }

        return [$name, self::$map[$name], $param];
    }
}
