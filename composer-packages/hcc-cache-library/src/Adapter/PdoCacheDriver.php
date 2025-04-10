<?php

namespace HCC\Cache\Adapter;

use Symfony\Component\Cache\Adapter\PdoAdapter;
use HCC\Cache\Interfaces\CacheDriverInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface as Psr16CacheInterface;

class PdoCacheDriver implements CacheDriverInterface
{
    protected PdoAdapter $adapter;

    public function __construct(\PDO $pdo)
    {
        $this->adapter = new PdoAdapter($pdo);
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
