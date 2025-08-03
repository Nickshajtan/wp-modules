<?php

namespace observers;

use HCC\Events\Bridge\Interfaces\ObserverBridgeInterface;
use HCC\Events\Observer\BridgeObserver;
use PHPUnit\Framework\TestCase;

class BridgeObserverTest extends TestCase
{
    public function testSubscribeDelegatesToBridge(): void
    {
        $bridge = $this->createMock(ObserverBridgeInterface::class);
        $observer = new BridgeObserver($bridge);

        $event = 'event.subscribe';
        $callback = fn() => null;
        $priority = 2;
        $args = ['foo', 'bar'];

        $expected = ['listener-id-1', 'listener-id-2'];

        $bridge->expects($this->once())
            ->method('subscribe')
            ->with($event, $callback, $priority, ...$args)
            ->willReturn($expected);

        $result = $observer->subscribe($event, $callback, $priority, ...$args);
        $this->assertSame($expected, $result);
    }

    public function testDispatchDelegatesToBridge(): void
    {
        $bridge = $this->createMock(ObserverBridgeInterface::class);
        $observer = new BridgeObserver($bridge);

        $event = 'event.dispatch';
        $priority = 1;
        $args = ['some', 'args'];

        $bridge->expects($this->once())
            ->method('dispatch')
            ->with($event, $priority, ...$args);

        $observer->dispatch($event, $priority, ...$args);
    }

    public function testRemoveListenerDelegatesToBridge(): void
    {
        $bridge = $this->createMock(ObserverBridgeInterface::class);
        $observer = new BridgeObserver($bridge);

        $event = 'event.remove';
        $priority = 10;
        $args = ['arg1'];

        $expected = ['removed-1', 'removed-2'];

        $bridge->expects($this->once())
            ->method('removeListener')
            ->with($event, $priority, ...$args)
            ->willReturn($expected);

        $result = $observer->removeListener($event, $priority, ...$args);
        $this->assertSame($expected, $result);
    }
}
