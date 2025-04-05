<?php

namespace HCC\Events\Engine;

use HCC\Events\Engine\Interfaces\AsyncEngineInterface;
use \React\EventLoop\LoopInterface;

class ReactPHPEngine implements AsyncEngineInterface
{
    public function __construct(private readonly LoopInterface $loop) {}

    public function dispatchAsync(object $event, array $args = []): void
    {
        $this->loop->futureTick(function () use ($event, $args) {
            if (method_exists($event, 'dispatch')) {
                $event->dispatch(...$args);
            }
        });
    }
}
