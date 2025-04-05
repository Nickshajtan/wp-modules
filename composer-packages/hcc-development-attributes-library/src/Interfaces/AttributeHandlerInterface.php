<?php

namespace HCC\Attribute\Interfaces;

interface AttributeHandlerInterface
{
    public function handle(object $attributeInstance, object $targetObject, \Reflector $reflection): void;
}
