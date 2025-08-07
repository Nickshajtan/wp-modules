<?php

namespace HCC\Events\Engine\Interfaces;

interface SyncEngineInterface extends EngineInterface
{
    public function dispatchSync(object $event, array $args = []): void;
}
