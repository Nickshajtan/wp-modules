<?php

namespace HCC\Plugin\Core\Hook\Interfaces;
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

    public function removeHook(string $hookName, string $id): void;
    public function executeHook(string $hookName, string $id): mixed;

    public function callHook(object $hook): mixed;

    public function getAllHooks(string $hookName = '', ?int $priority = null): array;
}