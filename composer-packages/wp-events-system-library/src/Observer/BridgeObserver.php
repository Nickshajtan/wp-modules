<?php

namespace HCC\Events\Observer;

use HCC\Events\Bridge\Interfaces\ObserverBridgeInterface;
use HCC\Events\Interfaces\CollectionInterface;

/**
 * Observer
 * ↓
 * Bridge (eg, SyncBridge)
 * ↓
 * Dispatcher (eg SynchronousDispatcher)
 * ↓
 * EventCollection (save Events and call them)
 */

readonly class BridgeObserver
{
    public function __construct(private ObserverBridgeInterface $bridge){}

    public function subscribe(string $eventName, callable $callback, int $priority = CollectionInterface::DEFAULT_PRIORITY, ...$args): array
    {
        return $this->bridge->subscribe($eventName, $callback, $priority, ...$args);
    }

    public function dispatch(string $eventName, int $priority = CollectionInterface::DEFAULT_PRIORITY, mixed ...$args): void
    {
        $this->bridge->dispatch($eventName, $priority, ...$args);
    }

    public function removeListener(string $eventName, int $priority = CollectionInterface::DEFAULT_PRIORITY, ...$args): array
    {
        return $this->bridge->removeListener($eventName, $priority, ...$args);
    }
}
