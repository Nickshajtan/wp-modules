<?php

use HCC\Events\Bridge\CompositeBridge;
use HCC\Events\Dispatcher\Interfaces\EventDispatcherInterface;
use PHPUnit\Framework\TestCase;

class CompositeBridgeTest extends TestCase
{
    public function testConstructorFiltersInvalidDispatchers(): void
    {
        $valid = $this->createMock(EventDispatcherInterface::class);
        $invalid = new \stdClass();

        $bridge = new CompositeBridge([$valid, $invalid]);

        $reflection = new \ReflectionClass($bridge);
        $property = $reflection->getProperty('dispatchers');
        $property->setAccessible(true);
        $dispatchers = $property->getValue($bridge);

        $this->assertCount(1, $dispatchers);
        $this->assertSame($valid, $dispatchers[0]);
    }

    public function testSubscribeDelegatesToAllDispatchers(): void
    {
        $dispatcher1 = $this->createMock(EventDispatcherInterface::class);
        $dispatcher2 = $this->createMock(EventDispatcherInterface::class);

        $eventName = 'some.event';
        $priority = 10;
        $callback = fn() => null;
        $args = ['foo'];

        $dispatcher1->expects($this->once())
            ->method('addListener')
            ->with($eventName, $callback, $priority, ...$args)
            ->willReturn('listener-1');

        $dispatcher2->expects($this->once())
            ->method('addListener')
            ->with($eventName, $callback, $priority, ...$args)
            ->willReturn('listener-2');

        $bridge = new CompositeBridge([$dispatcher1, $dispatcher2]);
        $result = $bridge->subscribe($eventName, $callback, $priority, ...$args);

        $expected = [
            get_class($dispatcher1) => [
                $eventName => [
                    $priority => 'listener-1'
                ]
            ],
            get_class($dispatcher2) => [
                $eventName => [
                    $priority => 'listener-2'
                ]
            ],
        ];

        $this->assertEquals($expected, $result);
    }

    public function testDispatchDelegatesToAllDispatchers(): void
    {
        $dispatcher1 = $this->createMock(EventDispatcherInterface::class);
        $dispatcher2 = $this->createMock(EventDispatcherInterface::class);

        $eventName = 'some.event';
        $priority = 5;
        $args = ['arg'];

        $dispatcher1->expects($this->once())
            ->method('dispatch')
            ->with($eventName, $priority, ...$args);

        $dispatcher2->expects($this->once())
            ->method('dispatch')
            ->with($eventName, $priority, ...$args);

        $bridge = new CompositeBridge([$dispatcher1, $dispatcher2]);
        $bridge->dispatch($eventName, $priority, ...$args);
    }

    public function testRemoveListenerDelegatesToAllDispatchers(): void
    {
        $dispatcher1 = $this->createMock(EventDispatcherInterface::class);
        $dispatcher2 = $this->createMock(EventDispatcherInterface::class);

        $eventName = 'some.event';
        $priority = 1;
        $args = ['x'];

        $dispatcher1->expects($this->once())
            ->method('removeListener')
            ->with($eventName, $priority, ...$args)
            ->willReturn(true);

        $dispatcher2->expects($this->once())
            ->method('removeListener')
            ->with($eventName, $priority, ...$args)
            ->willReturn(true);

        $bridge = new CompositeBridge([$dispatcher1, $dispatcher2]);
        $result = $bridge->removeListener($eventName, $priority, ...$args);

        $expected = [
            get_class($dispatcher1) => [
                $eventName => [
                    $priority => 'removed-1'
                ]
            ],
            get_class($dispatcher2) => [
                $eventName => [
                    $priority => 'removed-2'
                ]
            ],
        ];

        $this->assertEquals($expected, $result);
    }
}
