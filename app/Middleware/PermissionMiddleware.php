<?php

namespace App\Middleware;

use App\Core\Gate;
use App\Core\MiddlewareInterface;

class PermissionMiddleware implements MiddlewareInterface
{

    protected string $permission;

    public function __construct(string $permission)
    {
        $this->permission = $permission;
    }

    function handle(): void
    {
        Gate::authorize($this->permission);
    }
}
