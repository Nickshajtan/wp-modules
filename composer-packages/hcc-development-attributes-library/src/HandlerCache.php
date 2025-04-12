<?php

namespace HCC\Attributes;

use Psr\Cache\CacheItemPoolInterface;
use HCC\Attributes\Interfaces\AttributeHandlerInterface;

class HandlerCache
{
    private CacheItemPoolInterface $cachePool;

    public function __construct(CacheItemPoolInterface $cachePool)
    {
        $this->cachePool = $cachePool;
    }

    public function getHandler(string $attributeClass, AttributeHandlerInterface $handler): AttributeHandlerInterface
    {
        $cacheKey = 'handler_' . md5($attributeClass);

        $cacheItem = $this->cachePool->getItem($cacheKey);
        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $cacheItem->set($handler);
        $this->cachePool->save($cacheItem);

        return $handler;
    }
}
