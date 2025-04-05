<?php

namespace HCC\Core\Attribute\Interfaces;

interface AttributeHandlerInterface
{
    public function handle(object $attributeInstance, object $targetObject, \Reflector $reflection): void;
}
