<?php

namespace App\Core;

use App\Core\Support\EventDispatcher;
use App\Core\Support\HookManager;

abstract class ServiceProvider
{
    public function __construct(
        protected Container $container,
        protected HookManager $hooks,
        protected EventDispatcher $events
    ) {
    }

    abstract public function register(): void;

    public function boot(): void
    {
    }
}
