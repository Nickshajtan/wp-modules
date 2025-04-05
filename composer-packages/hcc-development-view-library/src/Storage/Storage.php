<?php

namespace HCC\View\Storage;

/**
 * Template sources storage
 */
class Storage
{
    private static array $data = [];
    private static ?string $currentGroup = null;
    private const DEFAULT_KEY = 'default';
    public static function withGroup(string $group, callable $callback): mixed
    {
        $prevGroup = self::$currentGroup;
        self::$currentGroup = $group;
        $result = $callback();
        self::$currentGroup = $prevGroup;

        return $result;
    }
    public static function set(string $key, mixed $value): void
    {
        self::$data[self::$currentGroup ?? self::DEFAULT_KEY][$key] = $value;
    }
    public static function get(string $key, mixed $default = null): mixed
    {
        return self::$data[self::$currentGroup ?? self::DEFAULT_KEY][$key] ?? $default;
    }
}
