<?php

namespace HCC\Attributes\Interfaces;

interface HandlerCacheInterface
{
    public function getHandler(string $attributeClass, AttributeHandlerInterface $handler): AttributeHandlerInterface;
}