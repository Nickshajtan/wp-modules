<?php

namespace HCC\Events\Dispatcher\Interfaces;

use HCC\Events\Interfaces\CollectionInterface;

interface EventDispatcherInterface
{
    public function dispatch(string $eventName, int $priority = CollectionInterface::DEFAULT_PRIORITY, ...$args): void;
    public function addListener(...$args): string;
    public function removeListener(...$args): bool;
}
