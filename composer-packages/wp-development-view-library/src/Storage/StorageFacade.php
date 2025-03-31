<?php

namespace HCC\View\Storage;

/**
 * Facade for convenient group syntax management
 */
class StorageFacade
{
    private static array $instances = [];
    private string $group;

    private function __construct(string $group)
    {
        $this->group = $group;
    }

    public static function for(string $group): self
    {
        if (!isset(self::$instances[$group])) {
            self::$instances[$group] = new self($group);
        }

        return self::$instances[$group];
    }

    public function set(string $key, mixed $value): void
    {
        Storage::withGroup($this->group, fn() => Storage::set($key, $value));
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return Storage::withGroup($this->group, fn() => Storage::get($key, $default));
    }

    public function remember(string $key, callable $callback): mixed
    {
        $cachedValue = $this->get($key);
        if ($cachedValue !== null) {
            return $cachedValue;
        }

        $value = $callback();
        $this->set($key, $value);

        return $value;
    }
}
