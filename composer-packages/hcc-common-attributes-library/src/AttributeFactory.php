<?php

namespace HCC\Attributes;

use HCC\Attributes\Interfaces\AttributeInterface;

/**
 * $cachePool = new SomeCachePool();
 * $reflectionCache = new ReflectionCache($cachePool);
 *
 * $attributeFactory = new AttributeFactory($reflectionCache);
 *
 * $attributeFactory->registerHandler(Service::class, new ServiceHandler($container));
 * $targetObject = new YourClass();
 * $attributeFactory->handleAttributes($targetObject);
 *
 */
class AttributeFactory
{
    private ReflectionCache $reflectionCache;

    private HandlerCache $handlerCache;

    private array $handlers = [];

    public function __construct(ReflectionCache $reflectionCache, HandlerCache $handlerCache)
    {
        $this->reflectionCache = $reflectionCache;
        $this->handlerCache = $handlerCache;
    }

    public function registerHandler(string $attributeClass, callable $handler): void
    {
        $this->handlers[$attributeClass] = $handler;
    }

    public function enableCaching(): void
    {
        $this->reflectionCache->enable();
    }

    public function disableCaching(): void
    {
        $this->reflectionCache->disable();
    }

    public function handleAttributes(object $targetObject): void
    {
        $className = get_class($targetObject);
        $classReflection = $this->reflectionCache->getClassReflection($className);

        foreach ($this->reflectionCache->getClassReflectionAttributes($className) as $data) {
            $this->processAttribute($this->reflectionCache->restoreAttribute($data), $targetObject, $classReflection);
        }

        foreach ($classReflection->getMethods() as $method) {
            foreach ($this->reflectionCache->getMethodReflectionAttributes($className, $method->getName()) as $data) {
                $this->processAttribute($this->reflectionCache->restoreAttribute($data), $targetObject, $method);
            }
        }

        foreach ($classReflection->getProperties() as $property) {
            foreach ($this->reflectionCache->getPropertyReflectionAttributes($className, $property->getName()) as $data) {
                $this->processAttribute($this->reflectionCache->restoreAttribute($data), $targetObject, $property);
            }
        }
    }

    public function handleFunction(string $functionName): void
    {
        foreach ($this->reflectionCache->getFunctionReflectionAttributes($functionName) as $data) {
            $this->processAttribute(
                $this->reflectionCache->restoreAttribute($data),
                null,
                $this->reflectionCache->getFunctionReflection($functionName)
            );
        }
    }

    protected function processAttribute(AttributeInterface $attribute, ?object $targetObject, \Reflector $reflection): void
    {
        if (!isset($this->handlers[$attribute::class])) {
            throw new \LogicException("No handler registered for attribute {$attribute::class}");
        }

        $handler = $this->handlerCache->getHandler($attribute::class, $this->handlers[$attribute::class]);
        $handler->handle($attribute, $targetObject, $reflection);
    }
}
