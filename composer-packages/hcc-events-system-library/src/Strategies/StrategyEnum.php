<?php

namespace HCC\Events\Strategies;

enum StrategyEnum: string
{
    case SINGLE = 'single';
    case BROADCAST = 'broadcast';
}