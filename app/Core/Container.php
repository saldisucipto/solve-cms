<?php

namespace App\Core;

use Closure;
use ReflectionClass;
use RuntimeException;

class Container
{
    protected array $bindings = [];
    protected array $instances = [];

    public function bind(string $abstract, Closure|string|null $concrete = null, bool $shared = false): void
    {
        $this->bindings[$abstract] = [
            'concrete' => $concrete ?? $abstract,
            'shared' => $shared,
        ];
    }

    public function singleton(string $abstract, Closure|string|null $concrete = null): void
    {
        $this->bind($abstract, $concrete, true);
    }

    public function instance(string $abstract, object $instance): void
    {
        $this->instances[$abstract] = $instance;
    }

    public function has(string $abstract): bool
    {
        return isset($this->instances[$abstract]) || isset($this->bindings[$abstract]);
    }

    public function make(string $abstract): mixed
    {
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        $binding = $this->bindings[$abstract] ?? null;
        $concrete = $binding['concrete'] ?? $abstract;
        $shared = $binding['shared'] ?? false;

        if ($concrete instanceof Closure) {
            $object = $concrete($this);
        } else {
            $object = $this->build($concrete);
        }

        if ($shared) {
            $this->instances[$abstract] = $object;
        }

        return $object;
    }

    protected function build(string $concrete): object
    {
        if (!class_exists($concrete)) {
            throw new RuntimeException("Class {$concrete} not found.");
        }

        $reflector = new ReflectionClass($concrete);

        if (!$reflector->isInstantiable()) {
            throw new RuntimeException("Class {$concrete} is not instantiable.");
        }

        $constructor = $reflector->getConstructor();
        if ($constructor === null) {
            return $reflector->newInstance();
        }

        $dependencies = [];
        foreach ($constructor->getParameters() as $parameter) {
            $type = $parameter->getType();

            if ($type !== null && !$type->isBuiltin()) {
                $dependencies[] = $this->make($type->getName());
                continue;
            }

            if ($parameter->isDefaultValueAvailable()) {
                $dependencies[] = $parameter->getDefaultValue();
                continue;
            }

            throw new RuntimeException(
                "Unable to resolve dependency [{$parameter->getName()}] in class {$concrete}."
            );
        }

        return $reflector->newInstanceArgs($dependencies);
    }
}