<?php

use HCC\Events\Dispatcher\AsyncDispatcher;
use HCC\Events\Engine\Interfaces\AsyncEngineInterface;
use HCC\Events\Event;
use HCC\Events\EventCollection;
use PHPUnit\Framework\TestCase;

class AsyncDispatcherTest extends TestCase
{
    public function testDispatchSendsEventsToAsyncEngine(): void
    {
        $eventName = 'async.event';
        $priority = 3;
        $args = ['foo', 'bar'];
        $engine = $this->createMock(AsyncEngineInterface::class);
        $engine->expects($this->exactly(2))->method('dispatchAsync');

        $dispatcher = new class(new EventCollection(), $engine) extends AsyncDispatcher {
            protected function getEvents(string $eventName, int $priority): \Generator
            {
                yield (object)[ 'eventName' => $eventName ];
                yield (object)[ 'eventName' => $eventName ];
            }
        };

        $dispatcher->dispatch($eventName, $priority, ...$args);
    }
}
