<?php

namespace HCC\Attributes\Scanner;

/**
 * Eg Composer\Autoload\ClassLoader $loader->getClassMap();
 */
class ClassScanner
{
    protected array $classMap;

    public function __construct(array $classMap)
    {
        $this->classMap = $classMap;
    }

    public function getClassesWithAttribute(string $attributeName): array
    {
        try {
            $result = [];

            foreach ($this->classMap as $class => $file) {
                if (!class_exists($class, false)) {
                    require_once $file;
                }

                if (!class_exists($class, false)) {
                    continue;
                }

                $ref = new \ReflectionClass($class);
                if (!$ref->isInstantiable()) {
                    continue;
                }

                $attributes = $ref->getAttributes($attributeName);
                if (!empty($attributes)) {
                    $result[$class] = $ref;
                }
            }

            return $result;
        } catch (\ReflectionException $exception) {
            throw new \RuntimeException('Exception during scan: ' . $exception->getMessage());
        }
    }
}
