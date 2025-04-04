<?php

namespace HCC\Events\Dispatcher;

use HCC\Events\Event;
use HCC\Events\EventCollection;
use HCC\Events\Interfaces\EventDispatcherInterface;
use HCC\Events\Queue\Interfaces\QueueEngineInterface;
use HCC\Events\Interfaces\JobInterface;

class QueuedDispatcher implements EventDispatcherInterface
{
    private EventCollection $eventCollection;
    private QueueEngineInterface $engine;

    public function __construct(EventCollection $eventCollection, QueueEngineInterface $engine)
    {
        $this->eventCollection = $eventCollection;
        $this->engine = $engine;
    }

    public function dispatch(string $eventName, ...$args): void
    {
        foreach ($this->eventCollection->getAllEvents($eventName) as $event) {
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

    public function addListener(...$args): string
    {
        return $this->eventCollection->addEvent(...$args);
    }

    public function removeListener(...$args): bool
    {
        return $this->eventCollection->removeEvent(...$args);
    }
}
