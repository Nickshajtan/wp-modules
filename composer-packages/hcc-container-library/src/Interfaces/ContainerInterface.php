<?php

namespace HCC\Container\Interfaces;

use Psr\Container\ContainerInterface as PsrContainerInterface;

interface ContainerInterface extends PsrContainerInterface
{
    public function has(string $name): bool;

    public function set(string $name, callable $factory, bool $singleton = false): void;

    public function get(string $name): mixed;
}
