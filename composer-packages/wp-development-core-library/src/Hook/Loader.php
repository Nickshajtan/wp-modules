<?php

namespace HCC\Plugin\Core\Hook;

use HCC\Plugin\Core\Hook\Interfaces\HandlerInterface;
use HCC\Plugin\Core\Hook\Interfaces\CollectionInterface;

/**
 * Wrapper class for isolate native functionality
 * Facade for hook mechanism
 * The design was created with possible use outside of WordPress.
 * Eg it can be handled with Symfony Event Dispatcher versa WordPress hooks
 */
class Loader
{
    /**
     * @var array of Handler objects
     */
    private array $handlers;

    public function __construct(array $handlers)
    {
        $handlers = array_filter($handlers, fn($handler) => $handler instanceof HandlerInterface::class);
        $this->handlers = array_merge(
            $handlers,
            array(
                CollectionInterface::FILTER => new Handler(new HookCollection('add_filter')),
                CollectionInterface::ACTION => new Handler(new HookCollection('add_action')),
            )
        );
    }

    protected function checkIfHandlerExist(string $type): void
    {
        if (!isset($this->handlers[$type]) || !($this->handlers[$type] instanceof HandlerInterface::class)) {
            throw new \InvalidArgumentException(
                "Handler for type '{$type}' must be set before adding hooks and must be a Handler object."
            );
        }
    }

    protected function addHook(string $type, ...$args): void
    {
        $this->checkIfHandlerExist($type);
        $handler = $this->handlers[$type];
        $handler->add(...$args);
    }

    public function addFilter(...$args): void
    {
        $this->addHook(CollectionInterface::FILTER, ...$args);
    }

    public function removeFilter(): void
    {

    }

    public function applyFilters(): mixed
    {

    }

    public function addAction(...$args): void
    {
        $this->addHook(CollectionInterface::ACTION, ...$args);
    }

    public function removeAction(): void
    {

    }

    public function doAction(): void
    {

    }

    public function run(): void
    {

    }
}
