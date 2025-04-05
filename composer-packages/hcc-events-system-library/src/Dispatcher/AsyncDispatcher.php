<?php

namespace HCC\Events\Dispatcher;

use HCC\Events\EventCollection;
use HCC\Events\Interfaces\CollectionInterface;
use HCC\Events\Engine\Interfaces\AsyncEngineInterface;

class AsyncDispatcher extends AbstractDispatcher
{
    private AsyncEngineInterface $engine;

    public function __construct(EventCollection $eventCollection, AsyncEngineInterface $engine)
    {
        parent::__construct($eventCollection);
        $this->engine = $engine;
    }

    public function dispatch(string $eventName, int $priority = CollectionInterface::DEFAULT_PRIORITY, ...$args): void
    {
        foreach ($this->getEvents($eventName, $priority) as $event) {
            $this->engine->dispatchAsync($event, $args);
        }
    }
}
