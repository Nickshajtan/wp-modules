<?php

namespace HCC\Attributes;

use Psr\Cache\CacheItemPoolInterface;
use \ReflectionClass;
use \ReflectionFunction;
use \ReflectionMethod;
use \ReflectionProperty;

class ReflectionCache
{
    private CacheItemPoolInterface $cachePool;
    private array $reflectionMemoryCache = [];
    private bool $isActive = false;

    protected const RFCT_CLASS = 'class';
    protected const RFCT_METHOD = 'method';
    protected const RFCT_PROPERTY = 'property';
    protected const RFCT_FUNCTION = 'function';

    public function __construct(CacheItemPoolInterface $cachePool)
    {
        $this->cachePool = $cachePool;
    }

    public function enable(): void
    {
        $this->isActive = true;
    }

    public function disable(): void
    {
        $this->isActive = false;
    }

    public function getClassReflection(string $className): ReflectionClass
    {
        return $this->getInMemoryReflection($className, fn() => $this->getReflection(static::RFCT_CLASS, $className));
    }

    public function getMethodReflection(string $className, string $methodName): ReflectionMethod
    {
        return $this->getInMemoryReflection(
            "{$className}_method_{$methodName}",
            fn() => $this->getReflection(static::RFCT_METHOD, $className, $methodName)
        );
    }

    public function getPropertyReflection(string $className, string $propertyName): ReflectionProperty
    {
        return $this->getInMemoryReflection(
            "{$className}_property_{$propertyName}",
            fn() => $this->getReflection(static::RFCT_PROPERTY, $className, $propertyName),
        );
    }

    public function getFunctionReflection(string $functionName): ReflectionFunction
    {
        return $this->getInMemoryReflection(
            $functionName,
            fn() => $this->getReflection(static::RFCT_FUNCTION, $functionName)
        );
    }

    public function getClassReflectionAttributes(string $className): array
    {
        $cacheKey = "reflection_{$className}_attributes";
        $cacheItem = $this->cachePool->getItem($cacheKey);

        if (!$this->isActive || !$cacheItem->isHit()) {
            $reflection = $this->getClassReflection($className);
            $attributes = $this->normalizeAttributes($reflection->getAttributes());
            $cacheItem->set($attributes);
            $this->cachePool->save($cacheItem);
        }

        return $cacheItem->get();
    }

    public function getMethodReflectionAttributes(string $className, string $methodName): array
    {
        $cacheKey = "reflection_{$className}_{$methodName}_method_attributes";
        $cacheItem = $this->cachePool->getItem($cacheKey);

        if (!$this->isActive || !$cacheItem->isHit()) {
            try {
                $method = $this->getClassReflection($className)->getMethod($methodName);
            } catch (\ReflectionException $exception) {
                throw new \InvalidArgumentException(
                    'Exception during reflection method cache processing: ' . $exception->getMessage()
                );
            }

            $attributes = $this->normalizeAttributes($method->getAttributes());
            $cacheItem->set($attributes);
            $this->cachePool->save($cacheItem);
        }

        return $cacheItem->get();
    }

    public function getPropertyReflectionAttributes(string $className, string $propertyName): array
    {
        $cacheKey = "reflection_{$className}_{$propertyName}_property_attributes";
        $cacheItem = $this->cachePool->getItem($cacheKey);

        if (!$this->isActive || !$cacheItem->isHit()) {
            try {
                $property = $this->getClassReflection($className)->getProperty($propertyName);
            } catch (\ReflectionException $exception) {
                throw new \InvalidArgumentException(
                    'Exception during reflection property cache processing: ' . $exception->getMessage()
                );
            }

            $attributes = $this->normalizeAttributes($property->getAttributes());

            $cacheItem->set($attributes);
            $this->cachePool->save($cacheItem);
        }

        return $cacheItem->get();
    }

    public function getFunctionReflectionAttributes(string $functionName): array
    {
        $cacheKey = "reflection_function_{$functionName}_attributes";
        $cacheItem = $this->cachePool->getItem($cacheKey);

        if (!$this->isActive || !$cacheItem->isHit()) {
            $reflection = $this->getFunctionReflection($functionName);
            $attributes = $this->normalizeAttributes($reflection->getAttributes());
            $cacheItem->set($attributes);
            $this->cachePool->save($cacheItem);
        }

        return $cacheItem->get();
    }

    public function restoreAttribute(array $data): object
    {
        return new ($data['name'])(...$data['args']);
    }

    protected function getInMemoryReflection(string $key, callable $factory): ReflectionMethod|ReflectionClass|ReflectionFunction|ReflectionProperty
    {
        if (!isset($this->reflectionMemoryCache[$key])) {
            $this->reflectionMemoryCache[$key] = $factory();
        }

        return $this->reflectionMemoryCache[$key];
    }

    protected function getReflection(string $type, ...$params): ReflectionMethod|ReflectionClass|ReflectionFunction|ReflectionProperty
    {
        $cacheKey = md5($type . implode('_', $params));
        $cacheItem = $this->cachePool->getItem($cacheKey);
        if (!$cacheItem->isHit()) {
            $reflectionData = $this->createReflectionData($type, ...$params);
            $cacheItem->set($reflectionData);
            $this->cachePool->save($cacheItem);
        }

        $reflectionData = $cacheItem->get();
        return $this->restoreReflection($type, ...$reflectionData);
    }

    protected function createReflectionData(string $type, ...$params): array
    {
        $default = ['name' => $params[0], 'method' => null, 'property' => null];

        return match ($type) {
            static::RFCT_FUNCTION,
            static::RFCT_CLASS => $default,
            static::RFCT_METHOD => array_merge($default, ['method' => $params[1]]),
            static::RFCT_PROPERTY => array_merge($default, ['property' => $params[1]]),
            default => throw new \InvalidArgumentException("Unsupported reflection type: $type"),
        };
    }

    protected function restoreReflection(string $type, ...$params): ReflectionMethod|ReflectionClass|ReflectionFunction|ReflectionProperty
    {
        try {
            $cacheKey = $type . $params['name'];
            if (!isset($this->reflectionMemoryCache[$cacheKey])) {
                $this->reflectionMemoryCache[$cacheKey] = static::RFCT_FUNCTION !== $type ?
                    new ReflectionClass($params['name']) : new ReflectionFunction($params['name']);
            }

            return match ($type) {
                static::RFCT_FUNCTION, static::RFCT_CLASS => $this->reflectionMemoryCache[$cacheKey],
                static::RFCT_METHOD => ($this->reflectionMemoryCache[$cacheKey])->getMethod($params['method']),
                static::RFCT_PROPERTY => ($this->reflectionMemoryCache[$cacheKey])->getProperty($params['property']),
                default => throw new \InvalidArgumentException("Unsupported reflection type: $type"),
            };
        } catch (\ReflectionException $exception) {
            throw new \InvalidArgumentException('Exception during reflection cache processing: ' . $exception->getMessage());
        }
    }

    protected function normalizeAttributes(array $attributes): array
    {
        return array_map(fn($attr) => [
            'name' => $attr->getName(),
            'args' => $attr->getArguments(),
        ], $attributes);
    }
}
