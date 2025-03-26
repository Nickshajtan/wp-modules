<?php

namespace HCC\Core\Container;

use HCC\Core\Container\Interfaces\ContainerInterface;
use HCC\Core\Cache\Interfaces\CacheInterface;
class Container implements ContainerInterface
{
    private array $services = array();
    private array $instances = array();
    private array $singletons = array();

    private ?CacheInterface $cache;

    public function __construct(?CacheInterface $cache = null)
    {
        $this->cache = $cache;
    }

    public function has(string $name): bool
    {
        return isset($this->services[$name]) || isset($this->instances[$name]) || ($this->isCacheEnabled() && $this->cache->has($name));
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
        if ($this->isCacheEnabled() && $cached = $this->cache->get($name)) {
            return $cached;
        }

        try {
            if (isset($this->instances[$name])) {
                return $this->instances[$name];
            }

            if (!isset($this->services[$name]) && class_exists($name)) {
                $this->instances[$name] = $this->resolve($name);

                if ($this->isCacheEnabled()) {
                    $this->cache->set($name, $this->instances[$name]);
                }

                return $this->instances[$name];
            }

            if (!isset($this->services[$name])) {
                throw new \InvalidArgumentException("Service not found: $name");
            }

            $instance = call_user_func($this->services[$name], $this);
            if (isset($this->singletons[$name])) {
                $this->instances[$name] = $instance;

                if ($this->isCacheEnabled()) {
                    $this->cache->set($name, $instance);
                }
            }

            return $instance;
        } catch (\RuntimeException $exception) {
            $this->handleException($exception->getMessage());
        }
    }

    public function clear(): void
    {
        $this->instances = [];
        $this->services = [];
        $this->singletons = [];

        if ($this->isCacheEnabled()) {
            $this->cache->clear();
        }
    }

    public function forget(string $name): void
    {
        unset($this->instances[$name]);

        if ($this->isCacheEnabled()) {
            $this->cache->delete($name);
        }
    }

    public function refresh(string $name): void
    {
        unset($this->instances[$name]);

        if ($this->isCacheEnabled()) {
            $this->cache->delete($name);
        }

        if (isset($this->services[$name]) || class_exists($name)) {
            $this->instances[$name] = $this->get($name);
        }
    }

    public function refreshAll(): void
    {
        foreach (array_keys($this->services) as $name) {
            $this->refresh($name);
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

    protected function isCacheEnabled(): bool
    {
        return !is_null($this->cache);
    }

    protected function handleException(string $message): void
    {
        throw new \RuntimeException("$message during container method execution");
    }
}
