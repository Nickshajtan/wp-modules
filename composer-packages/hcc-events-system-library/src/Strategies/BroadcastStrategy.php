<?php

namespace HCC\Events\Strategies;

use HCC\Events\Engine\Interfaces\EngineInterface;
use HCC\Events\Strategies\Interfaces\DispatchStrategyInterface;

final class BroadcastStrategy implements DispatchStrategyInterface
{
    private readonly array $engines;
    public function __construct(array $engines)
    {
        $this->engines = array_filter($engines, fn(object $engine) => is_a($engine, EngineInterface::class));
    }
    public function getMode(): StrategyEnum
    {
        return StrategyEnum::BROADCAST;
    }

    public function getEnginesFor(object $event, bool $async): array
    {
        return $this->engines;
    }

    public function getOptions(): array
    {
        return [];
    }
}