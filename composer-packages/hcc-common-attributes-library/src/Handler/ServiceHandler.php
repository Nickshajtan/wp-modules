<?php

namespace HCC\Attributes\Handler;

use HCC\Attributes\Interfaces\AttributeHandlerInterface;
use HCC\Attributes\Interfaces\AttributeInterface;
use HCC\Container\Interfaces\ContainerInterface;
use HCC\Attributes\Attribute\Service;

class ServiceHandler implements AttributeHandlerInterface
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function handle(AttributeInterface $attributeInstance, ?object $targetObject, \Reflector $reflection): void
    {
        if (!($reflection instanceof \ReflectionClass)) {
            return;
        }

        if (!($attributeInstance instanceof Service)) {
            throw new \InvalidArgumentException('Invalid attribute type.');
        }

        $serviceName = $attributeInstance->getName() ?: get_class($targetObject);
        $this->container->set($serviceName, fn() => new $targetObject(), $attributeInstance->isSingleton());
    }
}
