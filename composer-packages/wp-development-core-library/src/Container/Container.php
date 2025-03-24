<?php

namespace HCC\Plugin\Core\Container;

use HCC\Plugin\Core\Container\Interfaces\ContainerInterface;

class Container implements ContainerInterface
{
    private array $services = array();
    private array $instances = array();
    private array $singletons = array();

    public function has(string $name): bool
    {
        return ((bool) ($this->services[$name] ?? false));
    }

    public function set(string $name, callable $factory, bool $singleton = false): void
    {
        $this->services[$name] = $factory;
        if ($singleton) {
            $this->singletons[$name] = true;
        }
    }

    public function get(string $name): mixed
    {
        try {
            if (isset($this->instances[$name])) {
                return $this->instances[$name];
            }

            if (!isset($this->services[$name]) && class_exists($name)) {
                $this->instances[$name] = $this->resolve($name);
                return $this->instances[$name];
            }

            if (!isset($this->services[$name])) {
                throw new \InvalidArgumentException("Service not found: $name");
            }

            $instance = call_user_func($this->services[$name], $this);
            if (isset($this->singletons[$name])) {
                $this->instances[$name] = $instance;
            }

            return $instance;
        } catch (\RuntimeException $exception) {
            $this->handleException($exception->getMessage());
        }
    }

    protected function resolve(string $class): object
    {
        try {
            $reflection = new \ReflectionClass($class);

            if (!$reflection->isInstantiable()) {
                throw new \InvalidArgumentException("Class {$class} is not instantiable.");
            }

            $constructor = $reflection->getConstructor();

            if (is_null($constructor)) {
                return new $class;
            }

            $parameters = $constructor->getParameters();
            $dependencies = [];

            foreach ($parameters as $parameter) {
                $dependency = $parameter->getType();

                if ($dependency && !$dependency->isBuiltin()) {
                    $dependencies[] = $this->get($dependency->getName());
                } else {
                    $dependencies[] = null;
                }
            }

            return $reflection->newInstanceArgs($dependencies);
        } catch (\ReflectionException $exception) {
            $this->handleException($exception->getMessage());
        }
    }

    protected function handleException(string $message): void
    {
        throw new \RuntimeException("$message during container method execution");
    }
}
