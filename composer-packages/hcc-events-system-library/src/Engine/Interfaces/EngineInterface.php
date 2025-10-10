<?php

namespace HCC\Events\Engine\Interfaces;

interface EngineInterface
{
    public function dispatch(object $event, array $args = []): void;
}