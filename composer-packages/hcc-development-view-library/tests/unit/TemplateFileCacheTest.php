<?php

use PHPUnit\Framework\TestCase;
use HCC\View\Cache\TemplateFileCache;

class TemplateFileCacheTest extends TestCase
{
    private static int $ttl = 1;
    private static string $cacheDir;
    private static TemplateFileCache $cache;
    public static function setUpBeforeClass(): void
    {
        static::$cacheDir = sys_get_temp_dir() . '/tpl_cache_' . uniqid();
        static::$cache = new TemplateFileCache(static::$cacheDir, static::$ttl);
    }

    public static function tearDownAfterClass(): void
    {
        static::$cache->clear();
        @rmdir(static::$cacheDir);
    }

    public function testSetAndGetReturnsStoredContent(): void
    {
        static::$cache->set('foo.tpl', 'hello');
        $this->assertSame('hello', static::$cache->get('foo.tpl'));
    }

    public function testGetReturnsNullIfNotExist(): void
    {
        $this->assertNull(static::$cache->get('nope.tpl'));
    }

    public function testGetReturnsNullIfExpired(): void
    {
        static::$cache->set('expired.tpl', 'old');
        sleep(static::$ttl * 2);
        $this->assertNull(static::$cache->get('expired.tpl'));
    }

    public function testDeleteRemovesFile(): void
    {
        static::$cache->set('delete.tpl', 'bye');
        static::$cache->delete('delete.tpl');
        $this->assertNull(static::$cache->get('delete.tpl'));
    }

    public function testClearRemovesAll(): void
    {
        static::$cache->set('a.tpl', 'a');
        static::$cache->set('b.tpl', 'b');
        static::$cache->clear();

        $this->assertNull(static::$cache->get('a.tpl'));
        $this->assertNull(static::$cache->get('b.tpl'));
    }

    public function testPurgeExpiredRemovesOnlyExpired(): void
    {
        static::$cache->set('live.tpl', 'stay');
        static::$cache->set('old.tpl', 'die');
        sleep(static::$ttl * 2);
        $livePath = (new ReflectionClass(static::$cache))->getMethod('getFilePath');
        $livePath->setAccessible(true);
        $liveFile = $livePath->invoke(static::$cache, 'live.tpl');
        touch($liveFile);
        static::$cache->purgeExpired();

        $this->assertSame('stay', static::$cache->get('live.tpl'));
        $this->assertNull(static::$cache->get('old.tpl'));
    }

    public function testGetCacheDirectoryReturnsCorrectPath(): void
    {
        $this->assertSame(
            rtrim(static::$cacheDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR,
            static::$cache->getCacheDirectory()
        );
    }
}
