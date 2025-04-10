<?php

namespace HCC\Attributes\Handler;

use HCC\Attributes\Interfaces\AttributeHandlerInterface;
use HCC\Attributes\Interfaces\AttributeInterface;
use HCC\Container\Interfaces\ContainerInterface;
use HCC\Attributes\Attribute\Decorator;

class DecoratorHandler implements AttributeHandlerInterface
{
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly ContainerInterface $closureRegistry
    ) {}

    public function handle(AttributeInterface $attributeInstance, ?object $targetObject, \Reflector $reflection): void
    {
        if (!$attributeInstance instanceof Decorator || is_null($targetObject)) {
            return;
        }

        $decorator = $this->resolveCallable($attributeInstance->callable);
        if ($reflection instanceof \ReflectionMethod) {
            $this->wrapMethod($targetObject, $reflection, $decorator);
        }

        if ($reflection instanceof \ReflectionFunction) {
            $this->wrapFunction($reflection, $decorator);
        }

        throw new \LogicException('Decorator can only be applied to methods or functions');
    }

    protected function resolveCallable(string|callable $callable): callable
    {
        if (is_callable($callable)) {
            return $callable;
        }

        if (function_exists($callable)) {
            return $callable(...);
        }

        $throwExceptionIfNeeded = function ($callable) {
            if (!is_callable($callable)) {
                throw new \InvalidArgumentException('Can`t find callable argument');
            }
        };

        if (str_contains($callable, '::')) {
            $callable = explode('::', $callable);
            $callable = [$this->container->get($callable[0]), $callable[1]];
            $throwExceptionIfNeeded($callable);

            return $callable;
        }

        $callable = $this->container->get($callable);
        $throwExceptionIfNeeded($callable);

        return $callable;
    }

    protected function wrapMethod(object $targetObject, \ReflectionMethod $method, callable $decorator): void
    {
        try {
            $original = $method->getClosure($targetObject);
            $wrapper = function (...$args) use ($original, $decorator) {
                return $decorator(fn() => $original(...$args), $args);
            };
            $bound = \Closure::bind($wrapper, $targetObject, $targetObject::class);
            $targetObject->{$method->getName()} = $bound;

        } catch (\ReflectionException $exception) {
            throw new \RuntimeException('Exception during decorator apply... ' . $exception->getMessage());
        }
    }

    protected function wrapFunction(\ReflectionFunction $function, callable $decorator): void
    {
        $functionName = $function->getName();
        $original     = $function->getClosure();
        $wrapper      = function (...$args) use ($original, $decorator) {
            return $decorator(fn() => $original(...$args), $args);
        };

        $this->closureRegistry->set($functionName, fn() => $wrapper);
    }
}
