<?php

namespace HCC\Events\Engine;

use HCC\Events\Engine\Interfaces\AsyncEngineInterface;
use \PxPHP\Scheduler\Scheduler;

class PxPHPEngine implements AsyncEngineInterface
{
    public function __construct(private readonly Scheduler $scheduler) {}

    public function dispatchAsync(object $event, array $args = []): void
    {
        $this->scheduler->schedule(function () use ($event, $args) {
            if (method_exists($event, 'dispatch')) {
                $event->dispatch(...$args);
            }
        });
    }
}
