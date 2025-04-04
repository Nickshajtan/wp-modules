<?php

namespace HCC\Events;

class Observer
{
    private array $dispatchers = [];

    public function __construct(private HookCollection $hookCollection)
    {

    }

    public function subscribe(string $eventName, callable $callback, int $priority = 10): void
    {
        // Підписка через WordPress, RxPHP, Symfony і т.д.
        $this->hookCollection->addHook(
            $eventName,
            $callback,
            $priority
        );

        // Якщо потрібно, додаємо до інших механізмів
        $this->addToOtherDispatchers($eventName, $callback, $priority);
    }

    public function dispatch(string $eventName, ...$args): mixed
    {
        return $this->hookCollection->dispatchHook($eventName, '', ...$args);
    }

    private function addToOtherDispatchers(string $eventName, callable $callback, int $priority): void
    {
        foreach ($this->dispatchers as $dispatcher) {
            $dispatcher->addListener($eventName, $callback, $priority);
        }
    }

    public function addDispatcher(EventDispatcherInterface $dispatcher): void
    {
        $this->dispatchers[] = $dispatcher;
    }

    public function removeListener(string $eventName, callable $callback): void
    {
        foreach ($this->dispatchers as $dispatcher) {
            $dispatcher->removeListener($eventName, $callback);
        }
    }
}
