<?php

namespace HCC\Events\Engine\Interfaces;

interface AsyncEngineInterface
{
    public function dispatchAsync(object $event, array $args = []): void;
}
