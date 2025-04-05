<?php

namespace HCC\Events\Dispatcher;

use HCC\Events\EventCollection;
use HCC\Events\Dispatcher\Interfaces\EventDispatcherInterface;

abstract class AbstractDispatcher implements EventDispatcherInterface
{
    protected EventCollection $eventCollection;

    public function __construct(EventCollection $eventCollection)
    {
        $this->eventCollection = $eventCollection;
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

    protected function getEvents(string $eventName, int $priority): \Generator
    {
        foreach ($this->eventCollection->getAllEvents($eventName, $priority) as $event) {
            yield $event;
        }
    }
}
