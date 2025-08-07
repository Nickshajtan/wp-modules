<?php

use PHPUnit\Framework\TestCase;
use \React\EventLoop\LoopInterface;
use HCC\Events\Engine\ReactPHPEngine;
use HCC\Events\Event;

class ReactPHPEngineTest extends TestCase
{
    public function testDispatchAsyncCallsFutureTickWithCallback(): void
    {
        $loop = $this->createMock(LoopInterface::class);
        $callbackCalled = false;
        $event = new Event(
            eventName: 'test.event',
            callback: function (...$args) use (&$callbackCalled) {
                $callbackCalled = $args === ['arg1', 'arg2'];
            }
        );


        $loop->expects($this->once())
            ->method('futureTick')
            ->with($this->callback(function ($callback) use (&$callbackCalled) {
                $callback();
                return true;
            }));

        $engine = new ReactPHPEngine($loop);
        $engine->dispatchAsync($event, ['arg1', 'arg2']);

        $this->assertTrue($callbackCalled);
    }
}
