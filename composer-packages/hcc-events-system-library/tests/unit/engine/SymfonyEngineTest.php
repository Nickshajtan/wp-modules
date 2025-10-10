<?php

use PHPUnit\Framework\TestCase;
use HCC\Events\Event;
use \Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use HCC\Events\Engine\SymfonyEngine;

class SymfonyEngineTest extends TestCase
{
    public function testDispatchSyncCallsDispatcher(): void
    {
        $event = new Event(
            eventName: 'foo_event',
            callback: fn () => 'bar'
        );
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->expects($this->once())->method('dispatch')->with($event);
        $engine = new SymfonyEngine($dispatcher);
        $engine->dispatch($event);
    }
}
