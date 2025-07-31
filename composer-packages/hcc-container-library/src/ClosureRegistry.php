<?php

namespace HCC\Container;

use HCC\Container\Interfaces\ContainerInterface;

class ClosureRegistry implements ContainerInterface
{
    private array $closures = [];
    private array $singletons = [];

    private array $instances = [];

    public function set(string $name, callable $factory, bool $singleton = true): void
    {
        $this->closures[$name] = $factory;
        if ($singleton) {
            $this->singletons[$name] = true;
        }
    }

    public function get(string $name): mixed
    {
        if (isset($this->instances[$name]) && is_callable($this->instances[$name])) {
            return $this->instances[$name];
        }

        $instance = call_user_func($this->closures[$name]);
        if (isset($this->singletons[$name])) {
            $this->instances[$name] = $instance;
        }

        return $instance;
    }

    public function has(string $name): bool
    {
        return isset($this->closures[$name]);
    }

    public function invoke(string $name, ...$args): mixed
    {
        $closure = $this->get($name);
        if (!is_callable($closure)) {
            throw new \LogicException("No closure registered for: $name");
        }

        return call_user_func($closure, ...$args);
    }
}
