<?php

namespace HCC\Events\Hook\Interfaces;

interface CollectionInterface
{
    public const ACTION = 'action';
    public const FILTER = 'filter';
    public const DEFAULT_PRIORITY = 10;

    public function addHook(
        string          $hookName,
        callable        $callback,
        int             $priority = self::DEFAULT_PRIORITY,
        int             $acceptedArgs = 1,
        ?object         $object = null,
        null|string|int $id = null
    ): void;

    public function removeHook(
        string    $hookName,
        string    $id,
        int       $priority = self::DEFAULT_PRIORITY,
        ?callable $callback = null): bool;

    public function dispatchHook(string $hookName, string $id = '', ...$args): mixed;

    public function getAllHooks(string $hookName = '', int $priority = self::DEFAULT_PRIORITY): array;

    public function registerHook(string $hookName, string $id = '', ?int $priority = null): void;

    public function deregisterHook(string $hookName, string $id = '', ?int $priority = null): void;
}