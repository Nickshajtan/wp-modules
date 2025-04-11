<?php

namespace HCC\Cache;

use HCC\Cache\Interfaces\CacheDriverInterface;

class CacheFacade
{
    protected CacheDriverRegistry $registry;
    protected CacheManager $manager;

    public function __construct(?CacheDriverRegistry $registry = null, ?CacheManager $manager = null)
    {
        $this->registry = $registry ?? new CacheDriverRegistry();
        $this->manager = $manager ?? new CacheManager($this->registry);
    }

    public function register(string $key, callable $factory): void
    {
        $this->registry->register($key, $factory);
    }

    public function get(string $key): CacheDriverInterface
    {
        return $this->manager->get($key);
    }

    public function has(string $key): bool
    {
        return $this->manager->has($key);
    }

    public function all(): array
    {
        return $this->registry->getDefinitions();
    }
}
