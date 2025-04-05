<?php

namespace HCC\Events\Engine;

use HCC\Events\Engine\Interfaces\AsyncEngineInterface;
use \Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class SymfonyEngine implements AsyncEngineInterface
{
    public function __construct(private readonly EventDispatcherInterface $dispatcher) {}

    public function dispatchAsync(object $event, array $args = []): void
    {
        $this->dispatcher->dispatch($event);
    }
}
