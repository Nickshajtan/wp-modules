<?php

namespace HCC\Events\Hook\Interfaces;

use HCC\Events\Hook\Hook;
interface CollectionInterface
{
    public const ACTION = 'action';
    public const FILTER = 'filter';

    public function addHook(
        string          $hookName,
        callable        $callback,
        int             $priority = 10,
        int             $acceptedArgs = 1,
        ?object         $object = null,
        null|string|int $id = null
    ): void;

    public function removeHook(string $hookName, string $id): bool;
    public function dispatchHook(string $hookName, string $id = '', ?int $priority = null, ...$args): mixed;
    public function getAllHooks(string $hookName = '', ?int $priority = null): array;
    public function registerHook(string $hookName, string $id = '', ?int $priority = null): void;
    public function deregisterHook(string $hookName, string $id = '', ?int $priority = null): void;
}