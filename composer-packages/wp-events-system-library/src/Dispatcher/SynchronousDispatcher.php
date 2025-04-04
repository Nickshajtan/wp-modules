<?php

namespace HCC\Events\Dispatcher;

use HCC\Events\Interfaces\CollectionInterface;
class SynchronousDispatcher extends AbstractDispatcher
{
    public function dispatch(string $eventName, int $priority = CollectionInterface::DEFAULT_PRIORITY, ...$args): void
    {
        foreach ($this->getEvents($eventName, $priority) as $event) {
            $this->eventCollection->dispatchEvent($event->eventName, ...$args);
        }
    }
}
