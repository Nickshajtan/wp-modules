<?php

namespace HCC\Events\Dispatcher;

use HCC\Events\EventCollection;
use HCC\Events\Interfaces\EventDispatcherInterface;
use HCC\Events\Engine\Interfaces\AsyncEngineInterface;

class AsyncDispatcher implements EventDispatcherInterface
{
    private EventCollection $eventCollection;
    private AsyncEngineInterface $engine;

    public function __construct(EventCollection $eventCollection, AsyncEngineInterface $engine)
    {
        $this->eventCollection = $eventCollection;
        $this->engine = $engine;
    }

    public function dispatch(string $eventName, ...$args): void
    {
        foreach ($this->eventCollection->getAllEvents($eventName) as $event) {
            $this->engine->dispatchAsync($event, $args);
        }
    }

    public function addListener(...$args): string
    {
        return $this->eventCollection->addEvent(...$args);
    }

    public function removeListener(...$args): bool
    {
        return $this->eventCollection->removeEvent(...$args);
    }
}
