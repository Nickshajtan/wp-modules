<?php

namespace HCC\Attributes\Handler;

use HCC\Attributes\Interfaces\AttributeHandlerInterface;
use HCC\Attributes\Interfaces\ResolverInterface;
use HCC\Attributes\Interfaces\AttributeInterface;
use HCC\Attributes\Attribute\Inject;
use \Reflector;
use \ReflectionProperty;
use \ReflectionMethod;
use \ReflectionClass;

class InjectHandler implements AttributeHandlerInterface
{
    protected ResolverInterface $resolver;

    public function __construct(ResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }

    public function handle(AttributeInterface $attributeInstance, ?object $targetObject, Reflector $reflection): void
    {
        if (!$attributeInstance instanceof Inject) {
            return;
        }

        try {
            if ($reflection instanceof ReflectionProperty) {
                $reflection->setValue(
                    $targetObject,
                    $this->resolver->resolve($attributeInstance->service, $attributeInstance->lazy)
                );
            }

            if ($reflection instanceof ReflectionMethod) {
                $resolvedService = $this->resolver->resolve($attributeInstance->service, $attributeInstance->lazy);
                if (!is_null($resolvedService)) {
                    $parameters = $reflection->getParameters();
                    $args = [];
                    foreach ($parameters as $param) {
                        if ($param->getName() === $attributeInstance->service) {
                            $args[] = $resolvedService;
                        }
                    }

                    $reflection->invokeArgs($targetObject, $args);
                }
            }

            if ($reflection instanceof ReflectionClass) {
                $service = $attributeInstance->service;
                $resolvedService = $this->resolver->resolve($service, $attributeInstance->lazy);
                if (!is_null($resolvedService)) {
                    $targetObject->$service = $resolvedService;
                }
            }
        } catch (\ReflectionException $exception) {
            throw new \RuntimeException($exception->getMessage());
        }

    }
}
