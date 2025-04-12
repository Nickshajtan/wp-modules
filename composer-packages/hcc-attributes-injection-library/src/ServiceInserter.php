<?php

namespace HCC\Attributes;

use HCC\Attributes\Interfaces\AttributeHandlerInterface;
use HCC\Attributes\Interfaces\AttributeInterface;

readonly class ServiceInserter
{
    public function __construct(
        private iterable                  $classMap,
        private AttributeHandlerInterface $handler,
        private string                    $attributeClass,
    ) {}

    public function scan(): void
    {
        try {
            if (!is_subclass_of($this->attributeClass, AttributeInterface::class)) {
                throw new \InvalidArgumentException('The attribute must implement AttributeInterface');
            }

            foreach ($this->classMap as $class => $file) {
                if (!class_exists($class)) {
                    require_once $file;
                }

                $reflection = new \ReflectionClass($class);
                $attributes = $reflection->getAttributes($this->attributeClass);

                foreach ($attributes as $attr) {
                    $instance = $attr->newInstance();
                    $this->handler->handle($instance, null, $reflection);
                }
            }
        } catch (\ReflectionException $exception) {
            throw new \RuntimeException('Exception during process: ' . $exception->getMessage());
        }
    }
}