<?php

namespace HCC\Cache\Interfaces;

use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface as Psr16CacheInterface;

interface CacheDriverInterface
{
    public function getPsr6(): CacheItemPoolInterface;
    public function getPsr16(): Psr16CacheInterface;
}
