<?php

use PHPUnit\Framework\TestCase;
use HCC\Events\EventCollection;
use HCC\Events\CollectionCallbacksStore;

class EventCollectionTest extends TestCase
{
    protected EventCollection $collection;

    public function setUp(): void
    {
        $this->collection = new EventCollection();
    }

    public function testAddAndGetEvent(): void
    {
        $id = $this->collection->addEvent(eventName: 'my_event', callback: fn($arg) => $arg, priority: 5, acceptedArgs: 1);
        $events = $this->collection->getAllEvents('my_event', 5);

        $this->assertCount(1, $events);
        $this->assertEquals($id, $events[0]->id);
        $this->assertEquals('my_event', $events[0]->eventName);
    }

    public function testRemoveEventById(): void
    {
        $id = $this->collection->addEvent(eventName: 'event_name', callback: fn() => null);
        $result = $this->collection->removeEvent(eventName:'event_name', id: $id);

        $this->assertTrue($result);
        $this->assertEmpty($this->collection->getAllEvents('event_name'));
    }

    public function testDispatchFallbackToGlobalHandler(): void
    {
        $executed = false;
        $handlers = new CollectionCallbacksStore(
            fn() => null,
            fn() => null,
            function ($name, $value) use (&$executed) {
                $executed = $name === 'fallback_event' && $value === 42;
            }
        );

        $collection = new EventCollection($handlers);
        $collection->dispatchEvent('fallback_event', '', 42);

        $this->assertTrue($executed);
    }

    public function testRegisterEventCallsGlobalAdd(): void
    {
        $wasCalled = false;
        $handlers = new CollectionCallbacksStore(
            function () use (&$wasCalled) {
                $wasCalled = true;
            },
            fn() => null,
            fn() => null
        );

        $collection = new EventCollection($handlers);
        $id = $collection->addEvent(eventName: 'register_event', callback: fn () => null);
        $collection->registerEvent(eventName: 'register_event', id: $id);

        $this->assertTrue($wasCalled);
    }

    public function testDeregisterEventCallsGlobalRemove(): void
    {
        $wasCalled = false;
        $handlers = new CollectionCallbacksStore(
            fn() => null,
            function ($name) use (&$wasCalled) {
                if ($name === 'deregister_event') {
                    $wasCalled = true;
                }
            },
            fn() => null
        );

        $collection = new EventCollection($handlers);
        $collection->deregisterEvent(eventName: 'deregister_event', id: 'non_existing_id');

        $this->assertTrue($wasCalled);
    }

    public function testAddEventWithCustomId(): void
    {
        $customId = 'custom_123';
        $encodedId = hash('sha256', $customId);

        $returnedId = $this->collection->addEvent(eventName: 'custom_event', callback: fn () => null, priority: 10, acceptedArgs: 1, object: null, id: $customId);

        $this->assertEquals($encodedId, $returnedId);
    }
}