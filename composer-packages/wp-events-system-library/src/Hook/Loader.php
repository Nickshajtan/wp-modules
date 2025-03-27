<?php

namespace HCC\Events\Hook;

use HCC\Events\Hook\Interfaces\HandlerInterface;
use HCC\Events\Hook\Interfaces\CollectionInterface;

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

    public function __construct(array $callbacks = array())
    {
        $callbacks = array_filter($callbacks, fn($handler) => $handler instanceof CallbacksStore::class);
        array_walk($callbacks, function (&$value) {
            $value = $this->buildHandler($value);
        });

        $this->handlers = array_merge(
            $callbacks,
            array(
                CollectionInterface::FILTER => $this->buildHandler(
                    new CallbacksStore(add: 'add_filter', remove: 'remove_filter', execute: 'apply_filters')
                ),
                CollectionInterface::ACTION => $this->buildHandler(
                    new CallbacksStore(add: 'add_action', remove: 'remove_action', execute: 'do_action')
                ),
            )
        );
    }

    protected function buildHandler(CallbacksStore $callbacks): Handler
    {
        return new Handler(new HookCollection($callbacks));
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

    /**
     * @param array $callbacks
     * @param $myClass
     * @return true
     */
    public function getArr(array $callbacks, $myClass): bool
    {
        return array_walk($callbacks, function (&$value, $key) use ($myClass) {
            $value = $myClass->modify($value); // Застосовуємо метод класу до кожного елемента
        });
    }
}
