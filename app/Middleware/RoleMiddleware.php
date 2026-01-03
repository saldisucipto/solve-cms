<?php

namespace App\Middleware;

use App\Core\Auth;
use App\Core\MiddlewareInterface;

class RoleMiddleware extends MiddlewareInterface
{
    protected string $role;

    public function __construct(string $role)
    {
        $this->role = $role;
    }

    public function handle()
    {
        if (Auth::role() !== $this->role) {
            http_response_code(403);
            exit('Forbidden');
        }
    }
}
