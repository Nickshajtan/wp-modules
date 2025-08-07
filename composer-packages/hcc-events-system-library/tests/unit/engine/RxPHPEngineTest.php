<?php

use HCC\Events\Engine\RxPHPEngine;
use PHPUnit\Framework\TestCase;
use HCC\Events\Event;
use \Rx\SchedulerInterface;
use \Rx\DisposableInterface;

class RxPHPEngineTest extends TestCase
{
    public function testDispatchAsyncExecutesDispatch(): void
    {
        $callbackCalled = false;
        $event = new Event(
            eventName: 'test.event',
            callback: function (...$args) use (&$callbackCalled) {
                $callbackCalled = $args === ['arg1', 'arg2'];
            }
        );
        $disposable = $this->createMock(DisposableInterface::class);
        $scheduler = $this->createMock(SchedulerInterface::class);
        $scheduler->method('schedule')->willReturnCallback(function ($action) use($disposable) {
            $action();
            return $disposable;
        });
        $engine = new RxPHPEngine($scheduler);
        $engine->dispatchAsync($event, ['arg1', 'arg2']);
        $this->assertTrue($callbackCalled);
    }
}