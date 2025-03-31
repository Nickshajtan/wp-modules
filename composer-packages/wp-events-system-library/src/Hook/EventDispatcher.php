<?php

namespace HCC\Events\Hook;

use HCC\Events\Hook\Interfaces\EventAdapterInterface;

class EventDispatcher
{
    private array $adapters = [];
    private array $observers = [];

    public function attach(string $event, callable $observer): void
    {
        $this->observers[$event][] = $observer;
    }

    public function detach(string $event, callable $observer): void
    {
        if (!empty($this->observers[$event])) {
            $this->observers[$event] = array_filter($this->observers[$event], fn($obs) => $obs !== $observer);
        }
    }

    public function notify(string $event, array $args = []): void {
        foreach ($this->observers[$event] ?? [] as $observer) {
            $observer(...$args);
        }
    }



    public function addListener(string $event, callable $callback, int $priority = 10, int $acceptedArgs = 1): void {
        foreach ($this->adapters as $adapter) {
            $adapter->addListener($event, $callback, $priority, $acceptedArgs);
        }
    }

    public function dispatch(string $event, array $args = []): array
    {
        $results = [];
        $this->notify($event, $args);

        foreach ($this->adapters as $adapter) {
            $results[] = $adapter->dispatch($event, $args);
        }
        return $results;
    }
}