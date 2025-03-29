<?php

namespace HCC\Events\Hook;

/**
 * Class that describes a data structure
 */
class Hook
{
    public string $hookName;
    public $callback;
    public int $priority;
    public int $acceptedArgs;
    public string $id;
    public ?object $component;

    public function __construct(
        string $hookName,
        callable $callback,
        int $priority = 10,
        int $acceptedArgs = 1,
        ?object $component = null,
        ?string $id = null
    ) {
        $this->hookName = $hookName;
        $this->callback = $callback;
        $this->priority = $priority;
        $this->acceptedArgs = $acceptedArgs;
        $this->component = $component;
        $this->id = $id ?? '_' . $hookName . '_' . uniqid();
    }

    public function dispatch(...$args): mixed
    {
        return call_user_func($this->callback, ...$args);
    }

    public function toArray(): array
    {
        return [
            $this->hookName,
            $this->callback,
            $this->priority,
            $this->acceptedArgs
        ];
    }
}
