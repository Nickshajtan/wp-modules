<?php

namespace HCC\Events\Dispatcher;

use HCC\Events\Interfaces\EventDispatcherInterface;
use HCC\Events\EventCollection;

class SynchronousDispatcher implements EventDispatcherInterface
{
    private EventCollection $eventCollection;

    public function __construct(EventCollection $eventCollection)
    {
        $this->eventCollection = $eventCollection;
    }

    public function dispatch(string $eventName, ...$args): void
    {
        $this->eventCollection->dispatchEvent($eventName, ...$args);
    }

    /**
     * @return string Event id
     */
    public function addListener(...$args): string
    {
        return $this->eventCollection->addEvent(...$args);
    }

    public function removeListener(...$args): bool
    {
        return $this->eventCollection->removeEvent(...$args);
    }
}
