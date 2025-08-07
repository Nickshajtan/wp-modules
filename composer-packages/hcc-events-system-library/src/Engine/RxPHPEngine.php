<?php

namespace HCC\Events\Engine;

use HCC\Events\Engine\Interfaces\AsyncEngineInterface;
use \Rx\Observable;
use Rx\SchedulerInterface;

/**
 * @see https://github.com/ReactiveX/RxPHP
 */
class RxPHPEngine implements AsyncEngineInterface
{
    public function __construct(private readonly SchedulerInterface $scheduler) {}
    public function dispatchAsync(object $event, array $args = []): void
    {
        Observable::create(function ($observer) use ($event, $args) {
            if (method_exists($event, 'dispatch')) {
                $event->dispatch(...$args);
            }

            $observer->onCompleted();
        })->subscribeOn($this->scheduler)->subscribe();
    }
}
