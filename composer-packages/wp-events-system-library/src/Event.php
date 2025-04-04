<?php

namespace HCC\Events;

/**
 * Class that describes a data structure
 */
class Event
{
    public string $eventName;
    public $callback;
    public int $priority;
    public int $acceptedArgs;
    public string $id;
    public ?object $component;

    public function __construct(
        string $eventName,
        callable $callback,
        int $priority = 10,
        int $acceptedArgs = 1,
        ?object $component = null,
        ?string $id = null
    ) {
        $this->eventName = $eventName;
        $this->callback = $callback;
        $this->priority = $priority;
        $this->acceptedArgs = $acceptedArgs;
        $this->component = $component;
        $this->id = $id ?? '_' . $eventName . '_' . uniqid();
    }

    public function dispatch(...$args): mixed
    {
        return call_user_func($this->callback, ...$args);
    }

    public function toArray(): array
    {
        return [
            $this->eventName,
            $this->callback,
            $this->priority,
            $this->acceptedArgs
        ];
    }
}
