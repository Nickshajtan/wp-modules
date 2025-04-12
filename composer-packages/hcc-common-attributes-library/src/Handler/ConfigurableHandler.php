<?php

namespace HCC\Attributes\Handler;

use HCC\Attributes\Interfaces\AttributeHandlerInterface;
use HCC\Attributes\Interfaces\AttributeInterface;
use HCC\Container\Interfaces\ContainerInterface;
use HCC\Attributes\Attribute\Configurable;

class ConfigurableHandler implements AttributeHandlerInterface
{
    public function __construct(private readonly ContainerInterface $container, private readonly array $customSources = []) {}

    public function handle(AttributeInterface $attributeInstance, ?object $targetObject, \Reflector $reflection): void
    {
        if (!$attributeInstance instanceof Configurable || !$reflection instanceof \ReflectionClass) {
            return;
        }

        try {
            $instance = $targetObject ?? $reflection->newInstanceWithoutConstructor();
            $prefix = $attributeInstance->prefix ?? '';
            $sourceArray = $this->resolveSource($attributeInstance->source);

            foreach ($reflection->getProperties() as $property) {
                $propertyName = $property->getName();
                $key = $prefix . strtoupper($propertyName);

                if (array_key_exists($key, $sourceArray)) {
                    $value = $this->castValue($property->getType(), $sourceArray[$key]);
                    $property->setValue($instance, $value);
                }
            }

            $this->container->set($reflection->getName(), fn() => $instance, true);

        } catch (\ReflectionException $exception) {
            throw new \RuntimeException($exception->getMessage());
        }
    }

    protected function resolveSource(string $source): array
    {
        return match (strtolower($source)) {
            'env' => $_ENV,
            'server' => $_SERVER,
            'globals' => $GLOBALS,
            default => $this->customSources[$source] ?? []
        };
    }

    protected function castValue(?\ReflectionType $type, mixed $value): mixed
    {
        if ($type instanceof \ReflectionNamedType) {
            return match ($type->getName()) {
                'int' => (int) $value,
                'float' => (float) $value,
                'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
                'string' => (string) $value,
                default => $value
            };
        }

        return $value;
    }
}
