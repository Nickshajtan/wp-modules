<?php

namespace HCC\Attributes\Reflection;

use HCC\Attributes\Reflection\Interfaces\PropertyReflectorHandlerInterface;
use HCC\Attributes\Interfaces\ResolverInterface;
use HCC\Attributes\Interfaces\AttributeInterface;
use \Reflector;
use \ReflectionProperty;

class PropertyReflectorHandler implements PropertyReflectorHandlerInterface
{
    public function __construct(protected ResolverInterface $resolver) {}

    public function supports(Reflector $reflector): bool
    {
        return $reflector instanceof ReflectionProperty;
    }

    public function handle(AttributeInterface $attributeInstance, object $targetObject, Reflector $reflector): void
    {
        $reflector->setAccessible(true);
        $serviceId = $attributeInstance->service ?? $reflector->getType()?->getName();

        if (!$serviceId) {
            throw new \RuntimeException("Cannot resolve service for property '{$reflector->getName()}'");
        }

        $value = $this->resolver->resolve($serviceId, $attributeInstance->lazy ?? true);
        $reflector->setValue($targetObject, $value);
    }
}
