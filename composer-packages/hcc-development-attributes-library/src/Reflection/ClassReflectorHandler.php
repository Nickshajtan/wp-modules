<?php

namespace HCC\Attributes\Reflection;

use HCC\Attributes\Reflection\Interfaces\ClassReflectorHandlerInterface;
use HCC\Attributes\Interfaces\ResolverInterface;
use HCC\Attributes\Interfaces\AttributeInterface;
use \Reflector;
use \ReflectionClass;

class ClassReflectorHandler implements ClassReflectorHandlerInterface
{
    public function supports(Reflector $reflector): bool
    {
        return $reflector instanceof ReflectionClass;
    }

    public function handle(AttributeInterface $attributeInstance, object $targetObject, Reflector $reflector): void
    {
        /** @var ReflectionClass $reflector */
        foreach ($reflector->getProperties() as $property) {
            foreach ($property->getAttributes() as $attr) {
                if (in_array($attr->getName(), $this->supportedAttributes, true)) {
                    $this->selfHandler->handle($attr->newInstance(), $targetObject, $property);
                }
            }
        }
    }
}
