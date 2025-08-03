<?php

use HCC\Events\Dispatcher\Interfaces\EventDispatcherInterface;
use HCC\Events\Observer\BridgeObserver;
use HCC\Events\Observer\SimpleObserver;
use PHPUnit\Framework\TestCase;
use HCC\Events\Observer\ObserverFactory;

class ObserverFactoryTest extends TestCase
{
    public function testCreateObserverThrowsWhenNoValidDispatchers(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("The array does not contain valid dispatchers.");

        $invalid = ['not', 'dispatchers', 123, null];
        ObserverFactory::createObserver($invalid);
    }

    public function testCreateObserverReturnsSimpleObserverForSingleDispatcher(): void
    {
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $result = ObserverFactory::createObserver([$dispatcher]);
        $this->assertInstanceOf(SimpleObserver::class, $result);
    }

    public function testCreateObserverReturnsBridgeObserverForMultipleDifferentDispatchers()
    {
        $dispatcher1 = $this->createMock(EventDispatcherInterface::class);
        $dispatcher2 = $this->createMock(EventDispatcherInterface::class);
        $result = ObserverFactory::createObserver([$dispatcher1, $dispatcher2]);
        $this->assertInstanceOf(BridgeObserver::class, $result);
    }

    public function testCreateObserverFiltersOutInvalidEntries(): void
    {
        $dispatcher1 = $this->createMock(EventDispatcherInterface::class);
        $invalid = 'string';
        $dispatcher2 = $this->createMock(EventDispatcherInterface::class);
        $result = ObserverFactory::createObserver([$dispatcher1, $invalid, $dispatcher2]);
        $this->assertInstanceOf(BridgeObserver::class, $result);
    }

    public function testCreateObserverTreatsIdenticalDispatchersAsSingle(): void
    {
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        // Even if provided twice, it's the same dispatcher
        $result = ObserverFactory::createObserver([$dispatcher, $dispatcher]);
        $this->assertInstanceOf(SimpleObserver::class, $result);
    }
}
