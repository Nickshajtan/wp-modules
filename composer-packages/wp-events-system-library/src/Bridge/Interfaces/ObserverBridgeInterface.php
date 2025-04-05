<?php

namespace HCC\Events\Bridge\Interfaces;

use HCC\Events\Interfaces\CollectionInterface;

interface ObserverBridgeInterface
{
    public function subscribe(string $eventName, callable $callback, int $priority = CollectionInterface::DEFAULT_PRIORITY, ...$args): array;

    public function dispatch(string $eventName, int $priority = CollectionInterface::DEFAULT_PRIORITY, mixed ...$args): void;

    public function removeListener(string $eventName, int $priority = CollectionInterface::DEFAULT_PRIORITY, ...$args): array;
}
