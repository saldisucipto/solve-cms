<?php

namespace App\Core\Support;

class EventDispatcher
{
    protected array $listeners = [];

    public function listen(string $eventName, callable $listener, int $priority = 10): void
    {
        $this->listeners[$eventName][$priority][] = $listener;
    }

    public function dispatch(string $eventName, mixed $payload = null): array
    {
        $responses = [];

        if (!isset($this->listeners[$eventName])) {
            return $responses;
        }

        ksort($this->listeners[$eventName]);
        foreach ($this->listeners[$eventName] as $listenersByPriority) {
            foreach ($listenersByPriority as $listener) {
                $responses[] = $listener($payload, $eventName);
            }
        }

        return $responses;
    }
}
