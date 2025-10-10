<?php

namespace HCC\Events\Engine;

use HCC\Events\Engine\Interfaces\EngineInterface;
use \Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class SymfonyEngine implements EngineInterface
{
    public function __construct(private readonly EventDispatcherInterface $dispatcher) {}

    public function dispatch(object $event, array $args = []): void
    {
        $this->dispatcher->dispatch($event);
    }
}
