<?php

namespace HCC\Core\Hook\Interfaces;
interface HandlerInterface
{
    public function add(...$args): void;

    public function remove(...$args): void;

    public function execute(...$args): mixed;

    public function executeAll(...$args): \Generator;
}