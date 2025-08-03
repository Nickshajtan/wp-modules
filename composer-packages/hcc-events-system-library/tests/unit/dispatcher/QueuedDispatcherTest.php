<?php

use HCC\Events\Dispatcher\QueuedDispatcher;
use HCC\Events\Event;
use HCC\Events\EventCollection;
use HCC\Events\Interfaces\JobInterface;
use HCC\Events\Queue\Interfaces\QueueEngineInterface;
use PHPUnit\Framework\TestCase;

class QueuedDispatcherTest extends TestCase
{
    public function testDispatchPushesJobsToQueue(): void
    {
        $eventName = 'queued.event';
        $priority = 10;
        $event1 = $this->createMock(Event::class);
        $event2 = $this->createMock(Event::class);

        $queue = $this->createMock(QueueEngineInterface::class);
        $queue->expects($this->exactly(2))
            ->method('push')
            ->with($this->callback(function ($job) {
                if (!$job instanceof JobInterface) return false;
                $job->handle();

                return true;
            }));

        $dispatcher = new class(new EventCollection(), $queue) extends QueuedDispatcher {
            protected function getEvents(string $eventName, int $priority): \Generator
            {
                yield $GLOBALS['event1'];
                yield $GLOBALS['event2'];
            }
        };

        $GLOBALS['event1'] = $event1;
        $GLOBALS['event2'] = $event2;

        $dispatcher->dispatch($eventName, $priority);
    }
}
