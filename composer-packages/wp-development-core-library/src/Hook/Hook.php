<?php

namespace HCC\Plugin\Core\Hook;

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
}
