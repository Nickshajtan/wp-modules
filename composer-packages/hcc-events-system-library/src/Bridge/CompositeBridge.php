<?php

namespace HCC\Events\Bridge;

use HCC\Events\Bridge\Interfaces\ObserverBridgeInterface;
use HCC\Events\Dispatcher\Interfaces\EventDispatcherInterface;
use HCC\Events\Interfaces\CollectionInterface;

class CompositeBridge implements ObserverBridgeInterface
{
    /**
     * @var EventDispatcherInterface[]
     */
    protected array $dispatchers = [];

    public function __construct(array $dispatchers = [])
    {
        $dispatchers = array_filter($dispatchers, fn($dispatcher) => $dispatcher instanceof EventDispatcherInterface);
        if (count($dispatchers) > 0) {
            foreach ($dispatchers as $dispatcher) {
                $this->addBridge($dispatcher);
            }
        }
    }

    public function addBridge(EventDispatcherInterface $dispatcher): void
    {
        $this->dispatchers[] = $dispatcher;
    }

    public function subscribe(string $eventName, callable $callback, int $priority = CollectionInterface::DEFAULT_PRIORITY, ...$args): array
    {
        $result = [];
        foreach ($this->dispatchers as $bridge) {
            $result[$bridge::class][$eventName][$priority] = $bridge->addListener($eventName, $callback, $priority, ...$args);
        }

        return $result;
    }

    public function dispatch(string $eventName, int $priority = CollectionInterface::DEFAULT_PRIORITY, mixed ...$args): void
    {
        foreach ($this->dispatchers as $bridge) {
            $bridge->dispatch($eventName, $priority, ...$args);
        }
    }

    public function removeListener(string $eventName, int $priority = CollectionInterface::DEFAULT_PRIORITY, ...$args): array
    {
        $result = [];
        foreach ($this->dispatchers as $bridge) {
            $result[$bridge::class][$eventName][$priority] = $bridge->removeListener($eventName, $priority, ...$args);
        }

        return $result;
    }
}