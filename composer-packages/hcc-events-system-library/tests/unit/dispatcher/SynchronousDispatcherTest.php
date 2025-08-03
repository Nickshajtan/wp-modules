<?php

use HCC\Events\Dispatcher\SynchronousDispatcher;
use HCC\Events\EventCollection;
use PHPUnit\Framework\TestCase;

class SynchronousDispatcherTest extends TestCase
{
    public function testDispatchInvokesEventCollection(): void
    {
        $eventName = 'event.test';
        $priority = 5;
        $args = ['foo', 'bar'];

        // Мок EventCollectionInterface
        $eventCollection = $this->createMock(EventCollection::class);
        $called = [];
        $eventCollection->expects($this->exactly(2))
            ->method('dispatchEvent')
            ->willReturnCallback(function ($event, ...$args) use (&$called) {
                $called[] = [$event, $args];
            });

        $dispatcher = new class($eventCollection) extends SynchronousDispatcher {
            public function __construct($eventCollection)
            {
                $this->eventCollection = $eventCollection;
            }

            protected function getEvents(string $eventName, int $priority): \Generator
            {
                yield (object)[ 'eventName' => $eventName ];
                yield (object)[ 'eventName' => $eventName ];
            }
        };

        $dispatcher->dispatch($eventName, $priority, ...$args);
        $this->assertCount(2, $called);
        $this->assertEquals('event.test', $called[0][0]);
        $this->assertEquals(['foo', 'bar'], $called[0][1]);
        $this->assertEquals('event.test', $called[1][0]);
        $this->assertEquals(['foo', 'bar'], $called[1][1]);
    }
}
