<?php

namespace HCC\Events\Interfaces;

interface EventDispatcherInterface
{
    public function dispatch(string $eventName, ...$args): void;
    public function addListener(...$args): string;
    public function removeListener(...$args): bool;
}
