<?php

namespace HCC\Events\Dispatcher;

use HCC\Events\Event;
use HCC\Events\EventCollection;
use HCC\Events\Interfaces\CollectionInterface;
use HCC\Events\Queue\Interfaces\QueueEngineInterface;
use HCC\Events\Interfaces\JobInterface;

class QueuedDispatcher extends AbstractDispatcher
{
    private QueueEngineInterface $engine;

    public function __construct(EventCollection $eventCollection, QueueEngineInterface $engine)
    {
        parent::__construct($eventCollection);
        $this->engine = $engine;
    }

    public function dispatch(string $eventName, int $priority = CollectionInterface::DEFAULT_PRIORITY, ...$args): void
    {
        foreach ($this->getEvents($eventName, $priority) as $event) {
            $this->engine->push(new class($event) implements JobInterface {
                private Event $event;

                public function __construct(Event $event)
                {
                    $this->event = $event;
                }

                public function handle(): void
                {
                    $this->event->dispatch();
                }
            });
        }
    }
}
