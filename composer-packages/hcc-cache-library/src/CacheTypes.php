<?php

namespace HCC\Cache;

use HCC\Cache\Interfaces\CacheDriverInterface;
use HCC\Cache\Interfaces\CacheTypeInterface;
use HCC\Cache\Adapter\ApcuCacheDriver;
use HCC\Cache\Adapter\ArrayCacheDriver;
use HCC\Cache\Adapter\DoctrineCacheDriver;
use HCC\Cache\Adapter\FileCacheDriver;
use HCC\Cache\Adapter\MemcachedCacheDriver;
use HCC\Cache\Adapter\PdoCacheDriver;
use HCC\Cache\Adapter\RedisCacheDriver;

enum CacheTypes: string implements CacheTypeInterface
{
    case FILE = 'file';
    case APCU = 'apcu';
    case MEMORY = 'memory';
    case MEMCACHED = 'memcache';
    case REDIS = 'redis';
    case PDO = 'pdo';
    case DOCTRINE = 'doctrine';

    public function value(): string
    {
        return $this->value;
    }

    public function getDriver(): string
    {
        return match ($this) {
            self::FILE => FileCacheDriver::class,
            self::APCU => ApcuCacheDriver::class,
            self::MEMORY => ArrayCacheDriver::class,
            self::MEMCACHED => MemcachedCacheDriver::class,
            self::REDIS => RedisCacheDriver::class,
            self::PDO => PdoCacheDriver::class,
            self::DOCTRINE => DoctrineCacheDriver::class,
        };
    }

    public function makeInstance(array $config = []): CacheDriverInterface
    {
        $class = $this->getDriver();
        if (!class_exists($class)) {
            throw new \RuntimeException("Cache driver class '{$class}' does not exist.");
        }

        return new $class($config);
    }
}