<?php

namespace HCC\Attributes\Reflection\Interfaces;

use HCC\Attributes\Interfaces\AttributeInterface;

interface ReflectorHandlerInterface
{
    public function supports(\Reflector $reflector): bool;

    public function handle(AttributeInterface $attributeInstance, ?object $targetObject, \Reflector $reflector): void;
}
