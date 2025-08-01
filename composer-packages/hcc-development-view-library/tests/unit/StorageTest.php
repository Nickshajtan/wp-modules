<?php

use PHPUnit\Framework\TestCase;
use HCC\View\Storage\Storage;
use HCC\View\Storage\StorageFacade;

class StorageTest extends TestCase
{
    protected function tearDown(): void
    {
        $ref = new \ReflectionClass(Storage::class);
        $prop = $ref->getProperty('data');
        $prop->setAccessible(true);
        $prop->setValue([]);

        $refFacade = new \ReflectionClass(StorageFacade::class);
        $prop = $refFacade->getProperty('instances');
        $prop->setAccessible(true);
        $prop->setValue([]);
    }

    public function testStorageSetAndGetWithGroup(): void
    {
        Storage::withGroup('group1', fn() => Storage::set('foo', 'bar'));

        $value = Storage::withGroup('group1', fn() => Storage::get('foo'));
        $this->assertSame('bar', $value);

        $missing = Storage::withGroup('group2', fn() => Storage::get('foo', 'default'));
        $this->assertSame('default', $missing);
    }

    public function testFacadeSetAndGet(): void
    {
        $facade = StorageFacade::for('group42');
        $facade->set('x', 99);

        $result = $facade->get('x');
        $this->assertSame(99, $result);
    }

    public function testFacadeRememberCachesResult(): void
    {
        $facade = StorageFacade::for('cache');
        $firstCall = $facade->remember('expensive', fn() => 'computed');
        $secondCall = $facade->remember('expensive', fn() => 'should-not-run');

        $this->assertSame('computed', $firstCall);
        $this->assertSame('computed', $secondCall);
    }

    public function testFacadeSingletonPerGroup(): void
    {
        $a = StorageFacade::for('g');
        $b = StorageFacade::for('g');

        $this->assertSame($a, $b);

        $c = StorageFacade::for('other');
        $this->assertNotSame($a, $c);
    }
}
