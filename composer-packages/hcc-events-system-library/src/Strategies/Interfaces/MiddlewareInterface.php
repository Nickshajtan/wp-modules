<?php

namespace HCC\Events\Strategies\Interfaces;

interface MiddlewareInterface {
    public function handle(object $event, array $args, callable $next): void;
}