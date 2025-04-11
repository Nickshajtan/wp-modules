<?php

namespace HCC\Cache;

class CacheDriverRegistry
{
    protected array $drivers = [];

    public function register(string $key, callable $factory): void {
        $this->drivers[$key] = $factory;
    }

    public function unregister(string $key): void {
        unset($this->drivers[$key]);
    }

    public function getDefinitions(): array {
        return $this->drivers;
    }
}
