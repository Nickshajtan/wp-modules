<?php

namespace HCC\Events\Engine;

use HCC\Events\Engine\Interfaces\EngineInterface;
use \Rx\Observable;
use Rx\SchedulerInterface;

/**
 * @see https://github.com/ReactiveX/RxPHP
 */
class RxPHPEngine implements EngineInterface
{
    public function __construct(private readonly SchedulerInterface $scheduler) {}
    public function dispatch(object $event, array $args = []): void
    {
        Observable::create(function ($observer) use ($event, $args) {
            if (method_exists($event, 'dispatch')) {
                $event->dispatch(...$args);
            }

            $observer->onCompleted();
        })->subscribeOn($this->scheduler)->subscribe();
    }
}
