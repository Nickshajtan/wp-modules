<?php

namespace HCC\Events\Hook\Interfaces;

interface EventAdapterInterface
{
    public const DEFAULT_PRIORITY = 10;
    public function addListener(string $event, callable $callback, int $priority = self::DEFAULT_PRIORITY, int $acceptedArgs = 1): void;
    public function removeListener(string $event, callable|string $callback = '', int $priority = self::DEFAULT_PRIORITY): void;
    public function dispatch(string $event, array $args = []): mixed;
}
