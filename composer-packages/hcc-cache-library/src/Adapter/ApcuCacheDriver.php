<?php

namespace HCC\Cache\Adapter;

use Symfony\Component\Cache\Adapter\ApcuAdapter;
use HCC\Cache\Interfaces\CacheDriverInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface as Psr16CacheInterface;

class ApcuCacheDriver implements CacheDriverInterface
{
    protected ApcuAdapter $adapter;

    public function __construct()
    {
        $this->adapter = new ApcuAdapter();
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
