<?php

namespace HCC\Events\Engine\Interfaces;

interface AsyncEngineInterface extends EngineInterface
{
    public function dispatchAsync(object $event, array $args = []): void;
}
