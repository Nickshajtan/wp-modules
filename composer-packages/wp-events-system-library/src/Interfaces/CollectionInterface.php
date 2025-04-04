<?php

namespace HCC\Events\Interfaces;

interface CollectionInterface
{
    public const DEFAULT_PRIORITY = 10;

    public function addEvent(
        string          $eventName,
        callable        $callback,
        int             $priority = self::DEFAULT_PRIORITY,
        int             $acceptedArgs = 1,
        ?object         $object = null,
        null|string|int $id = null
    ): void;

    public function removeEvent(
        string    $eventName,
        string    $id = '',
        int       $priority = self::DEFAULT_PRIORITY,
        ?callable $callback = null
    ): bool;

    public function registerEvent(string $eventName, string $id = '', ?int $priority = null): void;

    public function deregisterEvent(string $eventName, string $id = '', ?int $priority = null): void;

    public function dispatchEvent(string $eventName, string $id = '', ...$args): mixed;

    public function getAllEvents(string $eventName = '', int $priority = self::DEFAULT_PRIORITY): array;
}
