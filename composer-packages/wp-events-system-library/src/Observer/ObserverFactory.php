<?php

namespace HCC\Events\Observer;

use HCC\Events\Bridge\CompositeBridge;
use HCC\Events\Dispatcher\Interfaces\EventDispatcherInterface;

/**
 * Factory class for automatic Observer creating
 */
class ObserverFactory
{
    /**
     *
     * @param EventDispatcherInterface[] $dispatchers
     * @return SimpleObserver|BridgeObserver
     */
    public static function createObserver(array $dispatchers): SimpleObserver|BridgeObserver
    {
        $dispatchers = array_filter($dispatchers, fn($dispatcher) => $dispatcher instanceof EventDispatcherInterface);
        if (0 === count($dispatchers)) {
            throw new \InvalidArgumentException("The array does not contain valid dispatchers.");
        }

        if (1 === count(array_unique($dispatchers, SORT_REGULAR))) {
            // Since there is only one class in the array we return SimpleObserver
            return new SimpleObserver($dispatchers[0]);
        }

        return new BridgeObserver(new CompositeBridge($dispatchers));
    }
}
