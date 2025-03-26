<?php

namespace HCC\Core\Hook;

use HCC\Core\Hook\Interfaces\CollectionInterface;
use HCC\Core\Hook\Interfaces\HandlerInterface;

/**
 * Facade processor for HookCollection
 */
class Handler implements HandlerInterface
{
    protected CollectionInterface $collection;

    public function __construct(CollectionInterface $collection)
    {
        $this->collection = $collection;
    }

    public function add(...$args): void
    {
        $this->collection->addHook(...$args);
    }

    public function remove(...$args): void
    {
        $this->collection->removeHook(...$args);
    }

    public function execute(...$args): mixed
    {
        return $this->collection->executeHook(...$args);
    }

    public function executeAll(...$args): \Generator
    {
        $hooks = $this->collection->getAllHooks(...$args);
        if (count($hooks) > 0) {
            foreach ($hooks as $hook) {
                yield $this->collection->callHook($hook);
            }
        }
    }
}