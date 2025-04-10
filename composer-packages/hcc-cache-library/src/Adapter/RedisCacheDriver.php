<?php

namespace HCC\Cache\Adapter;

use Symfony\Component\Cache\Adapter\RedisAdapter;
use HCC\Cache\Interfaces\CacheDriverInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface as Psr16CacheInterface;

class RedisCacheDriver implements  CacheDriverInterface
{
    protected RedisAdapter $adapter;

    public function __construct(\Redis $redis = null)
    {
        $this->adapter = new RedisAdapter(RedisAdapter::createConnection($redis));
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
