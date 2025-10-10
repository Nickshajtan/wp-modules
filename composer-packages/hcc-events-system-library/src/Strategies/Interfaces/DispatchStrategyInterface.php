<?php

namespace HCC\Events\Strategies\Interfaces;

use HCC\Events\Strategies\StrategyEnum;

interface DispatchStrategyInterface
{
    public function getMode(): StrategyEnum;

    public function getEnginesFor(object $event, bool $async): array;
    public function getOptions(): array;
}