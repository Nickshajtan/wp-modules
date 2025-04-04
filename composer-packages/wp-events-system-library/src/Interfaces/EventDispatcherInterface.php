<?php

namespace HCC\Events\Interfaces;

interface EventDispatcherInterface
{
    public function dispatch(string $eventName, ...$args): mixed;
    public function addListener(string $eventName, callable $callback, int $priority = 10): void;
    public function removeListener(string $eventName, callable $callback): void;
}
