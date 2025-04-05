<?php

namespace HCC\Events\Observer;

use HCC\Events\Dispatcher\Interfaces\EventDispatcherInterface;
use HCC\Events\Interfaces\CollectionInterface;

readonly class SimpleObserver
{
    public function __construct(private EventDispatcherInterface $dispatcher) {}

    public function subscribe(string $eventName, callable $callback, int $priority = CollectionInterface::DEFAULT_PRIORITY, ...$args): string
    {
        return $this->dispatcher->addListener($eventName, $callback, $priority, ...$args);
    }

    public function dispatch(string $eventName, int $priority = CollectionInterface::DEFAULT_PRIORITY, ...$args): void
    {
        $this->dispatcher->dispatch($eventName, $priority, ...$args);
    }

    public function removeListener(string $eventName, int $priority = CollectionInterface::DEFAULT_PRIORITY, ...$args): bool
    {
        return $this->dispatcher->removeListener($eventName, $priority, ...$args);
    }
}