<?php

namespace App\Core\Support;

use App\Core\Container;
use App\Core\ServiceProvider;
use RuntimeException;

class ModuleManager
{
    protected array $providers = [];

    public function __construct(
        protected string $modulesPath,
        protected Container $container,
        protected HookManager $hooks,
        protected EventDispatcher $events
    ) {
    }

    public function registerModules(): void
    {
        if (!is_dir($this->modulesPath)) {
            return;
        }

        $moduleDirs = glob($this->modulesPath . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR) ?: [];
        sort($moduleDirs);

        foreach ($moduleDirs as $moduleDir) {
            $moduleName = basename($moduleDir);
            $moduleFile = $moduleDir . DIRECTORY_SEPARATOR . 'Module.php';

            if (!file_exists($moduleFile)) {
                continue;
            }

            require_once $moduleFile;
            $className = "Modules\\{$moduleName}\\Module";

            if (!class_exists($className)) {
                throw new RuntimeException("Module class {$className} not found in {$moduleFile}.");
            }

            if (!is_subclass_of($className, ServiceProvider::class)) {
                throw new RuntimeException("{$className} must extend " . ServiceProvider::class . '.');
            }

            /** @var ServiceProvider $provider */
            $provider = new $className($this->container, $this->hooks, $this->events);
            $provider->register();

            $this->providers[] = $provider;
        }
    }

    public function bootModules(): void
    {
        foreach ($this->providers as $provider) {
            $provider->boot();
        }
    }

    public function allProviders(): array
    {
        return $this->providers;
    }
}
