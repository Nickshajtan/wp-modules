<?php

namespace HCC\Attributes\Reflection;

use HCC\Attributes\Reflection\Interfaces\MethodReflectorHandlerInterface;
use HCC\Attributes\Interfaces\ResolverInterface;
use HCC\Attributes\Interfaces\AttributeInterface;
use \Reflector;
use \ReflectionMethod;

class MethodReflectorHandler implements MethodReflectorHandlerInterface
{
    public function supports(Reflector $reflector): bool
    {
        return $reflector instanceof ReflectionMethod;
    }

    public function handle(AttributeInterface $attributeInstance, object $targetObject, Reflector $reflector): void
    {

    }
}