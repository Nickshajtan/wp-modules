<?php

use PHPUnit\Framework\TestCase;

use HCC\Container\ClosureRegistry;

class ClosureRegistryTest extends TestCase
{
    private ClosureRegistry $registry;

    protected function setUp(): void
    {
        parent::setUp();
        $this->registry = new ClosureRegistry();
    }

    public function testSetAndHas(): void
    {
        $this->registry->set('foo', fn() => 'bar');
        $this->assertTrue($this->registry->has('foo'));
        $this->assertFalse($this->registry->has('missing'));
    }

    public function testSingletonBehavior(): void
    {
        $this->registry->set('test', fn() => fn() => new \stdClass(), true);
        $this->assertSame($this->registry->get('test'), $this->registry->get('test'));
    }

    public function testNonSingletonBehavior(): void
    {
        $this->registry->set('test', fn() => fn() => new \stdClass(), false);
        $this->assertNotSame($this->registry->get('test'), $this->registry->get('test'));
    }

    public function testGetReturnsExpectedValue(): void
    {
        $this->registry->set('value', fn() => fn() => 'hello');
        $this->assertEquals(fn() => 'hello', $this->registry->get('value'));
    }

    public function testInvokeWorks(): void
    {
        $this->registry->set('add', fn() => fn($a, $b) => $a + $b, false);
        $this->assertEquals(5, $this->registry->invoke('add', 2, 3));
    }

    public function testInvokeFailsIfNotCallable(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('No closure registered for: not_callable');

        $this->registry->set('not_callable', fn() => 123, true);
        $this->registry->invoke('not_callable');
    }
}