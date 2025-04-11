<?php

namespace HCC\Cache;

use HCC\Cache\Interfaces\CacheDriverInterface;
use Psr\SimpleCache\CacheInterface;

class CacheChain implements CacheInterface
{
    protected array $caches = [];

    public function __construct(CacheDriverInterface ...$caches)
    {
        $this->caches = $caches;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        foreach ($this->caches as $index => $cache) {
            $cache = $cache->getPsr16();
            $value = $cache->get($key, null);

            if (!is_null($value)) {
                $this->forEachCache($index, 'set', $key, $value);
                return $value;
            }
        }

        return $default;
    }

    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $result = [];
        foreach ($this->caches as $index => $cache) {
            $values = array_filter($cache->getMultiple($keys, null));
            if (count($values) > 0 && $index > 0) {
                $this->forEachCache($index, 'setMultiple', $values);
                $result = array_merge($result, $values);
            }
        }

        return $result;
    }

    public function set(string $key, mixed $value, null|int|\DateInterval $ttl = null): bool
    {
        return $this->reduceEachCache('set', true, $key, $value, $ttl);
    }

    public function setMultiple(iterable $values, null|int|\DateInterval $ttl = null): bool
    {
        return $this->reduceEachCache('setMultiple', true, $values, $ttl);
    }

    public function delete(string $key): bool
    {
        return $this->reduceEachCache('delete', true, $key);
    }

    public function deleteMultiple(iterable $keys): bool
    {
        return $this->reduceEachCache('deleteMultiple', true, ...$keys);
    }

    public function has(string $key): bool
    {
        foreach ($this->caches as $cache) {
            $cache = $cache->getPsr16();
            if ($cache->has($key)) {
                return true;
            }
        }

        return false;
    }

    public function clear(): bool
    {
        return $this->reduceEachCache('clear', true);
    }

    protected function forEachCache(int $index, string $callback, ...$args): void
    {
        for ($i = 0; $i < $index; $i++) {
            if (method_exists($this->caches[$i], $callback)) {
                call_user_func([$this->caches[$i], $callback], ...$args);
            }
        }
    }

    protected function reduceEachCache(string|callable $callback, bool $initialState, ...$args): bool
    {
        return array_reduce(
            $this->caches,
            function (bool $current, CacheDriverInterface $cache) use($callback, $args): bool {
                $cache = $cache->getPsr16();
                if (method_exists($cache, $callback)) {
                    return call_user_func([$cache->getPsr16(), $callback], ...$args) && $current;
                }

                if (is_callable($callback)) {
                    return call_user_func($callback, $cache, ...$args);
                }

                return false;
            },
            $initialState
        );
    }
}
