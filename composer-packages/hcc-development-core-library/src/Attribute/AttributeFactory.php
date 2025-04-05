<?php

namespace HCC\Core\Attribute;

use HCC\Core\Attribute\Interfaces\AttributeHandlerInterface;

class AttributeFactory
{
    protected array $handlers = [];

    public function register(string $attributeClass, AttributeHandlerInterface $handler): void
    {
        $this->handlers[$attributeClass] = $handler;
    }

    public function handleAttributes(object $targetObject): void
    {
        $refClass = new \ReflectionObject($targetObject);

        foreach ($this->getAllReflections($refClass) as [$reflection, $attribute]) {
            $attributeName = $attribute->getName();

            if (!isset($this->handlers[$attributeName])) {
                continue; // Ігноруємо не зареєстровані атрибути
            }

            $handler = $this->handlers[$attributeName];
            $attributeInstance = $attribute->newInstance();

            $handler->handle($attributeInstance, $targetObject, $reflection);
        }
    }

    protected function getAllReflections(\ReflectionClass $class): iterable
    {
        // Клас
        foreach ($class->getAttributes() as $attr) {
            yield [$class, $attr];
        }

        // Властивості
        foreach ($class->getProperties() as $prop) {
            foreach ($prop->getAttributes() as $attr) {
                yield [$prop, $attr];
            }
        }

        // Методи
        foreach ($class->getMethods() as $method) {
            foreach ($method->getAttributes() as $attr) {
                yield [$method, $attr];
            }

            // Параметри методів
            foreach ($method->getParameters() as $param) {
                foreach ($param->getAttributes() as $attr) {
                    yield [$param, $attr];
                }
            }
        }
    }
}
