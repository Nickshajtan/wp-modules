<?php

namespace HCC\Cache\Adapter;

use Doctrine\Common\Cache\Cache as DoctrineCacheInterface;
use HCC\Cache\Interfaces\CacheDriverInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface as Psr16CacheInterface;

class DoctrineCacheDriver implements CacheDriverInterface
{
    protected DoctrineCacheInterface $doctrine;

    public function __construct(DoctrineCacheInterface $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function getPsr6(): CacheItemPoolInterface
    {
        throw new \RuntimeException('Doctrine cache is not PSR-6 compatible.');
    }

    public function getPsr16(): Psr16CacheInterface
    {
        return new class($this->doctrine) implements Psr16CacheInterface {
            public function __construct(protected DoctrineCacheInterface $doctrine) {}
            public function get(string $key, mixed $default = null): mixed
            {
                return $this->doctrine->fetch($key) ?: $default;
            }
            public function set(string $key, mixed $value, null|int|\DateInterval $ttl = null): bool
            {
                return $this->doctrine->save($key, $value);
            }
            public function delete(string $key): bool
            {
                return $this->doctrine->delete($key);
            }
            public function clear(): bool
            {
                return $this->doctrine->deleteAll();
            }
            public function getMultiple(array $keys, mixed $default = null): iterable
            {
                return array_map(fn(string $key) =>  $this->get($key, $default), $keys);
            }
            public function setMultiple(iterable $values, null|int|\DateInterval $ttl = null): bool
            {
                $success = true;
                foreach ($values as $key => $value) {
                    $success = $success && $this->set($key, $value, $ttl);
                }

                return $success;
            }
            public function deleteMultiple(iterable $keys): bool
            {
                $success = true;
                foreach ($keys as $key) {
                    $success = $success && $this->delete($key);
                }

                return $success;
            }
            public function has(string $key): bool
            {
                return $this->doctrine->contains($key);
            }
        };
    }
}