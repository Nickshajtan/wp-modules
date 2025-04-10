<?php

namespace HCC\Attributes\Interfaces;

interface AttributeHandlerInterface
{
    public function handle(AttributeInterface $attributeInstance, ?object $targetObject, \Reflector $reflection): void;
}
