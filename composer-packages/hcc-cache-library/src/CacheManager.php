<?php

namespace HCC\Cache;

use HCC\Cache\Interfaces\CacheDriverInterface;
class CacheManager
{
    protected CacheDriverRegistry $registry;
    protected array $instances = [];

    public function __construct(CacheDriverRegistry $registry)
    {
        $this->registry = $registry;




        /*$this->drivers = array_reduce(
            CacheTypes::cases(),
            function (array $drivers, CacheTypes $type) {
                $drivers[$type->value] = $type->getDriver();
                return $drivers;
            },
            []
        );*/
    }
    public function has(string $key): bool
    {
        return isset($this->instances[$key]);
    }

    public function get(string $key): CacheDriverInterface
    {
        if (!$this->has($key)) {
            $definitions = $this->registry->getDefinitions();

            if (!isset($definitions[$key])) {
                throw new \InvalidArgumentException("Cache driver [$key] is not registered.");
            }

            $this->instances[$key] = $definitions[$key]();
        }

        return $this->instances[$key];
    }
}
