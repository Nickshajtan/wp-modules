<?php

namespace HCC\Events\Engine;

use HCC\Events\Engine\Interfaces\AsyncEngineInterface;
use \Rx\Observable;
use \Rx\Scheduler\NewThreadScheduler;

class PxPHPEngine implements AsyncEngineInterface
{
    public function dispatchAsync(object $event, array $args = []): void
    {
        Observable::create(function ($observer) use ($event, $args) {
            if (method_exists($event, 'dispatch')) {
                $event->dispatch(...$args);
            }

            $observer->onCompleted();
        })->subscribeOn(NewThreadScheduler::instance());
    }
}
