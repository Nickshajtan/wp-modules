<?php

use PHPUnit\Framework\TestCase;
use HCC\Container\Container;
use Psr\SimpleCache\CacheInterface;

interface SampleInterface {}

class SampleClass
{
    public function __construct() {}
}

class ContainerTest extends TestCase
{
    private Container $container;
    private Container $containerWithCache;
    private $mockCache;

    public function setUp(): void
    {
        parent::setUp();
        $this->container = new Container();
        $this->mockCache = $this->createMock(CacheInterface::class);
        $this->containerWithCache = new Container($this->mockCache);
    }

    public function testSetAndGetService(): void
    {
        $this->container->set('foo', fn() => 'bar');
        $this->assertTrue($this->container->has('foo'));
        $this->assertEquals('bar', $this->container->get('foo'));
    }

    public function testSingletonService(): void
    {
        $counter = 0;

        $this->container->set('counter', function () use (&$counter) {
            return ++$counter;
        }, true);

        $this->assertEquals(1, $this->container->get('counter'));
        $this->assertEquals(1, $this->container->get('counter'));

        $this->container->set('std', fn() => new \stdClass(), true);
        $this->assertSame($this->container->get('std'), $this->container->get('std'));
    }

    public function testNonSingletonService()
    {
        $counter = 0;

        $this->container->set('counter2', function () use (&$counter) {
            return ++$counter;
        });

        $this->assertEquals(1, $this->container->get('counter2'));
        $this->assertEquals(2, $this->container->get('counter2'));

        $this->container->set('std2', fn() => new \stdClass());
        $this->assertNotSame($this->container->get('std2'), $this->container->get('std2'));
    }

    public function testForgetService(): void
    {
        $value = 'value';
        $this->container->set('temp', function () use(&$value) {
            return $value;
        }, true);
        $this->assertEquals($value, $this->container->get('temp'));
        $this->container->forget('temp');
        $this->assertFalse($this->container->has('temp'));
    }

    public function testClearServices(): void
    {
        $this->container->set('a', fn() => 1);
        $this->container->set('b', fn() => 2);

        $this->assertTrue($this->container->has('a'));
        $this->container->clear();
        $this->assertFalse($this->container->has('a'));
    }

    public function testRefreshService(): void
    {
        $value = 'value';
        $this->container->set('temp', function () use(&$value) {
            return $value;
        }, true);
        $this->assertEquals($value, $this->container->get('temp'));
        $value = 'value 2';
        $this->container->refresh('temp');
        $this->assertTrue($this->container->has('temp'));
        $this->assertEquals($value, $this->container->get('temp')); // Recreates the instance
    }

    public function testAutowireClass(): void
    {
        $this->assertInstanceOf(\SampleClass::class, $this->container->get(\SampleClass::class));
    }

    public function testMissingServiceThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->container->get('missingService');
    }

    public function testResolveNonInstantiableThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->container->get(\SampleInterface::class);
    }

    public function testCacheIsUsedWhenAvailable(): void
    {
        $this->mockCache->expects($this->once())
            ->method('get')
            ->with('cachedService')
            ->willReturn('cachedValue');

        $this->containerWithCache->set('cachedService', fn() => 'cachedValue');
        $this->assertTrue($this->containerWithCache->has('cachedService'));
        $this->assertEquals('cachedValue', $this->containerWithCache->get('cachedService'));
    }
}
