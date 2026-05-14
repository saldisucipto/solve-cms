<?php

namespace App\Core;

use App\Core\Support\EventDispatcher;
use App\Core\Support\HookManager;
use App\Core\Support\ModuleManager;

class CmsKernel
{
    protected Container $container;
    protected HookManager $hooks;
    protected EventDispatcher $events;
    protected ModuleManager $modules;

    public function __construct(string $modulesPath)
    {
        $this->container = new Container();
        $this->hooks = new HookManager();
        $this->events = new EventDispatcher();
        $this->modules = new ModuleManager($modulesPath, $this->container, $this->hooks, $this->events);

        $this->container->instance(Container::class, $this->container);
        $this->container->instance(HookManager::class, $this->hooks);
        $this->container->instance(EventDispatcher::class, $this->events);
        $this->container->instance(ModuleManager::class, $this->modules);
    }

    public function boot(): void
    {
        $this->modules->registerModules();
        $this->modules->bootModules();
    }

    public function container(): Container
    {
        return $this->container;
    }

    public function hooks(): HookManager
    {
        return $this->hooks;
    }

    public function events(): EventDispatcher
    {
        return $this->events;
    }

    public function modules(): ModuleManager
    {
        return $this->modules;
    }
}
