<?php

namespace HCC\Events\Strategies;

use HCC\Events\Engine\Interfaces\EngineInterface;
use HCC\Events\Strategies\Interfaces\DispatchStrategyInterface;

final class SingleEngineStrategy implements DispatchStrategyInterface
{
    public function __construct(private readonly EngineInterface $engine) {}
    public function getEnginesFor(object $event, bool $async): array
    {
        return [ $this->engine ];
    }

    public function getMode(): StrategyEnum
    {
        return StrategyEnum::SINGLE;
    }
    public function getOptions(): array { return []; }
}
