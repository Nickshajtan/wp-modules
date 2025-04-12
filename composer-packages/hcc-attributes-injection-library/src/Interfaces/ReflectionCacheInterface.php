<?php

namespace HCC\Attributes\Interfaces;

interface ReflectionCacheInterface
{
    public function getClassReflection(string $className): \ReflectionClass;
    public function getMethodReflection(string $className, string $methodName): \ReflectionMethod;
    public function getPropertyReflection(string $className, string $propertyName): \ReflectionProperty;
    public function getFunctionReflection(string $functionName): \ReflectionFunction;

    public function getClassReflectionAttributes(string $className): array;
    public function getMethodReflectionAttributes(string $className, string $methodName): array;
    public function getPropertyReflectionAttributes(string $className, string $propertyName): array;
    public function getFunctionReflectionAttributes(string $functionName): array;

    public function restoreAttribute(array $data): AttributeInterface;
}
