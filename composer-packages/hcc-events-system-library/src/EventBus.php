<?php

namespace HCC\Events;

use HCC\Events\Engine\Interfaces\EngineInterface;
use HCC\Events\Strategies\Interfaces\DispatchStrategyInterface;
use HCC\Events\Strategies\Interfaces\MiddlewareInterface;

class EventBus
{
    private readonly DispatchStrategyInterface $strategy;
    private array $middlewares = [];

    public function __construct(DispatchStrategyInterface $strategy, array $middlewares = [])
    {
        $this->strategy = $strategy;
        $this->middlewares = array_filter($middlewares, fn(object $instance) => is_a($instance, MiddlewareInterface::class));
    }

    public function dispatch(object $event, array $args = []): void
    {
        $this->runDispatch($event, $args, false);
    }

    public function dispatchAsync(object $event, array $args = []): void
    {
        $this->runDispatch($event, $args, true);
    }

    protected function runDispatch(object $event, array $args, bool $async): void
    {
        $mode = $this->strategy->getMode()->value;
        $engines = $this->strategy->getEnginesFor($event, $async);
        $options = $this->strategy->getOptions();

        $core = function(object $e, array $a) use ($mode, $engines, $options) {
            $lastError = null;
        };

        $this->applyMiddlewares( $event, $args );
    }

    protected function applyMiddlewares(object $event, array $args): void
    {
        $next = array_reduce(
            array_reverse($this->middlewares),
            fn($n, $mw) => fn($e,$a) => $mw->handle($e, $a, $n),
            $core
        );

        $next($event, $args);
    }

    protected function dispatchEngine(EngineInterface $engine, object $event, array $args = []): void
    {
        $engine->dispatch($event, $args);
    }
}
