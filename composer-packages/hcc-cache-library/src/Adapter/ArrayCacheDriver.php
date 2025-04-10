<?php

namespace HCC\Cache\Adapter;

use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;
use HCC\Cache\Interfaces\CacheDriverInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface as Psr16CacheInterface;

class ArrayCacheDriver implements CacheDriverInterface
{
    protected ArrayAdapter $adapter;

    public function __construct()
    {
        $this->adapter = new ArrayAdapter();
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
