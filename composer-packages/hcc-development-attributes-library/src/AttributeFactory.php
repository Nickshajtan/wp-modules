<?php

namespace HCC\Attributes;

use HCC\Attributes\Interfaces\AttributeHandlerInterface;
use HCC\Attributes\Interfaces\AttributeInterface;

class AttributeFactory
{
    protected array $handlers = [];

    public function register(string $attributeClass, AttributeHandlerInterface $handler): void
    {
        if (!is_subclass_of($attributeClass, AttributeInterface::class)) {
            throw new \InvalidArgumentException('The provided class must be an instance of Attribute.');
        }

        $this->handlers[$attributeClass] = $handler;
    }

    public function handleAttributes(object $targetObject): void
    {
        $reflectionClass = new \ReflectionClass($targetObject);

        // Обробка атрибутів класу
        foreach ($reflectionClass->getAttributes() as $attribute) {
            $handler = $this->getHandler($attribute->getName());
            $handler->handle($attribute->newInstance(), $targetObject, $reflectionClass);
        }

        // Обробка атрибутів властивостей
        foreach ($reflectionClass->getProperties() as $property) {
            foreach ($property->getAttributes() as $attribute) {
                $handler = $this->getHandler($attribute->getName());
                $handler->handle($attribute->newInstance(), $targetObject, $property);
            }
        }

        // Обробка атрибутів методів
        foreach ($reflectionClass->getMethods() as $method) {
            foreach ($method->getAttributes() as $attribute) {
                $handler = $this->getHandler($attribute->getName());
                $handler->handle($attribute->newInstance(), $targetObject, $method);
            }
        }
    }

    protected function getHandler(string $attributeClass): AttributeHandlerInterface
    {
        if (!isset($this->handlers[$attributeClass])) {
            throw new \RuntimeException("Handler for attribute '$attributeClass' not found.");
        }

        return $this->handlers[$attributeClass];
    }
}
