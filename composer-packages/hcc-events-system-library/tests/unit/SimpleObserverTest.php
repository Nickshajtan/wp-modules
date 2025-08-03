<?php

use PHPUnit\Framework\TestCase;
use HCC\Events\Dispatcher\Interfaces\EventDispatcherInterface;
use HCC\Events\Observer\SimpleObserver;

class SimpleObserverTest extends TestCase
{
    public function testSubscribeDelegatesToDispatcher(): void
    {
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $observer = new SimpleObserver($dispatcher);

        $event = 'test.event';
        $callback = fn() => null;
        $priority = 5;
        $args = ['foo', 'bar'];

        $dispatcher->expects($this->once())
            ->method('addListener')
            ->with($event, $callback, $priority, ...$args)
            ->willReturn('listener-id');

        $result = $observer->subscribe($event, $callback, $priority, ...$args);
        $this->assertEquals('listener-id', $result);
    }

    public function testDispatchDelegatesToDispatcher(): void
    {
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $observer = new SimpleObserver($dispatcher);

        $event = 'dispatch.event';
        $priority = 3;
        $args = ['arg1'];

        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($event, $priority, ...$args);

        $observer->dispatch($event, $priority, ...$args);
    }

    public function testRemoveListenerDelegatesToDispatcher(): void
    {
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $observer = new SimpleObserver($dispatcher);

        $event = 'remove.event';
        $priority = 7;
        $args = ['x'];

        $dispatcher->expects($this->once())
            ->method('removeListener')
            ->with($event, $priority, ...$args)
            ->willReturn(true);

        $result = $observer->removeListener($event, $priority, ...$args);
        $this->assertTrue($result);
    }
}
