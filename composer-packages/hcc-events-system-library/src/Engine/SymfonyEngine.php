<?php

namespace HCC\Events\Engine;

use HCC\Events\Engine\Interfaces\SyncEngineInterface;
use \Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class SymfonyEngine implements SyncEngineInterface
{
    public function __construct(private readonly EventDispatcherInterface $dispatcher) {}

    public function dispatchSync(object $event, array $args = []): void
    {
        $this->dispatcher->dispatch($event);
    }
}
