<?php

namespace HCC\Attributes\Handler;

use HCC\Attributes\Interfaces\AttributeHandlerInterface;
use HCC\Attributes\Interfaces\ResolverInterface;
use HCC\Attributes\Interfaces\AttributeInterface;
use HCC\Attributes\Reflection\Interfaces\ClassReflectorHandlerInterface;
use HCC\Attributes\Reflection\Interfaces\PropertyReflectorHandlerInterface;
use HCC\Attributes\Reflection\MethodReflectorHandler;
use \Reflector;
use \ReflectionProperty;
use \ReflectionMethod;
use \ReflectionClass;
use \RuntimeException;
use \Attribute;

class InjectHandler implements AttributeHandlerInterface
{
    protected ResolverInterface $resolver;
    protected ?PropertyReflectorHandlerInterface $propertyReflector;

    protected ?MethodReflectorHandler $methodReflector;

    protected ?ClassReflectorHandlerInterface $classReflector;

    public function __construct(
        ResolverInterface                 $resolver,
        PropertyReflectorHandlerInterface $propertyReflector = null,
        MethodReflectorHandler            $methodReflector = null,
        ClassReflectorHandlerInterface    $classReflector = null
    ) {
        $this->resolver = $resolver;
        $this->propertyReflector = $propertyReflector;
        $this->methodReflector = $methodReflector;
        $this->classReflector = $classReflector;
    }

    public function handle(AttributeInterface $attributeInstance, ?object $targetObject, Reflector $reflection): void
    {
        $supportedTargets = $this->getSupportedTargets($attributeInstance);
        $args = [$attributeInstance, $targetObject, $reflection];

        if ($reflection instanceof ReflectionProperty) {
            $this->handleProperty($supportedTargets, $args);
        }

        if ($reflection instanceof ReflectionMethod) {
            $this->handleMethod($supportedTargets, $args);
        }

        if ($reflection instanceof ReflectionClass) {
            $this->handleClass($supportedTargets, $args);
        }
    }

    protected function getSupportedTargets(object $attributeInstance): array
    {
        $reflection = new ReflectionClass($attributeInstance);
        $attributes = $reflection->getAttributes();

        if (count($attributes) < 1) {
            throw new RuntimeException("No attributes found for {$reflection->getName()}");
        }

        return array_map(fn(\Attribute $attribute) => $attribute->getTarget(), $attributes);
    }

    protected function handleProperty(array $targets, array $args): void
    {
        if (!in_array(Attribute::TARGET_PROPERTY, $targets)) {
            throw new RuntimeException('Attribute does not support target "property"');
        }
        if (is_null($this->propertyReflector)) {
            throw new RuntimeException('Property reflector is not defined');
        }
        $this->propertyReflector->handle(...$args);
    }

    protected function handleMethod(array $targets, array $args): void
    {
        if (!in_array(Attribute::TARGET_METHOD, $targets)) {
            throw new RuntimeException('Attribute does not support target "method"');
        }
        if (is_null($this->methodReflector)) {
            throw new RuntimeException('Method reflector is not defined');
        }
        $this->methodReflector->handle(...$args);
    }

    protected function handleClass(array $targets, array $args): void
    {
        if (!in_array(Attribute::TARGET_CLASS, $targets)) {
            throw new RuntimeException('Attribute does not support target "class"');
        }
        if (is_null($this->classReflector)) {
            throw new RuntimeException('Class reflector is not defined');
        }
        $this->classReflector->handle(...$args);
    }
}
