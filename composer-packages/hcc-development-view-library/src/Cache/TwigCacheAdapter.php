<?php

namespace HCC\View\Cache;

use HCC\View\Interfaces\TemplateCacheInterface;
use \Twig\Cache\CacheInterface;

class TwigCacheAdapter implements CacheInterface
{
    protected TemplateCacheInterface $cache;

    public function __construct(TemplateCacheInterface $cache)
    {
        $this->cache = $cache;
    }

    public function generateKey(string $name, string $className): string
    {
        return $this->cache->getCacheDirectory() . md5($name . $className) . '.php';
    }

    public function write(string $key, string $content): void
    {
        $this->cache->set($key, $content);
    }

    public function load(string $key): void
    {
        if (file_exists($key)) {
            require_once $key;
        }
    }

    public function getTimestamp(string $key): int
    {
        return file_exists($key) ? filemtime($key) : 0;
    }
}
