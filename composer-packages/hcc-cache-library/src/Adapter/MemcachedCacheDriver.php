<?php

namespace HCC\Cache\Adapter;

use Symfony\Component\Cache\Adapter\MemcachedAdapter;
use HCC\Cache\Interfaces\CacheDriverInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface as Psr16CacheInterface;

class MemcachedCacheDriver implements CacheDriverInterface
{
    protected MemcachedAdapter $adapter;

    public function __construct(\Memcached $memcached)
    {
        $this->adapter = new MemcachedAdapter($memcached);
    }

    public function getPsr6(): CacheItemPoolInterface
    {
        return $this->adapter;
    }

    public function getPsr16(): Psr16CacheInterface
    {
        return new Psr16Cache($this->adapter);
    }
}