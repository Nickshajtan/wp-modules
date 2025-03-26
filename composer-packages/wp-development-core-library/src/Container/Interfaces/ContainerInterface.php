<?php

namespace HCC\Core\Container\Interfaces;

interface ContainerInterface
{
    public function has(string $name): bool;

    public function set(string $name, callable $factory, bool $singleton = false): void;

    public function get(string $name): mixed;
}
