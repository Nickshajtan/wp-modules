<?php

namespace HCC\Core\Cache\Interfaces;

interface CacheInterface
{
    public function get(string $key): mixed;
    public function has(string $key): bool;
    public function set(string $key, mixed $value, int $ttl = 0): void;
    public function delete(string $key): void;
    public function clear(): void;
}
